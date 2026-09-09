<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 14px;">
    <div style="display: flex; align-items: center;">
        <img src="{{ Qs::getSystemLogo() }}" alt="Metonia Logo" style="height: 48px; margin-right: 15px;">
        <div>
            <h3 style="margin: 0; font-size: 16px; font-weight: 900; color: #0f172a; letter-spacing: 1px;">METONIA ENTERPRISE LIMITED</h3>
            <div style="font-size: 10px; color: #64748b; margin-top: 3px;">P.O BOX 1522,00606,Nairobi,Kenya</div>
        </div>
    </div>
    <div style="text-align: right; font-size: 10px; color: #64748b;">
        <div>Document Ref: <strong>MET-{{ $docRefCode ?? 'JC-'.($vehicle->id ?? '001') }}</strong></div>
        <div>Generated: {{ now()->format('d M Y, H:i') }} EAT</div>
        <div>Status: <strong style="color: #10B981;">{{ $docStatusLabel ?? 'OFFICIAL REGISTER' }}</strong></div>
    </div>
</div>
