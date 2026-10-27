<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:480px;margin:32px auto;padding:0 16px;color:#1f2937;text-align:center}
        #qr{display:inline-block;padding:12px;border:1px solid #e5e7eb;border-radius:8px}
        .badge{padding:4px 12px;border-radius:12px;font-weight:600}
        .pending{background:#fef3c7}.paid{background:#d1fae5}.expired{background:#fee2e2}
        button{padding:10px 16px;background:#16a34a;color:#fff;border:0;border-radius:6px;cursor:pointer}
        .note{background:#eff6ff;padding:8px;border-radius:6px;margin:12px 0}
    </style>
</head>
<body>
    <h1>{{ $invoice->invoice_number }}</h1>
    <h2>Rp {{ number_format($invoice->amount, 0, ',', '.') }}</h2>
    <p>Status: <span id="status" class="badge {{ $invoice->status }}">{{ $invoice->status }}</span></p>

    @if(session('result'))
        <div class="note">Hasil webhook: <strong>{{ session('result') }}</strong></div>
    @endif

    @if($invoice->status === 'pending')
        <div id="qr"></div>
        <p>Berlaku sampai {{ $invoice->expires_at->format('H:i') }}</p>
        <form method="POST" action="{{ route('invoices.simulate', $invoice) }}">
            @csrf
            <button type="submit">Simulasikan pembayaran</button>
        </form>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
            new QRCode(document.getElementById('qr'), {text: @json($invoice->qris_payload), width: 220, height: 220});
            setInterval(function () {
                fetch(@json(route('invoices.status', $invoice)))
                    .then(function (r) { return r.json(); })
                    .then(function (d) { if (d.status !== 'pending') location.reload(); });
            }, 3000);
        </script>
    @elseif($invoice->status === 'paid')
        <p>Dibayar pada {{ $invoice->paid_at->format('d/m/Y H:i') }}</p>
    @else
        <p>Faktur kedaluwarsa. Buat faktur baru.</p>
    @endif

    <p><a href="{{ route('invoices.index') }}">&larr; Daftar faktur</a></p>
</body>
</html>
