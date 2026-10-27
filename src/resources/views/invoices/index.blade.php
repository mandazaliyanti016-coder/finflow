<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Faktur QRIS</title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:860px;margin:32px auto;padding:0 16px;color:#1f2937}
        table{width:100%;border-collapse:collapse;margin-top:16px}
        th,td{padding:8px 10px;border-bottom:1px solid #e5e7eb;text-align:left}
        .btn{display:inline-block;padding:8px 14px;background:#2563eb;color:#fff;border-radius:6px;text-decoration:none}
        .badge{padding:2px 8px;border-radius:10px;font-size:12px}
        .pending{background:#fef3c7}.paid{background:#d1fae5}.expired{background:#fee2e2}
    </style>
</head>
<body>
    <h1>Faktur QRIS</h1>
    <a class="btn" href="{{ route('invoices.create') }}">+ Buat faktur</a>
    <table>
        <tr><th>No. Faktur</th><th>Jumlah</th><th>Status</th><th>Dibuat</th><th></th></tr>
        @forelse($invoices as $i)
            <tr>
                <td>{{ $i->invoice_number }}</td>
                <td>Rp {{ number_format($i->amount, 0, ',', '.') }}</td>
                <td><span class="badge {{ $i->status }}">{{ $i->status }}</span></td>
                <td>{{ $i->created_at->format('d/m/Y H:i') }}</td>
                <td><a href="{{ route('invoices.show', $i) }}">Lihat</a></td>
            </tr>
        @empty
            <tr><td colspan="5">Belum ada faktur.</td></tr>
        @endforelse
    </table>
</body>
</html>
