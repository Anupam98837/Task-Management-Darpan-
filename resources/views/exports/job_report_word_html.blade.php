<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Report - {{ $report['job']['title'] ?? 'Job' }}</title>
    <style>
        :root{
          --primary-color:#0369a1; --secondary-color:#0ea5e9; --accent-color:#38bdf8;
          --bg-body:#ffffff; --surface:#ffffff; --light-color:#f3f4f6; --border-color:#d1d5db;
          --text-color:#334155; --muted-color:#6b7280;
          --info-color:#38bdf8; --success-color:#16a34a; --warning-color:#f59e0b; --danger-color:#ef4444;
          --radius-sm:6px; --radius-md:10px; --radius-lg:14px;
          --shadow-sm:0 1px 2px rgba(15,23,42,.06); --shadow-md:0 8px 24px rgba(15,23,42,.12);
          --transition:all .18s ease;
          --font-sans:'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
          --font-head:'Poppins', var(--font-sans);
          --table-font-size:13px; --th-font-size:12.5px;
        }

        body{ font-family: var(--font-sans); font-size:12pt; color:var(--text-color); margin:20px; line-height:1.55; background:var(--bg-body); }

        .card-head{
            border:1px solid var(--border-color);
            background: linear-gradient(0deg, rgba(56,189,248,.14), rgba(3,105,161,.10));
            border-radius: var(--radius-lg); padding:16px 18px; margin-bottom:16px;
        }
        h1{ font-size:20pt; margin:0 0 6px; color:var(--primary-color); letter-spacing:.2px; font-family: var(--font-head); }
        .muted{ color:var(--muted-color); font-size:10.5pt; }
        .meta{ color:var(--muted-color); font-size:10.5pt; }
        .badge{
            display:inline-block; background:rgba(56,189,248,.22); color:var(--primary-color);
            border:1px solid var(--border-color); border-radius:999px; padding:2px 10px; font-weight:700; font-size:10pt;
        }

        h2{
            font-size:16pt; margin-top:16px; margin-bottom:8px; color:var(--primary-color);
            padding:8px 10px; border:1px solid var(--border-color); background:var(--surface);
            border-left:6px solid var(--secondary-color); border-radius: var(--radius-md);
        }

        p{ margin:4px 0; }

        table{ width:100%; border-collapse:collapse; margin-top:8px; }
        th, td{ border:1px solid var(--border-color); padding:7px 9px; text-align:left; vertical-align:top; font-size: var(--table-font-size); }
        th{
            background: linear-gradient(0deg, rgba(56,189,248,.14), rgba(14,165,233,.10));
            color: var(--primary-color); font-size: var(--th-font-size);
        }

        .note{
            background:#fff; padding:12px; border:1px solid var(--border-color);
            border-left:3px solid var(--accent-color); border-radius: var(--radius-lg);
        }

        .kpis{ display:flex; flex-wrap:wrap; gap:10px; margin-top:8px; }
        .kpi{
            padding:10px 14px; background:#fff; border-radius: var(--radius-lg); font-weight:800; color:var(--primary-color);
            min-width:160px; box-shadow: var(--shadow-sm);
        }

        a{ color:#0b5dd7; text-decoration:none; border-bottom:1px solid rgba(11,93,215,.25); }
        a:hover{ text-decoration:underline; }
    </style>
</head>
<body>
    <div class="card-head">
        <h1>Job Report — {{ $report['job']['title'] ?? 'Untitled' }}</h1>
        <p class="meta">
            <span class="badge">Job ID: {{ $report['job']['id'] ?? 'N/A' }}</span>
            &nbsp;•&nbsp; Status: <strong>{{ $report['job']['status'] ?? '—' }}</strong>
            &nbsp;•&nbsp; Priority: <strong>{{ $report['job']['priority'] ?? '—' }}</strong>
            &nbsp;•&nbsp; Generated: {{ $generated_at ?? ($report['meta']['generated_at'] ?? now()->toDateTimeString()) }}
        </p>
        <p class="muted">
            <strong>Client:</strong> {{ $report['client']['name'] ?? '—' }} @if(!empty($report['client']['email'])) ({{ $report['client']['email'] }}) @endif
            &nbsp;•&nbsp; <strong>Document:</strong> {{ $report['document']['name'] ?? '—' }}
        </p>
    </div>

    <h2>Overview</h2>
    <div class="note">
        {!! $report['job']['description'] ?? '<em class="muted">No description provided.</em>' !!}
    </div>

    {{-- Key Metrics (includes messages count but no message listing) --}}
    <h2>Key Metrics</h2>
    <div class="kpis" aria-hidden="false">
        <div class="kpi">Messages: {{ $report['messages_summary']['count'] ?? (is_array($report['messages'] ?? null) ? array_reduce($report['messages'], function($carry,$g){ return $carry + count($g); }, 0) : (count($report['messages'] ?? []))) }}</div>
        <div class="kpi">Assignees: {{ count($report['assignees'] ?? []) }}</div>
        <div class="kpi">Subtasks: {{ $report['children_summary']['count'] ?? count($report['children'] ?? []) }}</div>
    </div>

    @if(!empty($report['assignees']))
    <h2>Assignees</h2>
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Assigned At</th></tr></thead>
        <tbody>
            @foreach($report['assignees'] as $a)
            <tr>
                <td>{{ $a['name'] ?? '—' }}</td>
                <td>{{ $a['email'] ?? '—' }}</td>
                <td>{{ $a['phone'] ?? '—' }}</td>
                <td>{{ $a['assigned_at'] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(!empty($report['children']))
    <h2>Subtasks</h2>
    <table>
        <thead><tr><th>ID</th><th>Title</th><th>Status</th><th>Priority</th></tr></thead>
        <tbody>
            @foreach($report['children'] as $c)
            <tr>
                <td>{{ $c['id'] }}</td>
                <td>{{ $c['title'] }}</td>
                <td>{{ $c['status'] ?? '—' }}</td>
                <td>{{ $c['priority'] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Messages listing removed on purpose. Only total kept above. --}}

    @if(!empty($report['media']))
    <h2>Media & Files</h2>
    <ul>
        @foreach($report['media'] as $m)
            <li>{{ $m['title'] ?? '—' }} — {{ $m['relative_url'] ?? ($m['absolute_url'] ?? '—') }}</li>
        @endforeach
    </ul>
    @endif

    @if(!empty($report['activity']))
    <h2>Activity (recent)</h2>
    <table>
        <thead><tr><th style="width:170px;">When</th><th style="width:240px;">By</th><th>Activity</th></tr></thead>
        <tbody>
            @foreach($report['activity'] as $a)
            <tr>
                <td>{{ $a['created_at'] ?? '' }}</td>
                <td>{{ $a['performed_by'] ?? '' }} ({{ $a['performed_by_role'] ?? '' }})</td>
                <td>{{ $a['activity'] ?? '' }} — <span class="muted">{{ $a['log_note'] ?? '' }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <p class="muted" style="margin-top:20px;">
        Exported by: {{ $report['meta']['requested_by']['role'] ?? ($report['meta']['requested_by']['type'] ?? 'system') }}
        — {{ $generated_at ?? now()->toDateTimeString() }}
    </p>
</body>
</html>
