{{-- resources/views/exports/export_word_html.blade.php --}}
<!doctype html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta charset="utf-8" />
  <title>Job {{ $job->id ?? '' }} — Expenses</title>

  <style>
    /* Word-friendly CSS */
    body { font-family: Arial, Helvetica, sans-serif; font-size:12px; color:#333; margin:12px; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    h1 { color:#0369a1; font-size:18px; margin:0 0 6px; }
    h2 { color:#0369a1; font-size:14px; margin:12px 0 8px; }
    .meta { font-size:11px; color:#64748b; margin-bottom:8px; }

    table { width:100%; border-collapse:collapse; margin-top:8px; mso-table-lspace:0pt; mso-table-rspace:0pt; }
    th, td { border:1px solid #ddd; padding:6px 8px; vertical-align:top; }
    th { background:#f3f4f6; font-weight:700; text-align:left; }
    td.right { text-align:right; }

    /* Summary block */
    .summary { border:1px solid #e6eef6; background:#fff; padding:10px; border-radius:6px; margin-bottom:10px; }
    .summary h3 { margin:0 0 8px; font-size:13px; color:#0369a1; }

    /* Compact note */
    .note { font-size:12px; white-space:pre-wrap; word-break:break-word; color:#333; }

    /* Small print footer */
    .footer { margin-top:14px; font-size:11px; color:#64748b; text-align:center; }

    /* Ensure Word preserves spacing */
    td, th { mso-line-height-rule:exactly; }
  </style>
</head>
<body>
  <h1>Job {{ $job->id ?? '' }} — Expenses</h1>
  <div class="meta">
    <strong>Title:</strong> {{ $job->title ?? '—' }} &nbsp; • &nbsp;
    <strong>Generated:</strong> {{ $generated_at ?? now()->toDateTimeString() }}
  </div>

  {{-- Grand totals summary --}}
  @if(!empty($personTotals) && is_array($personTotals))
    <div class="summary" style="page-break-inside:avoid;">
      <h3>Expense Summary <small style="color:#64748b; font-weight:700; margin-left:8px">({{ count($personTotals) }})</small></h3>

      <table role="presentation" aria-label="Grand totals by person">
        <thead>
          <tr>
            <th style="width:55%;">Person</th>
            <th style="width:20%;">Currency</th>
            <th style="width:25%; text-align:right;">Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach($personTotals as $person => $cmap)
            @foreach($cmap as $cur => $amt)
              <tr>
                <td>{{ $person }}</td>
                <td>{{ $cur }}</td>
                <td class="right"><strong>{{ number_format($amt, 2) }}</strong></td>
              </tr>
            @endforeach
          @endforeach
        </tbody>
      </table>
    </div>
  @endif

  {{-- Expenses grouped sections --}}
  @foreach($groups as $group => $rows)
    <h2>{{ ucfirst($group) }} <span style="font-size:11px;color:#64748b;font-weight:700;margin-left:8px">({{ count($rows) }})</span></h2>
    <h2>Expense Data </h2>
    <table role="presentation">
      <thead>
        <tr>
          <th style="width:6%;">ID</th>
          <th style="width:12%;">Date</th>
          <th style="width:30%;">Head</th>
          <th style="width:12%; text-align:right">Amount</th>
          <th style="width:8%;">Curr</th>
          <th style="width:18%;">Creator</th>
          <th style="width:14%;">Note / Attachments</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rows as $e)
          <tr>
            <td>#{{ $e->id }}</td>
            <td>{{ \Carbon\Carbon::parse($e->expense_date)->toDateString() }}</td>
            <td>{{ $e->expense_head ?? '—' }}</td>
            <td class="right">{{ number_format($e->amount, 2) }}</td>
            <td>{{ $e->currency ?? '—' }}</td>
            <td>
              {{ $e->creator_name ?? ('#'.($e->created_by ?? '—')) }}
              @if(!empty($e->creator_email))<div style="font-size:10px;color:#64748b">{{ $e->creator_email }}</div>@endif
            </td>
            <td>
              @if(!empty($e->note))
                <div class="note">{!! nl2br(e(strip_tags($e->note))) !!}</div>
              @endif

              @if(!empty($include_attachments) && !empty($e->attachments))
                <div style="margin-top:6px;font-size:11px;color:#333">
                  <strong>Attachments:</strong>
                  <ul style="margin:6px 0 0 16px;padding:0;">
                    @foreach($e->attachments as $att)
                      <li style="margin:2px 0;">
                        {{ $att['original_name'] ?? ($att['stored_name'] ?? 'file') }}
                        @if(!empty($att['absolute_url'])) — <a href="{{ $att['absolute_url'] }}">{{ $att['absolute_url'] }}</a>@endif
                      </li>
                    @endforeach
                  </ul>
                </div>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

  @endforeach

  <div class="footer">
    Exported from {{ config('app.name') }} on {{ now()->toDateTimeString() }}
  </div>
</body>
</html>
