<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /* =========================================================
     |  Private: common DB-facade activity logger for Auth
     |  Writes to user_data_activity_log
     * ========================================================= */
    private function logAuthActivity(
        Request $request,
        string $activity,            // e.g. 'login' | 'logout'
        string $note,                // short human note
        ?int   $adminId = null,      // actor id if known
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => $adminId ?: 0,
                'performed_by_role' => 'admin',
                'ip'                => $request->ip(),
                'user_agent'        => (string) $request->userAgent(),
                'activity'          => $activity,      // 'login' | 'logout'
                'module'            => 'Auth',
                'table_name'        => 'admins',
                'record_id'         => $adminId,
                'changed_fields'    => null,           // not needed for auth events
                'old_values'        => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('user_data_activity_log insert failed (Auth)', ['error' => $e->getMessage()]);
        }
    }

    // Admin login (email or username)
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string', // email or username
            'password'   => 'required|string',
            'remember'   => 'sometimes|boolean',
        ]);

        $identifier = $request->identifier;
        $remember = (bool) $request->boolean('remember', false);

        // Find admin by email or username
        $admin = DB::table('admins')
            ->where('email', $identifier)
            ->orWhere('name', $identifier) // adjust if your username field differs
            ->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            Log::warning('Admin login failed', ['identifier' => $identifier]);

            // LOG: failed login (no admin id)
            $this->logAuthActivity(
                $request,
                'login',
                'Login failed (invalid credentials) for identifier: '.$identifier,
                null,
                null,
                null
            );

            return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
        }

        // Generate token (PLAINTEXT returned to client)
        $plainText = bin2hex(random_bytes(40));
        $hash = hash('sha256', $plainText);
        $expiresAt = $remember ? now()->addDays(30) : now()->addHours(12);

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'admin',
            'tokenable_id'   => $admin->id,
            'name'           => 'auth_token',
            'token'          => $hash,
            'abilities'      => json_encode(['*']),
            'expires_at'     => $expiresAt,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // LOG: successful login
        $this->logAuthActivity(
            $request,
            'login',
            'Login successful',
            (int) $admin->id,
            null,
            [
                // NEVER log plaintext token
                'token_hash_prefix' => substr($hash, 0, 12),
            ]
        );

        return response()->json([
            'status'         => 'success',
            'message'        => 'Login successful',
            'access_token'   => $plainText, // return plaintext to client
            'token_type'     => 'Bearer',
            'tokenable_type' => 'admin',
            'remember'       => $remember,
            'expires_at'     => $expiresAt?->toIso8601String(),
        ], 200);
    }

    // Admin logout
    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            // LOG: logout attempt without token
            $this->logAuthActivity($request, 'logout', 'No token provided');
            return response()->json(['status' => 'error', 'message' => 'Token not provided'], 401);
        }

        $hashedToken = hash('sha256', $token);

        // Fetch token row first so we can capture admin id for logging
        $row = DB::table('personal_access_tokens')
            ->where('token', $hashedToken)
            ->where('tokenable_type', 'admin')
            ->first();

        if (!$row) {
            // LOG: invalid/expired token
            $this->logAuthActivity(
                $request,
                'logout',
                'Invalid or expired token',
                null,
                ['token_hash_prefix' => substr($hashedToken, 0, 12)],
                null
            );
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired token'], 401);
        }

        $deleted = DB::table('personal_access_tokens')
            ->where('token', $hashedToken)
            ->where('tokenable_type', 'admin')
            ->delete();

        if ($deleted) {
            // LOG: logout success
            $this->logAuthActivity(
                $request,
                'logout',
                'Logged out successfully',
                (int) $row->tokenable_id,
                ['token_hash_prefix' => substr($hashedToken, 0, 12)],
                null
            );
            return response()->json(['status' => 'success', 'message' => 'Logged out successfully'], 200);
        }

        // LOG: race/edge case where delete failed
        $this->logAuthActivity(
            $request,
            'logout',
            'Logout failed during token revoke',
            (int) ($row->tokenable_id ?? 0),
            ['token_hash_prefix' => substr($hashedToken, 0, 12)],
            null
        );

        return response()->json(['status' => 'error', 'message' => 'Invalid or expired token'], 401);
    }
}
