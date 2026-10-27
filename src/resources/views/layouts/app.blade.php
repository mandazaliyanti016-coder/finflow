<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'FinFlow')</title>
    <style>
        *{box-sizing:border-box} body{font-family:system-ui,Arial,sans-serif;margin:0;background:#f4f6f8;color:#1f2933}
        header{background:#0f4c81;color:#fff;padding:12px 24px;display:flex;align-items:center;gap:24px}
        header a{color:#fff;text-decoration:none;font-size:14px} header .brand{font-weight:700;font-size:18px}
        header form{margin-left:auto} main{max-width:960px;margin:24px auto;padding:0 16px}
        .card{background:#fff;border-radius:8px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.1)}
        table{width:100%;border-collapse:collapse;font-size:14px} th,td{padding:8px 10px;border-bottom:1px solid #e4e7eb;text-align:left}
        th{background:#f0f4f8} td.num,th.num{text-align:right;font-variant-numeric:tabular-nums}
        label{display:block;margin:12px 0 4px;font-size:14px;font-weight:600}
        input,select{width:100%;padding:9px;border:1px solid #bcccdc;border-radius:6px;font-size:14px}
        button,.btn{background:#0f4c81;color:#fff;border:0;border-radius:6px;padding:9px 16px;cursor:pointer;font-size:14px;text-decoration:none;display:inline-block}
        .btn-light{background:transparent;color:#fff;border:1px solid #fff;padding:5px 10px}
        .ok{background:#e3f9e5;color:#1f6f43;padding:10px;border-radius:6px;margin-bottom:12px}
        .err{background:#ffe3e3;color:#a61b1b;padding:10px;border-radius:6px;margin-bottom:12px}
        .row{display:flex;gap:12px;align-items:end;flex-wrap:wrap} .row>div{flex:1;min-width:140px}
    </style>
</head>
<body>
@auth
<header>
    <span class="brand">FinFlow</span>
    <a href="{{ route('accounts.index') }}">Daftar Akun (COA)</a>
    <a href="{{ route('sales.create') }}">Input Penjualan Tunai</a>
    <a href="{{ route('ledger.show', 1) }}">Buku Besar</a>
    <form method="POST" action="{{ route('logout') }}">@csrf
        <button class="btn-light" type="submit">Keluar ({{ auth()->user()->name }})</button>
    </form>
</header>
@endauth
<main>
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    @yield('content')
</main>
</body>
</html>
