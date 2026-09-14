<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }} — Metonia Assembly Plant #1</title>
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

    @include('print.document_header', ['docRefCode' => 'RST-REQ-'.now()->format('Ymd'), 'docStatusLabel' => 'OFFICIAL REQUISITION'])

    <div class="doc-title-block">
        <h2 class="doc-title">{{ $reportTitle }}</h2>
        <div class="doc-subtitle">{{ $items->count() }} Item(s) Below Safety Reorder Threshold</div>
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
                <th class="text-right">Est. Unit Price</th>
                <th class="text-right">Est. Cost Needed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            @php
                $isSafety = $item->isSafetyStock();
                $needed = max(0, (float)$item->low_stock - (float)$item->qty);
                $estTotalCost = $needed * (float)$item->unit_cost;
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->item_code }}</td>
                <td><strong>{{ $item->name }}</strong></td>
                <td>
                    <span style="font-weight: bold; color: {{ $isSafety ? '#dc2626' : '#059669' }};">
                        {{ $isSafety ? 'Worker Safety & PPE' : 'Store Material / Part' }}
                    </span>
                </td>
                <td class="text-center" style="background-color: #fef2f2; color: #b91c1c; font-weight: bold;">
                    +{{ (float)$needed == (int)$needed ? number_format($needed) : number_format($needed, 2) }}
                </td>
                <td class="text-center">{{ $item->unit }}</td>
                <td class="text-right">KES {{ number_format($item->unit_cost, 2) }}</td>
                <td class="text-right" style="font-weight: bold; color: #047857;">KES {{ number_format($estTotalCost, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 16px; color: #64748b;">All items are sufficiently stocked above reorder safety limits. No restock needed.</td>
            </tr>
            @endforelse
        </tbody>
        @if($items->count() > 0)
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="6" class="text-right" style="text-transform: uppercase; font-size: 11px;">Grand Total Estimated Requisition Budget:</td>
                <td colspan="2" class="text-right" style="font-size: 13px; color: #047857;">
                    KES {{ number_format($totalEstimatedBudget, 2) }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>

    @include('print.document_signatures')

    @include('print.document_footer')
</div>

</body>
</html>
