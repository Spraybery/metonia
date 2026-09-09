<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Worker Safety Gear (PPE) Issuance Register — Metonia Assembly Plant #1</title>
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

    @include('print.document_header', ['docRefCode' => 'PPE-ISS-'.now()->format('Ymd'), 'docStatusLabel' => 'OFFICIAL REGISTER'])

    <div class="doc-title-block">
        <h2 class="doc-title">Worker Safety Gear (PPE) Outward Issuance Register</h2>
        <div class="doc-subtitle">{{ $safetyIssuances->count() }} PPE Issuance Record(s) Listed</div>
    </div>

    <table class="doc-grid">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Date</th>
                <th>Safety Equipment / PPE</th>
                <th class="text-center">Quantity Issued</th>
                <th>Issued To (Worker)</th>
                <th>Issued By</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse($safetyIssuances as $movement)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $movement->date ? $movement->date->format('d M Y') : $movement->created_at->format('d M Y') }}</td>
                <td><strong>{{ $movement->material_name }}</strong></td>
                <td class="text-center" style="color: #dc2626; font-weight: bold;">
                    {{ (float)$movement->qty == (int)$movement->qty ? number_format($movement->qty) : number_format($movement->qty, 2) }} {{ $movement->unit }}
                </td>
                <td><strong>{{ $movement->issued_to ?: ($movement->person ?: 'Worker') }}</strong></td>
                <td>{{ $movement->issued_by ?: '—' }}</td>
                <td class="font-size-sm">{{ $movement->note ?: '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 16px; color: #64748b;">No worker safety gear issuances logged in this register.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @include('print.document_signatures')

    @include('print.document_footer')
</div>

</body>
</html>
