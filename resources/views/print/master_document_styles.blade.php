<style>
    @media print {
        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .no-print {
            display: none !important;
        }
    }

    body {
        font-family: 'Roboto', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #0f172a;
        background-color: #ffffff;
        margin: 0;
        padding: 20px;
        font-size: 12px;
        line-height: 1.5;
    }

    .document-container {
        position: relative;
        max-width: 900px;
        margin: 0 auto;
        padding: 30px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* Watermark - faint 5% opacity centered behind content */
    .doc-watermark {
        position: absolute;
        top: 35%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 320px;
        height: 320px;
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
        opacity: 0.05;
        pointer-events: none;
        z-index: 0;
    }

    /* Document Title Block */
    .doc-title-block {
        text-align: center;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 2px solid #10B981;
    }

    .doc-title {
        font-size: 18px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #0f172a;
        margin: 0;
    }

    .doc-subtitle {
        font-size: 12px;
        font-weight: 600;
        color: #10B981;
        margin-top: 4px;
    }

    /* Profile / Metadata Table */
    .meta-profile-table {
        width: 100%;
        margin-bottom: 20px;
        border-collapse: collapse;
    }

    .meta-profile-table td {
        padding: 6px 10px;
        border: 1px solid #e2e8f0;
        vertical-align: top;
    }

    .meta-label {
        font-weight: 700;
        color: #475569;
        background-color: #f8fafc;
        width: 25%;
        font-size: 11px;
        text-transform: uppercase;
    }

    .meta-value {
        color: #0f172a;
        font-weight: 600;
        width: 25%;
    }

    /* Main Data Grid */
    .doc-grid {
        width: 100%;
        margin-bottom: 24px;
        border-collapse: collapse;
    }

    .doc-grid th {
        background-color: #f8fafc;
        color: #0f172a;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        padding: 8px 10px;
        border: 1px solid #cbd5e1;
        border-top: 2px solid #10B981;
    }

    .doc-grid td {
        padding: 8px 10px;
        border: 1px solid #e2e8f0;
        color: #0f172a;
    }

    .doc-grid tr:nth-child(even) {
        background-color: #fcfcfc;
    }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .font-weight-bold { font-weight: 700; }

    /* Sign-off Blocks */
    .signatures-box {
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
        page-break-inside: avoid;
    }

    .sig-line {
        width: 42%;
        border-top: 1px solid #0f172a;
        padding-top: 6px;
        text-align: center;
        font-size: 11px;
        font-weight: 600;
    }

    /* System Footer */
    .system-footer {
        margin-top: 30px;
        padding-top: 10px;
        border-top: 1px solid #cbd5e1;
        font-size: 10px;
        color: #64748b;
        text-align: center;
    }
</style>
