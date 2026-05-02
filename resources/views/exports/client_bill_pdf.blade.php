<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Bill #{{ $bill->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 12px;
            margin: 28px;
        }
        .topbar {
            display: table;
            width: 100%;
            margin-bottom: 24px;
        }
        .topbar > div {
            display: table-cell;
            vertical-align: top;
        }
        .right {
            text-align: right;
        }
        h1 {
            font-size: 24px;
            margin: 0 0 6px;
        }
        h2 {
            font-size: 15px;
            margin: 0 0 10px;
            color: #1d4ed8;
        }
        .meta-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-bottom: 22px;
        }
        .meta-grid td {
            width: 50%;
            padding: 10px 12px;
            border: 1px solid #dbeafe;
            background: #f8fbff;
            border-radius: 10px;
        }
        .label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px 12px;
            border: 1px solid #dbeafe;
        }
        th {
            background: #eff6ff;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: .4px;
            text-align: left;
        }
        .total-row td {
            font-weight: 700;
            background: #f8fbff;
        }
        .section {
            margin-top: 22px;
        }
        .note-box {
            border: 1px solid #dbeafe;
            background: #f8fbff;
            padding: 14px;
            border-radius: 10px;
            white-space: pre-wrap;
        }
        .muted {
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div>
            <h1>Client Bill #{{ $bill->id }}</h1>
            <div class="muted">{{ $bill->client_name ?? 'Client' }}</div>
        </div>
        <div class="right">
            <div><strong>Status:</strong> {{ !empty($bill->is_published) ? 'Published' : 'Draft' }}</div>
            <div><strong>Published:</strong> {{ !empty($bill->published_at) ? \Illuminate\Support\Carbon::parse($bill->published_at)->format('d M Y') : '—' }}</div>
        </div>
    </div>

    <table class="meta-grid">
        <tr>
            <td>
                <span class="label">Bill Date</span>
                <strong>{{ !empty($bill->bill_date) ? \Illuminate\Support\Carbon::parse($bill->bill_date)->format('d M Y') : '—' }}</strong>
            </td>
            <td>
                <span class="label">Due Date</span>
                <strong>{{ !empty($bill->due_date) ? \Illuminate\Support\Carbon::parse($bill->due_date)->format('d M Y') : '—' }}</strong>
            </td>
        </tr>
    </table>

    <h2>Bill Items</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">#</th>
                <th>Bill Head</th>
                <th style="width: 24%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($bill->items ?? []) as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->bill_head_title ?? 'Untitled' }}</td>
                    <td>Rs {{ number_format((float) ($item->amount ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No bill items found.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="2">Total</td>
                <td>Rs {{ number_format((float) ($bill->total_amount ?? 0), 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section">
        <h2>Repayments</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 22%;">Date</th>
                    <th style="width: 20%;">Status</th>
                    <th style="width: 22%;">Amount</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($bill->repayments ?? []) as $repayment)
                    <tr>
                        <td>{{ !empty($repayment->repayment_date) ? \Illuminate\Support\Carbon::parse($repayment->repayment_date)->format('d M Y') : '—' }}</td>
                        <td>{{ ucfirst((string) ($repayment->status ?? 'pending')) }}</td>
                        <td>Rs {{ number_format((float) ($repayment->amount ?? 0), 2) }}</td>
                        <td>{{ $repayment->note ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No repayments recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Notes</h2>
        <div class="note-box">{{ $bill->notes ?: 'No notes added.' }}</div>
    </div>
</body>
</html>
