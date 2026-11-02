<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bayar {{ $invoice->invoice_number }}</title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:420px;margin:24px auto;padding:0 16px;text-align:center}
        .card{border:1px solid #e5e7eb;border-radius:12px;padding:20px}
        #qr{display:inline-block;padding:12px;border:1px solid #e5e7eb;border-radius:8px;margin:12px 0}
        .badge{padding:4px 12px;border-radius:12px;font-weight:600}
        .pending{background:#fef3c7}
        .paid{background:#d1fae5}
        .expired{background:#fee2e2}
        button{padding:10px 16px;background:#16a34a;color:#fff;border:0;border-radius:6px}
        .muted{color:#6b7280;font-size:13px}
        .note{background:#eff6ff;padding:8px;border-radius:6px;margin:12px 0}
    </style>
</head>
<body>
<div class="card">
    <h2>FinFlow</h2>
    <p class="muted">Tagihan {{ $invoice->invoice_number }}</p>
    <h1>Rp {{ number_format($invoice->amount, 0, ',', '.') }}</h1>
    <p><span id="status" class="badge {{ $invoice->status }}">{{ $invoice->status }}</span></p>

    @if(session('result'))
        <div class="note">Hasil webhook: <strong>{{ session('result') }}</strong></div>
    @endif

    @if($invoice->status === 'pending')
        <p>Pindai kode QRIS di bawah dengan aplikasi pembayaran Anda.</p>
        <div id="qr"></div>
        <p class="muted">Berlaku sampai {{ $invoice->expires_at->format('H:i') }}</p>
        <form method="POST" action="{{ route('invoices.simulate', $invoice) }}">
            @csrf
            <button type="submit">Simulasikan pembayaran (demo)</button>
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
        <p>Pembayaran diterima pada {{ $invoice->paid_at->format('d/m/Y H:i') }}. Terima kasih.</p>
    @else
        <p>Tagihan ini sudah kedaluwarsa. Hubungi penjual untuk tagihan baru.</p>
    @endif
</div>
</body>
</html>

    @if(session('result'))
        <div class="note">Hasil webhook: <strong>{{ session('result') }}</strong></div>
    @endif

    @if($invoice->status === 'pending')
        <p>Pindai kode QRIS di bawah dengan aplikasi pembayaran Anda.</p>
        <div id="qr"></div>
        <p class="muted">Berlaku sampai {{ $invoice->expires_at->format('H:i') }}</p>
        <form method="POST" action="{{ route('invoices.simulate', $invoice) }}">
            @csrf
            <button type="submit">Simulasikan pembayaran (demo)</button>
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
        <p>Pembayaran diterima pada {{ $invoice->paid_at->format('d/m/Y H:i') }}. Terima kasih.</p>
    @else
        <p>Tagihan ini sudah kedaluwarsa. Hubungi penjual untuk tagihan baru.</p>
    @endif
</div>
</body>
</html>
