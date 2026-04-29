<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<title>Job Messages - {{ $job->id ?? '' }}</title>
<style>
    :root{
      --primary-color:   #0369a1;
      --secondary-color: #0ea5e9;
      --accent-color:    #38bdf8;

      /* Neutrals */
      --bg-body:         #ffffff;
      --surface:         #ffffff;
      --light-color:     #f3f4f6;
      --border-color:    #d1d5db;
      --text-color:      #334155;
      --muted-color:     #6b7280;

      /* States */
      --info-color:      #38bdf8;
      --success-color:   #16a34a;
      --warning-color:   #f59e0b;
      --danger-color:    #ef4444;

      /* Shape / Motion */
      --radius-sm:       6px;
      --radius-md:       10px;
      --radius-lg:       14px;
      --shadow-sm:       0 1px 2px rgba(15, 23, 42, .06);
      --shadow-md:       0 8px 24px rgba(15, 23, 42, .12);
      --transition:      all .18s ease;

      /* Type & sizing */
      --font-sans:       'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      --font-head:       'Poppins', var(--font-sans);
      --btn-font-size:   13.5px;
      --table-font-size: 13px;
      --th-font-size:    12.5px;
    }

    html, body { margin:0; padding:0; }
    body{
        font-family: var(--font-sans), "DejaVu Sans", sans-serif;
        font-size:12px; color:var(--text-color); background:var(--bg-body);
        padding:20px 24px 64px; line-height:1.5;
    }

    /* Header */
    header{
        border:1px solid var(--border-color);
        background: linear-gradient(0deg,
            rgba(56,189,248,.10),
            rgba(14,165,233,.08));
        border-radius: var(--radius-lg);
        padding:14px 16px; text-align:center; margin-bottom:14px;
    }
    .title{
        margin:0 0 6px; font-size:18px; font-weight:800; color:var(--primary-color);
        letter-spacing:.2px; font-family: var(--font-head), "DejaVu Sans", sans-serif;
    }
    .meta{ font-size:10px; color:var(--muted-color); }
    .meta strong{ color:var(--text-color); }

    /* Section heading + chip */
    h2{
        font-size:13px; margin:18px 0 10px; color:var(--primary-color);
        padding:8px 10px; border:1px solid var(--border-color); background:var(--surface);
        border-left:6px solid var(--secondary-color); border-radius: var(--radius-md);
    }
    .chip{
        display:inline-block; margin-left:8px; padding:2px 10px;
        background: rgba(56,189,248,.18);
        color: var(--primary-color);
        border:1px solid var(--border-color);
        border-radius:999px; font-size:10px; font-weight:700; vertical-align:middle;
    }

    /* Message card */
    .role-section{ page-break-inside:avoid; }
    .msg{
        padding:10px 12px; margin-bottom:10px;
        border:1px solid var(--border-color);
        background: linear-gradient(0deg, #fff, rgba(14,165,233,.04));
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        page-break-inside:avoid;
    }
    .msg .meta{ font-size:10px; color:var(--muted-color); margin-bottom:8px; }
    .content pre{
        white-space:pre-wrap; word-wrap:break-word;
        background:#fff; padding:10px; border-radius: var(--radius-md);
        border-left:3px solid var(--accent-color);
        border:1px solid var(--border-color);
        font-family:"DejaVu Sans Mono","DejaVu Sans",monospace; font-size:11px; line-height:1.55;
    }

    /* Attachments */
    .attachments{ font-size:11px; color:var(--text-color); margin-top:8px; }
    .attachments ul{ margin:6px 0 0 18px; padding:0; }
    .attachments li{ margin:2px 0; }
    a{ color:#0b5dd7; text-decoration:none; border-bottom:1px solid rgba(11,93,215,.25); }
    a:hover{ text-decoration:underline; }

    /* Footer */
    footer .foot{
        position:fixed; left:24px; right:24px; bottom:10px;
        font-size:10px; color:var(--muted-color); text-align:center;
        border-top:1px solid var(--border-color); padding-top:6px;
        background:linear-gradient(0deg, rgba(3,105,161,.06), rgba(56,189,248,.04));
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
</style>
</head>
<body>
<header>
    <h1 class="title">Job Messages</h1>
    <div class="meta">
        <strong>Job ID:</strong> {{ $job->id }}
        &nbsp;•&nbsp; <strong>Title:</strong> {{ $job->title ?? '—' }}
        &nbsp;•&nbsp; <strong>Generated:</strong> {{ $generated_at }}
    </div>
</header>

@foreach($groups as $role => $rows)
    <div class="role-section">
        <h2>{{ ucfirst($role) }} <span class="chip">{{ count($rows) }}</span></h2>

        @foreach($rows as $m)
            <div class="msg">
               <div class="meta">
    <strong>#{{ $m->id }}</strong>
    &nbsp;|&nbsp;
    <em>
      {{-- Prefer sender_name if provided, otherwise fall back to role --}}
      {{ $m->sender_name ?? ($m->sender_role ? ucfirst($m->sender_role) : 'Unknown') }}

      {{-- keep sender_type and id optionally, only if available --}}
      @if(!empty($m->sender_type))
        / {{ $m->sender_type }}
      @endif

      @if(!empty($m->sender_id))
        #{{ $m->sender_id }}
      @endif
    </em>
    &nbsp;|&nbsp;
    <span>{{ \Carbon\Carbon::parse($m->created_at)->toDateTimeString() }}</span>

    {{-- optional: show email/phone if resolved --}}
    @if(!empty($m->sender_email) || !empty($m->sender_phone))
      <div style="font-size:10px;color:var(--muted-color);margin-top:6px">
        @if(!empty($m->sender_email)) <span>{{ $m->sender_email }}</span>@endif
        @if(!empty($m->sender_phone)) &nbsp;·&nbsp; <span>{{ $m->sender_phone }}</span>@endif
      </div>
    @endif
</div>

                <div class="content">
                    @if($m->message_text)
                        <pre>{{ $m->message_text }}</pre>
                    @elseif($m->message_html)
                        {!! $m->message_html !!}
                    @else
                        <em style="color:var(--muted-color)">(no text)</em>
                    @endif
                </div>

                @if($include_attachments && !empty($m->attachments))
                    <div class="attachments">
                        <strong>Attachments:</strong>
                        <ul>
                            @foreach($m->attachments as $att)
                                <li>
                                    {{ $att['original_name'] ?? ($att['stored_name'] ?? 'file') }}
                                    @if(!empty($att['absolute_url'])) —
                                    <a href="{{ $att['absolute_url'] }}">{{ $att['absolute_url'] }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endforeach

<footer>
    <div class="foot">Exported from {{ config('app.name') }} on {{ now()->toDateTimeString() }}</div>
</footer>
</body>
</html>
