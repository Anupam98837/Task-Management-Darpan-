<?php
 
namespace App\Services;
 
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
final class DynamicMail
{
    /**
     * Configure a mailer at runtime from the owner's DEFAULT mailer row.
     * Accepts multiple owner_type synonyms (admin|admins|App\Models\Admin, user|users|App\Models\User).
     * Falls back to a global row ('*',0) if present, then to config('mail.default').
     */
    public static function resolveForOwner(string $ownerType, int $ownerId): string
    {
        // 1) Try exact/normalized owner types for this owner
        $cands = self::ownerTypeCandidates($ownerType);
 
        $qb = fn() => DB::table('mailer_settings')
            ->whereIn('owner_type', $cands)
            ->where('owner_id', $ownerId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            });
 
        // Prefer explicit default for this owner
        $row = (clone $qb()) // is_default=1 first
            ->where('is_default', 1)
            ->orderByDesc('updated_at')
            ->first();
 
        // Otherwise take newest active row for this owner
        if (!$row) {
            $row = (clone $qb())
                ->orderByDesc('is_default')
                ->orderByDesc('updated_at')
                ->first();
        }
 
        // 2) Global fallback row ('*',0) or ('global',0)/('any',0), if you keep one
        if (!$row) {
            $row = DB::table('mailer_settings')
                ->whereIn('owner_type', ['*', 'global', 'any'])
                ->whereIn('owner_id', [0, -1])
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', 'active');
                })
                ->where('is_default', 1)
                ->orderByDesc('updated_at')
                ->first();
        }
 
        // 3) Final fallback → framework default
        if (!$row) {
            return (string) config('mail.default');
        }
 
        $name   = 'runtime_' . $row->id . '_' . Str::uuid()->toString();
        $driver = strtolower((string) ($row->mailer ?? 'smtp'));
 
        if ($driver === 'smtp') {
            $port = (int) ($row->port ?: 587);
            $enc  = self::normalizeEncryption($row->encryption ?? null, $port);
 
            // decrypt password safely (if plain, keep as-is)
            $password = null;
            try {
                $password = $row->password ? Crypt::decryptString($row->password) : null;
            } catch (\Throwable $e) {
                $password = $row->password;
            }
 
            Config::set("mail.mailers.$name", [
                'transport'    => 'smtp',
                'host'         => (string) $row->host,
                'port'         => $port,
                'encryption'   => $enc,            // 'ssl' | 'tls' | null
                'username'     => $row->username ?: null,
                'password'     => $password,
                'timeout'      => 15,
                'local_domain' => self::guessLocalDomain(
                    (string) ($row->from_address ?? ''),
                    (string) ($row->username ?? ''),
                    (string) ($row->host ?? '')
                ),
            ]);
        } elseif ($driver === 'sendmail') {
            Config::set("mail.mailers.$name", [
                'transport' => 'sendmail',
                'path'      => '/usr/sbin/sendmail -bs',
            ]);
        } elseif ($driver === 'log') {
            Config::set("mail.mailers.$name", ['transport' => 'log']);
        } else {
            // ses/mailgun/postmark etc can be wired similarly
            Config::set("mail.mailers.$name", ['transport' => $driver]);
        }
 
        // Per-mailer "from" fallback
        if (!empty($row->from_address)) {
            Config::set('mail.from.address', (string) $row->from_address);
            if (!empty($row->from_name)) {
                Config::set('mail.from.name', (string) $row->from_name);
            }
        }
 
        return $name;
    }
 
    /** Generate owner_type candidates for robust matching (admin/user synonyms). */
    private static function ownerTypeCandidates(string $type): array
    {
        $raw = trim($type);
        $lc  = strtolower($raw);
 
        // class basename if a namespaced type like App\Models\Admin
        $basename = strtolower(preg_replace('/^.*[\\\\\\/]/', '', $raw)); // 'Admin' -> 'admin'
 
        $norm = function (string $v): string {
            $v = strtolower(trim($v));
            if (in_array($v, ['app\\models\\admin', 'app/models/admin', 'admins', 'admin'], true)) return 'admin';
            if (in_array($v, ['app\\models\\user',  'app/models/user',  'users',  'user'],  true)) return 'user';
            return $v;
        };
 
        // ensure uniqueness, drop empties
        return array_values(array_unique(array_filter([
            $raw, $lc, $basename, $norm($raw), $norm($lc), $norm($basename),
        ], fn($x) => $x !== '')));
    }
 
    /** (unchanged) Normalize encryption based on value/port. */
    private static function normalizeEncryption(?string $raw, int $port): ?string
    {
        $v = strtolower(trim((string) $raw));
        if ($v === 'ssl') return 'ssl';
        if ($v === 'tls' || $v === 'starttls') return 'tls';
        if ($v === '' || $v === 'none' || $v === 'null') { /* fallthrough */ }
 
        if ($port === 465) return 'ssl';
        if (in_array($port, [587, 25], true)) return 'tls';
        return null;
    }
 
    /** (unchanged) Pick a sane EHLO domain. */
    private static function guessLocalDomain(string $from, string $username, string $host): string
    {
        foreach ([$from, $username] as $addr) {
            if ($addr && str_contains($addr, '@')) {
                return substr(strrchr($addr, '@'), 1);
            }
        }
        if ($host && !filter_var($host, FILTER_VALIDATE_IP)) {
            return $host;
        }
        $appHost = parse_url((string) config('app.url', ''), PHP_URL_HOST);
        return $appHost ?: 'localhost';
    }
}