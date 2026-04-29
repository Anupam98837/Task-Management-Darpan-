<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Canonical role names we support.
     * Add new ones here.
     */
    private const ROLES = ['admin', 'user', 'assignee', 'client_user'];

    /**
     * Synonyms accepted for each role (lowercased, singular where possible).
     * Extend freely as your models/DB naming evolves.
     */
    private const SYNONYMS = [
        'admin' => [
            'administrator', 'admins', 'adminuser', 'superadmin', 'super-admin', 'superadministrator',
        ],
        'user'  => [
            'member', 'users', 'enduser', 'end-user', 'basicuser', 'standarduser',
        ],
        'assignee' => [
            // common variants
            'assignee', 'assignees', 'assigned', 'assigneduser', 'assignedperson', 'assignedpeople',
            'taskassignee', 'taskowner', 'issueassignee', 'owner',
            // model-style slugs you might see in tokenable_type
            'assigneeuser', 'assigneeaccount', 'assignedpersonmodel', 'assignedpeoplemodel',
        ],
        'client_user' => [
            'clientuser', 'clientusers', 'client_user', 'client_users', 'clientaccount',
            'clientportaluser', 'clientmember', 'portalclientuser',
        ],
    ];

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $allowed = $this->expandAllowed($roles);
        if (!$allowed) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        $plaintext = $this->extractToken($request);
        if (!$plaintext) {
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        $hashed = hash('sha256', $plaintext);

        // Look up token (Sanctum-style)
        $q = DB::table('personal_access_tokens')->where('token', $hashed);

        // LOCAL DEV convenience: if plaintext was accidentally stored, also match it
        if (App::environment('local')) {
            $q->orWhere('token', $plaintext);
        }

        $record = $q->first();
        if (!$record) {
            $this->debug('token_not_found', $plaintext, null, $roles);
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        $typeRaw  = (string) ($record->tokenable_type ?? '');
        $typeNorm = $this->normalizeType($typeRaw);

        // Try to infer alias from tokenable_type…
        $alias = $this->mapToAlias($typeNorm);

        // …or fall back to abilities like ["role:assignee", ...]
        if (!$alias) {
            $alias = $this->aliasFromAbilities($record->abilities ?? null);
        }

        // Enforce route-level allowed roles
        if (!$alias || !in_array($alias, $allowed, true)) {
            $this->debug('type_not_allowed', $plaintext, $record, $roles, [
                'type_raw'  => $typeRaw,
                'type_norm' => $typeNorm,
                'alias'     => $alias,
                'allowed'   => $allowed,
            ]);
            return response()->json(['error' => 'Unauthorized Access'], 403);
        }

        // Attach identity for controllers
        $request->attributes->set('auth_role', $alias);
        $request->attributes->set('auth_tokenable_type', $typeRaw);
        $request->attributes->set('auth_tokenable_id', $record->tokenable_id ?? null);

        $this->debug('ok', $plaintext, $record, $roles, ['alias' => $alias]);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $h = $request->header('Authorization', '');
        if (stripos($h, 'Bearer ') === 0) return trim(substr($h, 7));
        $h = trim($h);
        return $h !== '' ? $h : null;
    }

    /** Expand allowed with synonyms (lowercase) */
    private function expandAllowed(array $roles): array
    {
        $out = [];
        foreach ($roles as $r) {
            $r = strtolower(trim((string) $r));
            if (!$r) continue;
            $out[] = $r;
            if (isset(self::SYNONYMS[$r])) {
                $out = array_merge($out, self::SYNONYMS[$r]);
            }
        }
        return array_values(array_unique($out));
    }

    /**
     * Normalize tokenable_type to a comparable slug.
     * Example: App\Models\AssignedPerson -> assignedperson
     */
    private function normalizeType(string $type): string
    {
        // keep basename, lower, strip non-letters, naive singularize trailing 's'
        $base = strtolower(preg_replace('~^.*\\\\~', '', $type));     // e.g. AssignedPeople
        $slug = strtolower(preg_replace('/[^a-z]/', '', $base));      // assignedpeople
        if ($slug !== 'class' && strlen($slug) > 3 && str_ends_with($slug, 's')) {
            $slug = substr($slug, 0, -1);                             // people(s) -> people; assignees -> assignee
        }
        return $slug;
    }

    /**
     * Map normalized type (or free string) to canonical alias 'admin'|'user'|'assignee' if possible.
     */
    private function mapToAlias(string $norm): ?string
    {
        if (in_array($norm, self::ROLES, true)) return $norm;

        foreach (self::SYNONYMS as $alias => $alts) {
            if (in_array($norm, $alts, true)) return $alias;
        }

        // Heuristics for common model/class-name patterns
        if (str_starts_with($norm, 'admin')) return 'admin';
        if (str_ends_with($norm, 'user'))    return 'user';
        if (
            str_contains($norm, 'assignee') ||
            str_starts_with($norm, 'assign') ||     // AssignedPerson, AssignUser, etc.
            str_contains($norm, 'assignedperson') ||
            str_contains($norm, 'assignedpeople')
        ) {
            return 'assignee';
        }
        if (
            str_contains($norm, 'clientuser') ||
            str_contains($norm, 'clientaccount') ||
            str_contains($norm, 'clientmember')
        ) {
            return 'client_user';
        }

        return null;
    }

    /** Parse abilities JSON/array for entries like "role:assignee" */
    private function aliasFromAbilities($raw): ?string
    {
        $arr = is_string($raw) ? json_decode($raw, true) : (is_array($raw) ? $raw : null);
        if (!is_array($arr)) return null;
        foreach ($arr as $a) {
            if (is_string($a) && str_starts_with($a, 'role:')) {
                $r = strtolower(trim(substr($a, 5)));
                if (in_array($r, self::ROLES, true)) return $r;
            }
        }
        return null;
    }

    /** Structured logs in local dev only */
    private function debug(string $event, string $plaintext, $record, array $routeRoles, array $extra = []): void
    {
        if (!App::environment('local')) return; // only in local dev
        Log::info('[CheckRole]', array_merge([
            'event'       => $event,
            'bearer_head' => substr($plaintext, 0, 6), // don't log full token
            'route_roles' => $routeRoles,
            'db_present'  => (bool) $record,
        ], $extra));
    }
}
