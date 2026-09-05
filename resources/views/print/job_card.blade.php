<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Card #{{ $vehicle->plate }} — Metonia Assembly Plant #1</title>
    @include('print.master_document_styles')
</head>
<body>

<div class="no-print" style="max-width: 900px; margin: 10px auto; text-align: right;">
    <button onclick="window.print()" style="background: #10B981; color: white; border: none; padding: 8px 16px; font-weight: bold; border-radius: 4px; cursor: pointer;">
        🖨️ Print / Save as PDF
    </button>
</div>

<div class="document-container">
    {{-- Watermark --}}
    <div class="doc-watermark" style="background-image: url('{{ Qs::getSystemLogo() }}');"></div>

    {{-- Plant Header --}}
    @include('print.document_header')

    {{-- Title Block --}}
    <div class="doc-title-block">
        <h2 class="doc-title">Official Vehicle Job Card &amp; Build Dossier</h2>
        <div class="doc-subtitle">Job Card Reference: {{ $vehicle->plate }} (Stage: {{ $vehicle->stage }})</div>
    </div>

    {{-- Profile & Metadata Table --}}
    <table class="meta-profile-table">
        <tr>
            <td class="meta-label">Vehicle Plate / VIN:</td>
            <td class="meta-value" style="color: #10B981;">{{ $vehicle->plate }}</td>
            <td class="meta-label">Plant Intake Date:</td>
            <td class="meta-value">{{ $vehicle->intake_date ? $vehicle->intake_date->format('d M Y, H:i') : '—' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Make &amp; Model:</td>
            <td class="meta-value">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year ?: date('Y') }})</td>
            <td class="meta-label">Lead Supervisor:</td>
            <td class="meta-value">{{ $vehicle->assigned_to ?: 'Unassigned' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Client Account:</td>
            <td class="meta-value">{{ $vehicle->customer_name ?: 'Internal Plant Unit' }}</td>
            <td class="meta-label">Client Contact:</td>
            <td class="meta-value">{{ $vehicle->customer_phone ?: '—' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Current Stage:</td>
            <td class="meta-value"><strong>{{ $vehicle->stage }}</strong></td>
            <td class="meta-label">Checklist Status:</td>
            <td class="meta-value">{{ $vehicle->checklist_done }} of {{ $vehicle->checklist_total }} Steps Completed ({{ $vehicle->checklistPercentage() }}%)</td>
        </tr>
    </table>

    {{-- Issued Parts & Materials Grid --}}
    <div style="margin-top: 20px; margin-bottom: 8px; font-weight: 800; text-transform: uppercase; font-size: 11px; color: #0f172a;">
        Issued Materials &amp; Parts Schedule
    </div>
    <table class="doc-grid">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>Material / Component Description</th>
                <th class="text-center" style="width: 100px;">Quantity</th>
                <th class="text-right" style="width: 140px;">Unit Rate (KES)</th>
                <th class="text-right" style="width: 140px;">Total Cost (KES)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vehicle->parts as $part)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><strong>{{ $part->material_name }}</strong></td>
                <td class="text-center">{{ number_format($part->qty, 2) }}</td>
                <td class="text-right">{{ number_format($part->unit_cost, 2) }}</td>
                <td class="text-right font-weight-bold">{{ number_format($part->cost, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 16px; color: #64748b;">
                    No billable store materials or parts logged to this job card.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right font-weight-bold text-uppercase">Cumulative Materials Cost:</td>
                <td class="text-right font-weight-bold" style="color: #0f172a;">{{ number_format($vehicle->totalPartsCost(), 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right font-weight-bold text-uppercase">Direct Labor Allocation:</td>
                <td class="text-right font-weight-bold" style="color: #0f172a;">{{ number_format($vehicle->labor_cost, 2) }}</td>
            </tr>
            <tr style="background-color: #f1f5f9;">
                <td colspan="4" class="text-right font-weight-bold text-uppercase">Total Cost of Production (KES):</td>
                <td class="text-right font-weight-bold" style="font-size: 13px;">{{ number_format($vehicle->totalCost(), 2) }}</td>
            </tr>
            <tr style="background-color: #ecfdf5; border-top: 2px solid #10B981;">
                <td colspan="4" class="text-right font-weight-bold text-uppercase" style="color: #065f46;">Customer Invoiced Value (KES):</td>
                <td class="text-right font-weight-bold" style="color: #065f46; font-size: 14px;">{{ number_format($vehicle->invoice_total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Technical Intake Remarks --}}
    @if($vehicle->notes)
    <div style="margin-top: 15px; padding: 10px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px;">
        <div style="font-weight: 700; font-size: 10px; text-transform: uppercase; color: #64748b; margin-bottom: 4px;">Technical Intake Notes &amp; Observations:</div>
        <div style="font-size: 11px; color: #1e293b;">{{ $vehicle->notes }}</div>
    </div>
    @endif

    {{-- Signatures Box --}}
    <div class="signatures-box">
        <div class="sig-line">
            <div>{{ $vehicle->assigned_to ?: 'Lead Workshop Engineer' }}</div>
            <span style="font-size: 10px; color: #64748b;">Stage Supervisor Sign &amp; Date</span>
        </div>
        <div class="sig-line">
            <div>Plant Operations Director</div>
            <span style="font-size: 10px; color: #64748b;">Quality Assurance &amp; Final Release</span>
        </div>
    </div>

    {{-- Footer --}}
    <div class="system-footer">
        Generated by <strong>Metonia Workshop Management System</strong> on {{ now()->format('d M Y, H:i') }} |
        Nairobi Assembly Plant #1 Quality Verification Protocol
    </div>
</div>

</body>
</html>
