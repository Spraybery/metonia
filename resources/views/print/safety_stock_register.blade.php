<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Worker Safety &amp; PPE Register — Metonia Assembly Plant #1</title>
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

    @include('print.document_header', ['docRefCode' => 'PPE-REG-'.now()->format('Ymd'), 'docStatusLabel' => 'OFFICIAL REGISTER'])

    <div class="doc-title-block">
        <h2 class="doc-title">Worker Safety &amp; Personal Protective Equipment (PPE) Register</h2>
        <div class="doc-subtitle">{{ $materials->count() }} Safety Gear Item(s) Listed</div>
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
            @forelse($materials as $material)
            @php
                $needed = max(0, (float)$material->low_stock - (float)$material->qty);
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $material->item_code }}</td>
                <td><strong>{{ $material->name }}</strong></td>
                <td>
                    <span style="font-weight: bold; color: #dc2626;">
                        Worker Safety &amp; PPE
                    </span>
                </td>
                <td class="text-center" style="font-weight: bold; color: {{ $needed > 0 ? '#b91c1c' : '#047857' }};">
                    {{ (float)$needed == (int)$needed ? number_format($needed) : number_format($needed, 2) }}
                </td>
                <td class="text-center">{{ $material->unit }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 16px; color: #64748b;">No safety stock items match this register.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @include('print.document_signatures')

    @include('print.document_footer')
</div>

</body>
</html>
