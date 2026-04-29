<?php
 
namespace App\Services;
 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobEventMail;
use App\Services\DynamicMail;
use Illuminate\Mail\Mailables\Address;
 
class JobNotifier
{
    /** Build recipients and de-dupe; $opts: ['admins'=>bool,'client'=>bool,'exclude'=>email] */
    public static function recipients(int $jobId, array $opts = []): array
    {
        $exclude = strtolower(trim($opts['exclude'] ?? ''));
 
        // Active assignees
        $to = DB::table('job_assignees as ja')
            ->join('assigned_people as ap', 'ap.id', '=', 'ja.assigned_person_id')
            ->where('ja.job_id', $jobId)
            ->where('ja.status', 'active')
            ->select('ap.email', 'ap.name')
            ->get()
            ->map(fn($r) => ['email' => strtolower(trim($r->email ?? '')), 'name' => $r->name ?: null])
            ->all();
 
        // Optionally add opted-in admins
        if (!empty($opts['admins'])) {
            $admins = DB::table('admins')->select('email', 'name')->get()
                ->map(fn($r) => ['email' => strtolower(trim($r->email ?? '')), 'name' => $r->name ?: null])
                ->all();
            $to = array_merge($to, $admins);
        }
 
        // Optionally add the client email for this job
        if (!empty($opts['client'])) {
            $c = DB::table('job_details as j')
                ->leftJoin('clients as cl', 'cl.id', '=', 'j.client_id')
                ->where('j.id', $jobId)
                ->first(['cl.email', 'cl.name']);
            if ($c && $c->email) {
                $to[] = ['email' => strtolower(trim($c->email)), 'name' => $c->name ?: null];
            }
        }
 
        // Validate, exclude sender, and de-dupe by email
        $bucket = [];
        foreach ($to as $r) {
            $email = strtolower(trim($r['email'] ?? ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
            if ($exclude !== '' && $email === $exclude) continue;
            $bucket[$email] = ['email' => $email, 'name' => $r['name'] ?? null];
        }
 
        return array_values($bucket);
    }
 
    /**
     * Send emails using the owner's DEFAULT mailer from DB.
     * Never throws back to controller.
     *
     * $ownerType: 'admin' | 'user'  (matches mailer_settings.owner_type)
     * $ownerId  : int                (matches mailer_settings.owner_id)
     */
    public static function notify(string $ownerType, int $ownerId, array $to, array $data): void
    {
        if (empty($to)) {
            Log::info('[notify] No valid recipients', ['data_action' => $data['action'] ?? null]);
            return;
        }
 
        // Build the mailer for this owner (falls back to config('mail.default') if none)
        $mailer = DynamicMail::resolveForOwner($ownerType, $ownerId);
        $payload = $data + ['owner' => ['type' => $ownerType, 'id' => $ownerId]];
 
        Log::info('[notify] start', [
            'ownerType'   => $ownerType,
            'ownerId'     => $ownerId,
            'mailer'      => $mailer,
            'rcpt_count'  => count($to),
            'action'      => $data['action'] ?? null,
        ]);
foreach ($to as $rcp) {
    try {
        $addr = new Address($rcp['email'], $rcp['name'] ?? null);
 
        Mail::mailer($mailer)
            ->to($addr)                               // <- single argument
            ->send(new JobEventMail($payload));
 
        Log::info('[notify] sent', [
            'to'     => $rcp['email'],
            'action' => $data['action'] ?? null,
            'mailer' => $mailer,
        ]);
    } catch (\Throwable $e) {
        Log::warning('[notify] send failed', [
            'to'  => $rcp,
            'err' => $e->getMessage(),
        ]);
    }
}
 
 
    }
}