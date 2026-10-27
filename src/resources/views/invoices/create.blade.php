<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Buat Faktur QRIS</title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:480px;margin:32px auto;padding:0 16px;color:#1f2937}
        label{display:block;margin-top:14px;font-weight:600}
        input,select{width:100%;padding:8px;margin-top:4px;box-sizing:border-box}
        button{margin-top:18px;padding:10px 16px;background:#2563eb;color:#fff;border:0;border-radius:6px;cursor:pointer}
        .err{color:#b91c1c;margin-top:8px}
    </style>
</head>
<body>
    <h1>Buat Faktur QRIS</h1>
    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf
        <label>Cabang
            <select name="branch_id">
                @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name ?? ('Cabang #'.$b->id) }}</option>
                @endforeach
            </select>
        </label>
        <label>Jumlah (Rp)
            <input type="number" name="amount" min="1000" value="{{ old('amount', 150000) }}" required>
        </label>
        @foreach($errors->all() as $e)<div class="err">{{ $e }}</div>@endforeach
        <button type="submit">Buat faktur &amp; tampilkan QR</button>
    </form>
    <p><a href="{{ route('invoices.index') }}">&larr; Daftar faktur</a></p>
</body>
</html>
