<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 14px;">
    <div style="display: flex; align-items: center;">
        <img src="{{ Qs::getSystemLogo() }}" alt="Metonia Logo" style="height: 48px; margin-right: 15px;">
        <div>
            <h3 style="margin: 0; font-size: 16px; font-weight: 900; color: #0f172a; letter-spacing: 1px;">METONIA ENTERPRISE LIMITED</h3>
            <div style="font-size: 11px; font-weight: 600; color: #10B981;">NAIROBI ASSEMBLY PLANT #1 — HEAVY VEHICLE OPERATIONS</div>
            <div style="font-size: 10px; color: #64748b;">P.O. Box 40822-00100 Nairobi, Kenya | Industrial Area Bay 4 | Tel: +254 20 288 4000</div>
        </div>
    </div>
    <div style="text-align: right; font-size: 10px; color: #64748b;">
        <div>Document Ref: <strong>MET-{{ $docRefCode ?? 'JC-'.($vehicle->id ?? '001') }}</strong></div>
        <div>Generated: {{ now()->format('d M Y, H:i') }} EAT</div>
        <div>Status: <strong style="color: #10B981;">{{ $docStatusLabel ?? 'OFFICIAL DISPATCH' }}</strong></div>
    </div>
</div>
