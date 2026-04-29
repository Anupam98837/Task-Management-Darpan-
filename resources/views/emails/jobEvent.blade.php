<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="color-scheme" content="light dark">
  <meta name="supported-color-schemes" content="light dark">
  <title>{{ $action_label ?? 'Job Update' }} • {{ $client['name'] ?? config('app.name') }}</title>
  <!-- Keep styles minimal; most are inline for email client compatibility -->
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;">
 
@php
  $brand   = config('mail.from.name') ?: config('app.name');
  $logoUrl = asset('assets/media/images/hallienzlogo_light.png'); /* public/assets/media/images/hallienzlogo_light.png */
  $event   = strtolower((string)($action ?? 'updated'));
  $title   = trim((string)($job['title'] ?? 'Job'));
 
  $badge = [
    'created'        => ['#E5F3FF','#084C8A'],    // light bg, text
    'assigned'       => ['#EEF4FF','#1E40AF'],
    'status_changed' => ['#FEF3C7','#92400E'],
    'message'        => ['#E8F7EE','#065F46'],
    'updated'        => ['#F2F4F7','#344054'],
  ][$event] ?? ['#F2F4F7','#344054'];
 
  $clientName = $client['name'] ?? $brand;
  $jobUrl     = $job['url']  ?? null;            // if you pass a URL, a CTA button will render
  $oldSt      = $old_status ?? null;
  $newSt      = $new_status ?? null;
@endphp
 
<!-- hidden preview text -->
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">
  “{{ $title }}” {{ strtolower($action_label ?? 'updated') }} for {{ $clientName }}.
</div>
 
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7fb;">
  <tr>
    <td align="center" style="padding:28px 12px;">
      <!-- card -->
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
             style="max-width:640px;background:#ffffff;border-radius:16px;overflow:hidden;
                    border:1px solid #eef1f6;box-shadow:0 1px 3px rgba(16,24,40,.06);">
        <!-- header with logo -->
        <tr>
          <td style="padding:18px 22px;border-bottom:1px solid #EEF1F6;">
            <table role="presentation" width="100%">
              <tr>
                <td style="vertical-align:middle;">
                  <a href="{{ url('/') }}" style="text-decoration:none;display:inline-flex;align-items:center;">
                    <img src="{{ $logoUrl }}" alt="{{ $brand }}" width="132" height="auto"
                         style="display:block;border:0;outline:0;text-decoration:none;height:auto;max-width:100%;">
                  </a>
                </td>
                <td align="right" style="vertical-align:middle;font:12px system-ui,-apple-system,Segoe UI,Roboto,Arial;color:#667085;">
                  Job notification
                </td>
              </tr>
            </table>
          </td>
        </tr>
 
        <!-- body -->
        <tr>
          <td style="padding:22px;">
            @if(!empty($recipient_name))
              <div style="margin:0 0 8px 0;color:#344054;font:600 14px system-ui,-apple-system,Segoe UI,Roboto,Arial;">
                Hi {{ $recipient_name }},
              </div>
            @endif
 
            <div style="margin:0 0 16px 0;color:#475467;font:14px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Arial;">
              {{ $intro ?? 'There is an update on the job below.' }}
            </div>
 
            <!-- badge + title block -->
            <div style="border:1px solid #EEF2F7;border-radius:14px;padding:16px 16px 12px 16px;margin-bottom:16px;">
              <span style="display:inline-block;padding:6px 12px;border-radius:999px;background:{{ $badge[0] }};
                           color:{{ $badge[1] }};font:600 12px/1 system-ui,-apple-system,Segoe UI,Roboto,Arial;
                           border:1px solid rgba(2,6,23,.06);margin-bottom:10px;">
                {{ $action_label ?? 'Updated' }}
              </span>
 
              <div style="font:700 18px/1.3 system-ui,-apple-system,Segoe UI,Roboto,Arial;color:#1F2937;margin:4px 0 8px;">
                {{ $title }}
              </div>
 
              <!-- summary key-values (responsive table) -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                     style="border-collapse:separate;border-spacing:0 8px;font:13px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Arial;color:#475467;">
                <tr>
                  <td style="width:160px;color:#667085;">Client</td>
                  <td style="font-weight:600;color:#344054;">{{ $clientName }}</td>
                </tr>
                @if(!empty($job['status']))
                <tr>
                  <td style="width:160px;color:#667085;">Status</td>
                  <td style="font-weight:600;color:#344054;">{{ ucwords(str_replace('_',' ', $job['status'])) }}</td>
                </tr>
                @endif
                @if($oldSt || $newSt)
                <tr>
                  <td style="width:160px;color:#667085;">Changed</td>
                  <td style="font-weight:600;color:#344054;">
                    {{ $oldSt ?? '—' }} <span style="opacity:.6;">→</span> {{ $newSt ?? '—' }}
                  </td>
                </tr>
                @endif
                @if(!empty($actor['name']) || !empty($actor['role']))
                <tr>
                  <td style="width:160px;color:#667085;">By</td>
                  <td style="font-weight:600;color:#344054;">
                    {{ $actor['name'] ?? '—' }}{{ !empty($actor['role']) ? ' ('.ucwords($actor['role']).')' : '' }}
                  </td>
                </tr>
                @endif
              </table>
 
              @if(!empty($note))
                <div style="margin-top:8px;padding:12px;border:1px dashed #E5E7EB;border-radius:10px;background:#FAFAFB;color:#374151;font:13px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Arial;">
                  <strong style="display:block;margin-bottom:6px;color:#111827;">Note</strong>
                  {!! nl2br(e($note)) !!}
                </div>
              @endif
            </div>
 
            @if($jobUrl)
              <!-- CTA button (bulletproof) -->
              <table role="presentation" cellpadding="0" cellspacing="0" style="margin:4px 0 4px 0;">
                <tr>
                  <td align="left" style="border-radius:10px;" bgcolor="#1D4ED8">
                    <a href="{{ $jobUrl }}"
                       style="display:inline-block;padding:11px 16px;border-radius:10px;background:#1D4ED8;
                              color:#ffffff;font:600 14px system-ui,-apple-system,Segoe UI,Roboto,Arial;
                              text-decoration:none;">
                      View Job
                    </a>
                  </td>
                </tr>
              </table>
            @endif
 
            <div style="margin-top:18px;color:#667085;font:12px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Arial;">
              You’re receiving this because you’re associated with {{ $clientName }}.
            </div>
          </td>
        </tr>
 
        <!-- footer -->
        <tr>
          <td style="padding:14px 22px;background:#F8FAFC;border-top:1px solid #EEF1F6;color:#667085;font:12px system-ui,-apple-system,Segoe UI,Roboto,Arial;">
            © {{ date('Y') }} {{ $brand }}. All rights reserved.
          </td>
        </tr>
      </table>
      <!-- /card -->
    </td>
  </tr>
</table>
</body>
</html>