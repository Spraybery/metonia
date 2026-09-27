<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Restock &amp; Delivery Register — Metonia Enterprise Limited</title>
    @include('print.master_document_styles')
</head>
<body>

<div class="no-print" style="max-width: 900px; margin: 10px auto; text-align: right;">
    <button onclick="window.print()" style="background: #10B981; color: white; border: none; padding: 8px 16px; font-weight: bold; border-radius: 4px; cursor: pointer;">
        🖨️ Print / Save as PDF
    </button>
</div>

<div class="document-container">
    <div class="doc-watermark" style="background-image: url('{{ Qs::getSystemLogo() }}');"></div>

    @include('print.document_header', ['docRefCode' => 'RST-REG-'.now()->format('Ymd'), 'docStatusLabel' => 'OFFICIAL REGISTER'])

    <div class="doc-title-block">
        <h2 class="doc-title">Supplier Restock &amp; Delivery Register</h2>
        <div class="doc-subtitle">{{ $restockMovements->count() }} Delivery Record(s) Listed</div>
    </div>

    <table class="doc-grid">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Item Code</th>
                <th>Description</th>
                <th>Register Type</th>
                <th class="text-center">Quantity Needed</th>
                <th class="text-center">Unit</th>
                <th>Delivery Date</th>
                <th class="text-right">Amount Spent</th>
            </tr>
        </thead>
        <tbody>
            @forelse($restockMovements as $movement)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $movement->material->item_code ?? '—' }}</td>
                <td><strong>{{ $movement->material_name }}</strong></td>
                <td>
                    <span style="font-weight: bold; color: #059669;">
                        Supplier Restock
                    </span>
                </td>
                <td class="text-center font-weight-bold">
                    {{ (float)$movement->qty == (int)$movement->qty ? number_format($movement->qty) : number_format($movement->qty, 2) }}
                </td>
                <td class="text-center">{{ $movement->unit }}</td>
                <td>{{ $movement->date->format('d M Y') }}</td>
                <td class="text-right">{{ $movement->hasRecordedSpend() ? 'KES '.number_format($movement->amount_spent, 2) : 'Not recorded' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 16px; color: #64748b;">No supplier restock records match this register.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($monthlyRestockSpend->isNotEmpty())
    <table class="doc-grid" style="margin-top: 16px;">
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-center">Deliveries</th>
                <th class="text-center">Awaiting Amount</th>
                <th class="text-right">Total Money Spent</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlyRestockSpend as $month)
            <tr>
                <td><strong>{{ $month['month_name'] }}</strong></td>
                <td class="text-center">{{ $month['restock_count'] }}</td>
                <td class="text-center">{{ $month['awaiting_cost_count'] }}</td>
                <td class="text-right font-weight-bold">KES {{ number_format($month['amount_spent'], 2) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3" class="text-right"><strong>Grand Total</strong></td>
                <td class="text-right font-weight-bold">KES {{ number_format($monthlyRestockSpend->sum('amount_spent'), 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    @include('print.document_signatures')

    @include('print.document_footer')
</div>

</body>
</html>
