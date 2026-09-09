<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Outward Material Issuance Register — Metonia Assembly Plant #1</title>
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

    @include('print.document_header', ['docRefCode' => 'ISS-REG-'.now()->format('Ymd'), 'docStatusLabel' => 'OFFICIAL REGISTER'])

    <div class="doc-title-block">
        <h2 class="doc-title">Outward Store Material Issuance Register</h2>
        <div class="doc-subtitle">{{ $outwardMovements->count() }} Dispatch Record(s) Listed</div>
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
            </tr>
        </thead>
        <tbody>
            @forelse($outwardMovements as $movement)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $movement->material->item_code ?? '—' }}</td>
                <td><strong>{{ $movement->material_name }}</strong></td>
                <td>
                    <span style="font-weight: bold; color: #0284c7;">
                        Store Issuance
                    </span>
                </td>
                <td class="text-center font-weight-bold">
                    {{ (float)$movement->qty == (int)$movement->qty ? number_format($movement->qty) : number_format($movement->qty, 2) }}
                </td>
                <td class="text-center">{{ $movement->unit }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 16px; color: #64748b;">No material issuance records match this register.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @include('print.document_signatures')

    @include('print.document_footer')
</div>

</body>
</html>
