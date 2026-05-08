<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeveloperLoginController extends Controller
{
    private const SECRET_CODE = 'Developer@hallienz#*';

    private const ROLE_MAP = [
        'admin' => [
            'table' => 'admins',
            'id_column' => 'id',
            'email_column' => 'email',
            'tokenable_type' => 'admin',
            'abilities' => ['*'],
            'redirect_url' => '/dashboard',
            'label' => 'Admin',
            'requires_active_status' => false,
        ],
        'assignee' => [
            'table' => 'assigned_people',
            'id_column' => 'id',
            'email_column' => 'email',
            'tokenable_type' => 'assignee',
            'abilities' => ['*'],
            'redirect_url' => '/assignee/dashboard',
            'label' => 'Assignee',
            'requires_active_status' => false,
        ],
        'client_user' => [
            'table' => 'client_users',
            'id_column' => 'id',
            'email_column' => 'email',
            'tokenable_type' => 'client_user',
            'abilities' => ['*', 'role:client_user'],
            'redirect_url' => '/client-user/dashboard',
            'label' => 'Client User',
            'requires_active_status' => true,
        ],
        'accountant_user' => [
            'table' => 'accountant_users',
            'id_column' => 'id',
            'email_column' => 'email',
            'tokenable_type' => 'accountant_user',
            'abilities' => ['*', 'role:accountant_user'],
            'redirect_url' => '/accountant-user/dashboard',
            'label' => 'Accountant User',
            'requires_active_status' => true,
        ],
    ];

    public function show()
    {
        return view('pages/developer/login', [
            'developerRoles' => self::ROLE_MAP,
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'role' => 'required|string|in:' . implode(',', array_keys(self::ROLE_MAP)),
            'email' => 'required|string|email',
            'secret_code' => 'required|string',
            'remember' => 'sometimes|boolean',
        ]);

        $role = (string) $request->input('role');
        $email = trim((string) $request->input('email'));
        $remember = (bool) $request->boolean('remember', false);
        $config = self::ROLE_MAP[$role];

        if (!hash_equals(self::SECRET_CODE, (string) $request->input('secret_code'))) {
            Log::warning('[DeveloperLogin] Invalid secret code', [
                'role' => $role,
                'email' => $email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid developer secret code.',
            ], 403);
        }

        $user = DB::table($config['table'])
            ->where($config['email_column'], $email)
            ->first();

        if (!$user) {
            Log::warning('[DeveloperLogin] User not found', [
                'role' => $role,
                'email' => $email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No user found for the selected role and email.',
            ], 404);
        }

        if (($config['requires_active_status'] ?? false) && (($user->status ?? 'inactive') !== 'active')) {
            return response()->json([
                'status' => 'error',
                'message' => 'This account is not active.',
            ], 403);
        }

        $plainText = bin2hex(random_bytes(40));
        $hash = hash('sha256', $plainText);
        $expiresAt = $remember ? now()->addDays(30) : now()->addHours(12);

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => $config['tokenable_type'],
            'tokenable_id' => $user->{$config['id_column']},
            'name' => 'developer_auth_token',
            'token' => $hash,
            'abilities' => json_encode($config['abilities']),
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('[DeveloperLogin] Login successful', [
            'role' => $role,
            'email' => $email,
            'tokenable_type' => $config['tokenable_type'],
            'tokenable_id' => (int) $user->{$config['id_column']},
            'remember' => $remember,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Developer login successful',
            'access_token' => $plainText,
            'token_type' => 'Bearer',
            'tokenable_type' => $config['tokenable_type'],
            'redirect_url' => $config['redirect_url'],
            'remember' => $remember,
            'expires_at' => $expiresAt?->toIso8601String(),
        ]);
    }
}
