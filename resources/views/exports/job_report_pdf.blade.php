<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Report - {{ $report['job']['id'] ?? 'N/A' }}</title>
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

        html, body { margin:0; padding:0; }
        body{
            font-family: var(--font-sans), "DejaVu Sans", sans-serif;
            font-size:12px; color:var(--text-color); margin:20px; line-height:1.5; background:var(--bg-body);
        }

        .header{
            border:1px solid var(--border-color);
            background: linear-gradient(0deg, rgba(56,189,248,.12), rgba(3,105,161,.08));
            border-radius: var(--radius-lg); padding:14px 16px; margin-bottom:16px;
        }
        h1{ margin:0 0 6px; font-size:18px; color:var(--primary-color); letter-spacing:.2px; font-family: var(--font-head), "DejaVu Sans", sans-serif; }
        .meta{ font-size:11px; color:var(--muted-color); }
        .muted{ color:var(--muted-color); font-size:11px; }

        h2{
            font-size:14px; margin:18px 0 10px; color:var(--primary-color);
            padding:8px 10px; border:1px solid var(--border-color); background:var(--surface);
            border-left:6px solid var(--secondary-color); border-radius: var(--radius-md);
        }
        p{ margin:4px 0; }

        table{ width:100%; border-collapse:collapse; margin-top:8px; }
        th, td{ border:1px solid var(--border-color); padding:7px 9px; text-align:left; vertical-align:top; font-size: var(--table-font-size); }
        th{
            background: linear-gradient(0deg, rgba(56,189,248,.12), rgba(14,165,233,.08));
            color: var(--primary-color); font-weight:700; font-size: var(--th-font-size);
        }
        tr:nth-child(even) td{ background:#fdfefe; }

        .note{
            background:#fff; padding:10px; border:1px solid var(--border-color); border-left:3px solid var(--accent-color);
            border-radius: var(--radius-md);
        }

        .kpis{ display:flex; flex-wrap:wrap; gap:10px; margin-top:8px; }
        .kpi{
            padding:10px 14px; background:#fff; border-radius: var(--radius-lg); font-weight:800; color:var(--primary-color);
            min-width:180px; box-shadow: var(--shadow-sm);
        }

        .media-grid{ display:flex; flex-wrap:wrap; gap:10px; margin-top:8px; }
        .media-item{
            width:160px; word-wrap:break-word; font-size:11px;
            border:1px solid var(--border-color); border-radius: var(--radius-md); background:#fff; padding:8px;
        }

        a{ color:#0b5dd7; text-decoration:none; border-bottom:1px solid rgba(11,93,215,.25); }
        a:hover{ text-decoration:underline; }
        /* make images behave for screen and for print */
img {
  max-width: 100%;
  height: auto;
  display: block;
  margin: 8px 0;
  page-break-inside: avoid;
}

/* optionally cap really large images for print output */
@media print {
  img { max-width: 100%; height: auto; }
  .note img, .content img { max-width: 400px; } /* tweak max-width to taste */
}

    </style>
</head>
<body>
    <div class="header">
        <h1>Job Report — {{ $report['job']['title'] ?? 'Untitled' }} (#{{ $report['job']['id'] ?? 'N/A' }})</h1>
        <div class="meta">
            <span>Generated: {{ $generated_at ?? ($report['meta']['generated_at'] ?? now()->toDateTimeString()) }}</span>
            &nbsp;•&nbsp;
            <span>Status: <strong>{{ $report['job']['status'] ?? 'N/A' }}</strong></span>
            &nbsp;•&nbsp;
            <span>Priority: <strong>{{ $report['job']['priority'] ?? 'N/A' }}</strong></span>
        </div>
        <div style="margin-top:6px;" class="muted">
            Client: {{ $report['client']['name'] ?? '—' }} @if(!empty($report['client']['email'])) ({{ $report['client']['email'] }}) @endif
            &nbsp;•&nbsp; Document: {{ $report['document']['name'] ?? '—' }}
        </div>
    </div>

    <div class="section" id="overview">
        <h2>Overview</h2>
        <p class="muted"><strong>Created:</strong> {{ $report['job']['created_at'] ?? '—' }} &nbsp; <strong>Updated:</strong> {{ $report['job']['updated_at'] ?? '—' }}</p>
        <div class="note">
            {!! $report['job']['description'] ?? '<span class="muted">No description provided.</span>' !!}
        </div>
    </div>

    <div class="section" id="kpis">
        <h2>Key Metrics</h2>
        <div class="kpis">
            <div class="kpi">Open Subtasks: {{ $report['kpis']['open_children'] ?? 0 }}</div>
            <div class="kpi">Total Attachments: {{ $report['kpis']['attachments_total'] ?? 0 }}</div>

            {{-- prefer explicit assignees_count; fallback safely to count() if report['assignees'] is countable --}}
            <div class="kpi">
                Assignees:
                {{
                    $report['assignees_count']
                        ?? (is_countable($report['assignees'] ?? null) ? count($report['assignees']) : 0)
                }}
            </div>

            {{-- messages total kept but messages listing removed --}}
            <div class="kpi">
                Messages:
                {{ $report['messages_summary']['count'] ?? (is_countable($report['messages'] ?? null) ? (is_array($report['messages']) && array_keys($report['messages']) !== range(0,count($report['messages'])-1) ? array_reduce($report['messages'], fn($c,$g)=> $c + count($g), 0) : count($report['messages'])) : 0) }}
            </div>
        </div>
    </div>

    @if(!empty($report['assignees']))
    <div class="section" id="assignees">
        <h2>Assignees ({{ $report['assignees_count'] ?? (is_countable($report['assignees'] ?? null) ? count($report['assignees']) : 0) }})</h2>
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Assigned At</th><th>Status</th><th>Note</th></tr></thead>
            <tbody>
                @foreach($report['assignees'] as $a)
                <tr>
                    <td>{{ $a['name'] ?? '—' }}</td>
                    <td>{{ $a['email'] ?? '—' }}</td>
                    <td>{{ $a['phone'] ?? '—' }}</td>
                    <td>{{ $a['assigned_at'] ?? '—' }}</td>
                    <td>{{ $a['map_status'] ?? '—' }}</td>
                    <td>{{ $a['note'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(!empty($report['children']))
    <div class="section" id="children">
        <h2>Subtasks / Children ({{ $report['children_summary']['count'] ?? count($report['children']) }})</h2>
        <table>
            <thead><tr><th>ID</th><th>Title</th><th>Status</th><th>Priority</th><th>Assignees</th></tr></thead>
            <tbody>
                @foreach($report['children'] as $c)
                <tr>
                    <td>{{ $c['id'] }}</td>
                    <td>{{ $c['title'] }}</td>
                    <td>{{ $c['status'] ?? '—' }}</td>
                    <td>{{ $c['priority'] ?? '—' }}</td>
                    <td>{{ $c['assignees_count'] ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Messages list intentionally removed; totals kept above --}}

    @if(!empty($report['media']))
    <div class="section" id="media">
        <h2>Media / Files</h2>
        <div class="media-grid">
            @foreach($report['media'] as $m)
                <div class="media-item">
                    <div><strong>{{ $m['title'] ?? '—' }}</strong></div>
                    <div class="muted" style="word-break:break-all;">{{ $m['relative_url'] ?? ($m['absolute_url'] ?? '') }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(!empty($report['activity']))
    <div class="section" id="activity">
        <h2>Activity Log (recent)</h2>
        <table>
            <thead><tr><th>When</th><th>By (role)</th><th>Activity</th><th>Note</th></tr></thead>
            <tbody>
                @foreach($report['activity'] as $a)
                <tr>
                    <td>{{ $a['created_at'] ?? '' }}</td>
                    <td>{{ $a['performed_by'] ?? '' }} ({{ $a['performed_by_role'] ?? '' }})</td>
                    <td>{{ $a['activity'] ?? '' }}</td>
                    <td class="muted">{{ $a['log_note'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div style="margin-top:28px;" class="muted">
        Exported by: {{ $report['meta']['requested_by']['role'] ?? ($report['meta']['requested_by']['type'] ?? 'system') }}
    </div>
</body>
</html>
