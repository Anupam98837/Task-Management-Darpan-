<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthContextController extends Controller
{
    public function __invoke(Request $request)
    {
        $role = (string) ($request->attributes->get('auth_role') ?? '');
        $id = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);
        $type = (string) ($request->attributes->get('auth_tokenable_type') ?? '');

        if ($role === '' || $id <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        [$table, $nameColumn, $emailColumn] = match ($role) {
            'admin' => ['admins', 'name', 'email'],
            'assignee' => ['assigned_people', 'name', 'email'],
            'client_user' => ['client_users', 'name', 'email'],
            'accountant_user' => ['accountant_users', 'name', 'email'],
            default => [null, null, null],
        };

        if (!$table) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $row = DB::table($table)
            ->where('id', $id)
            ->select('id', "{$nameColumn} as name", "{$emailColumn} as email")
            ->first();

        if (!$row) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => (int) $row->id,
                'role' => $role,
                'tokenable_type' => $type,
                'name' => (string) ($row->name ?? ''),
                'email' => (string) ($row->email ?? ''),
            ],
        ]);
    }
}
