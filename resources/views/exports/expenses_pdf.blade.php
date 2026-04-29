<!doctype html>
<html>
<head>
<meta charset="utf-8"/>
<title>Job Expenses - {{ $job->id ?? '' }}</title>
<style>
    @page { margin: 20mm; }
    :root{
      --primary-color:   #0369a1;
      --secondary-color: #0ea5e9;
      --accent-color:    #38bdf8;
      --border-color:    #d1d5db;
      --text-color:      #334155;
      --muted-color:     #6b7280;
      --radius-md:       10px;
      --font-sans:       'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    html, body { margin:0; padding:0; }
    body{
        font-family: var(--font-sans), "DejaVu Sans", sans-serif;
        font-size:12px; color:var(--text-color); padding:0; line-height:1.45;
    }

    .wrap{ padding:16px 18px; }

    header{
        border-bottom:1px solid var(--border-color); margin-bottom:12px; padding-bottom:8px;
    }
    .title{ font-size:18px; font-weight:800; color:var(--primary-color); margin:0; }
    .meta{ font-size:11px; color:var(--muted-color); margin-top:6px; }

    .section{ margin-top:14px; page-break-inside:avoid; }
    .section h2{ font-size:13px; margin:0 0 8px; color:var(--primary-color); padding:6px 8px; border-left:6px solid var(--secondary-color); background:#fff; border:1px solid var(--border-color); border-radius:8px; }

    .expense-row{ border:1px solid var(--border-color); padding:10px; border-radius:8px; margin-bottom:10px; background:#fff; }
    .expense-row .meta{ font-size:11px; color:var(--muted-color); margin-bottom:6px; }

    .note{ white-space:pre-wrap; word-wrap:break-word; font-size:12px; color:var(--text-color); }

    .attachments{ font-size:11px; margin-top:6px; }
    .attachments ul{ margin:6px 0 0 18px; padding:0; }
    .attachments li{ margin:2px 0; }

    footer { position:fixed; bottom:10px; left:0; right:0; text-align:center; font-size:10px; color:var(--muted-color); border-top:1px solid var(--border-color); padding-top:6px; background:transparent; }

    /* Print helpers */
    .page-break { page-break-after: always; }
    img.inline-image { max-width: 100%; height: auto; display:block; margin-top:8px; border:1px solid var(--border-color); border-radius:6px; }

    /* Summary table */
    .summary { margin-top:12px; border:1px solid var(--border-color); border-radius:8px; background:#fff; overflow:hidden; }
    .summary table { width:100%; border-collapse:collapse; }
    .summary th, .summary td { padding:8px 10px; font-size:12px; border-bottom:1px solid var(--border-color); text-align:left; }
    .summary thead th { background:#f8fafc; color:var(--primary-color); font-weight:700; }
    .summary .muted { color:var(--muted-color); font-size:11px; }
</style>
</head>
<body>
<div class="wrap">
    <header>
        <div class="title">Job Expenses</div>
        <div class="meta">
            <strong>Job ID:</strong> {{ $job->id }} &nbsp; • &nbsp;
            <strong>Title:</strong> {{ $job->title ?? '—' }} &nbsp; • &nbsp;
            <strong>Generated:</strong> {{ $generated_at }}
        </div>
    </header>

    {{-- Ensure $personTotals exists: compute from $groups if controller did not provide it --}}
    @php
        if (!isset($personTotals) || empty($personTotals)) {
            $personTotals = [];
            foreach ($groups as $group => $rows) {
                foreach ($rows as $e) {
                    // determine person label
                    if (!empty($e->creator_name)) {
                        $name = (string) $e->creator_name;
                    } elseif (!empty($e->creator_email)) {
                        $name = (string) $e->creator_email;
                    } else {
                        $name = '#'.((int)($e->created_by ?? 0));
                    }

                    $currency = !empty($e->currency) ? strtoupper((string)$e->currency) : 'INR';
                    $amount = (float) ($e->amount ?? 0);

                    if (!isset($personTotals[$name])) $personTotals[$name] = [];
                    if (!isset($personTotals[$name][$currency])) $personTotals[$name][$currency] = 0.0;

                    $personTotals[$name][$currency] += $amount;
                }
            }

            // sort descending by grand total
            uasort($personTotals, function($a, $b) {
                return (array_sum($b) <=> array_sum($a));
            });
        }
    @endphp

    {{-- Grand totals summary (expects $personTotals = [ name => [currency => amount]] ) --}}
    @if(!empty($personTotals) && is_array($personTotals))
        <section class="section summary" style="page-break-inside:avoid;">
            <h2>Expense Summary <span style="float:right;font-size:11px;color:var(--muted-color)">{{ count($personTotals) }}</span></h2>

            <table aria-label="Grand totals by person">
                <thead>
                    <tr>
                        <th style="width:55%;">Person</th>
                        <th style="width:20%;">Currency</th>
                        <th style="width:25%; text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($personTotals as $person => $currencyTotals)
                        @foreach($currencyTotals as $cur => $amt)
                            <tr>
                                <td>{{ $person }}</td>
                                <td>{{ $cur }}</td>
                                <td style="text-align:right;"><strong>{{ number_format($amt, 2) }}</strong></td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    @foreach($groups as $group => $rows)
        <section class="section">
            <h2>Expense Data</h2>
            <h2>{{ ucfirst($group) }} <span style="float:right;font-size:11px;color:var(--muted-color)">{{ count($rows) }}</span></h2>

            @foreach($rows as $e)
                <div class="expense-row">
                    <div class="meta">
                        <strong>#{{ $e->id }}</strong>
                        &nbsp;|&nbsp; {{ \Carbon\Carbon::parse($e->expense_date)->toDateString() }}
                        &nbsp;|&nbsp; <strong>{{ number_format($e->amount,2) }} {{ $e->currency ?? '' }}</strong>
                        &nbsp;|&nbsp; <em>{{ $e->creator_name ?? ('#'.($e->created_by ?? '—')) }}</em>
                    </div>

                    @if(!empty($e->note))
                        <div class="note">{!! nl2br(e(strip_tags($e->note))) !!}</div>
                    @else
                        <div style="font-size:11px;color:var(--muted-color)">(no note)</div>
                    @endif

                    @if($include_attachments && !empty($e->attachments))
                        <div class="attachments">
                            <strong>Attachments</strong>
                            <ul>
                                @foreach($e->attachments as $att)
                                    <li>
                                        {{ $att['original_name'] ?? ($att['stored_name'] ?? 'file') }}
                                        @if(!empty($att['absolute_url'])) — <a href="{{ $att['absolute_url'] }}">{{ $att['absolute_url'] }}</a>@endif

                                        {{-- inline image preview if available --}}
                                        @if(!empty($att['absolute_url']) && (isset($att['kind']) && $att['kind'] === 'image'))
                                            <img class="inline-image" src="{{ $att['absolute_url'] }}" alt="{{ $att['original_name'] ?? '' }}" />
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    @endforeach

    <div class="page-break"></div>
</div>

<footer>
    Exported from {{ config('app.name') }} on {{ now()->toDateTimeString() }}
</footer>
</body>
</html>
