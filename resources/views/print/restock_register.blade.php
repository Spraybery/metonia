<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Restock &amp; Delivery Register — Metonia Assembly Plant #1</title>
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
                <th class="text-center">Quantity Received</th>
                <th class="text-center">Unit</th>
                <th class="text-center">Amount Still Needed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($restockMovements as $movement)
            @php
                $stillNeeded = $movement->material ? max(0, (float) $movement->material->low_stock - (float) $movement->material->qty) : 0;
            @endphp
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
                <td class="text-center font-weight-bold" style="color: {{ $stillNeeded > 0 ? '#b45309' : '#059669' }};">
                    {{ $stillNeeded > 0 ? ((float)$stillNeeded == (int)$stillNeeded ? number_format($stillNeeded) : number_format($stillNeeded, 2)) : 'Stocked Up' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 16px; color: #64748b;">No supplier restock records match this register.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @include('print.document_signatures')

    @include('print.document_footer')
</div>

</body>
</html>
