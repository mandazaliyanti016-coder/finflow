<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan</title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:900px;margin:32px auto;padding:0 16px;color:#1f2937}
        form{display:flex;gap:12px;flex-wrap:wrap;align-items:end;margin-bottom:16px}
        label{font-size:13px;font-weight:600}
        input,select{display:block;padding:6px;margin-top:4px}
        table{width:100%;border-collapse:collapse;margin:8px 0 24px}
        th,td{padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:left}
        td.n,th.n{text-align:right}
        tr.tot td{font-weight:700;background:#f9fafb}
        .btn{padding:8px 14px;background:#2563eb;color:#fff;border:0;border-radius:6px;text-decoration:none;cursor:pointer}
        .ok{color:#15803d}.bad{color:#b91c1c}.muted{color:#6b7280;font-size:13px}
    </style>
</head>
<body>
@php $rp = fn ($v) => number_format($v, 0, ',', '.'); @endphp
<h1>Laporan Keuangan</h1>

<form method="GET" action="{{ route('reports.index') }}">
    <label>Dari <input type="date" name="from" value="{{ $from }}"></label>
    <label>Sampai <input type="date" name="to" value="{{ $to }}"></label>
    <label>Cabang
        <select name="branch_id">
            <option value="">Semua cabang</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name ?? ('Cabang #'.$b->id) }}</option>
            @endforeach
        </select>
    </label>
    <label>Jenis WP
        <select name="wp">
            <option value="op" @selected($wp==='op')>Orang Pribadi</option>
            <option value="badan" @selected($wp==='badan')>Badan</option>
        </select>
    </label>
    <button class="btn" type="submit">Tampilkan</button>
    <a class="btn" href="{{ route('reports.excel', request()->query()) }}">Unduh Excel</a>
</form>
<p>
    <button class="btn" id="btn-pdf" type="button">Buat PDF</button>
    <span id="pdf-status" class="muted"></span>
</p>
<script>
document.getElementById('btn-pdf').addEventListener('click', function () {
    var st = document.getElementById('pdf-status');
    st.textContent = 'Mengantrekan...';
    var body = new URLSearchParams({_token: @json(csrf_token()), from: @json($from), to: @json($to), branch_id: @json($branchId ?? ''), wp: @json($wp)});
    fetch(@json(route('reports.pdf')), {method: 'POST', body: body, headers: {'Accept': 'application/json'}})
        .then(function (r) { return r.json(); })
        .then(function (d) {
            st.textContent = 'Menunggu worker (antrean)...';
            var t = setInterval(function () {
                fetch('/reports/exports/' + d.id + '/status').then(function (r) { return r.json(); }).then(function (x) {
                    if (x.status === 'done') {
                        clearInterval(t);
                        st.innerHTML = 'Selesai (' + x.duration_ms + ' ms) <a class="btn" href="' + x.url + '">Unduh PDF</a>';
                    } else if (x.status === 'failed') {
                        clearInterval(t);
                        st.textContent = 'Gagal: ' + x.error;
                    } else {
                        st.textContent = 'Status: ' + x.status + '...';
                    }
                });
            }, 1500);
        });
});
</script>
<p class="muted">Laporan dibuat dalam <strong id="ms">{{ $ms }}</strong> ms.</p>

<h2>Neraca Saldo</h2>
<table>
    <tr><th>Kode</th><th>Akun</th><th class="n">Saldo Debit</th><th class="n">Saldo Kredit</th></tr>
    @foreach($tb['rows'] as $x)
        <tr><td>{{ $x['code'] }}</td><td>{{ $x['name'] }}</td>
            <td class="n">{{ $rp($x['saldo_debit']) }}</td><td class="n">{{ $rp($x['saldo_kredit']) }}</td></tr>
    @endforeach
    <tr class="tot"><td></td><td>TOTAL</td><td class="n">{{ $rp($tb['total_debit']) }}</td><td class="n">{{ $rp($tb['total_kredit']) }}</td></tr>
</table>
<p class="{{ $tb['balanced'] ? 'ok' : 'bad' }}">{{ $tb['balanced'] ? 'Seimbang: total debit = total kredit' : 'TIDAK SEIMBANG' }}</p>

<h2>Laporan Laba Rugi</h2>
<table>
    <tr><th colspan="3">Pendapatan</th></tr>
    @foreach($is['pendapatan'] as $i)<tr><td>{{ $i['code'] }}</td><td>{{ $i['name'] }}</td><td class="n">{{ $rp($i['amount']) }}</td></tr>@endforeach
    <tr class="tot"><td></td><td>Total Pendapatan</td><td class="n">{{ $rp($is['total_pendapatan']) }}</td></tr>
    <tr><th colspan="3">Harga Pokok Penjualan</th></tr>
    @foreach($is['hpp'] as $i)<tr><td>{{ $i['code'] }}</td><td>{{ $i['name'] }}</td><td class="n">{{ $rp($i['amount']) }}</td></tr>@endforeach
    <tr class="tot"><td></td><td>Laba Kotor</td><td class="n">{{ $rp($is['laba_kotor']) }}</td></tr>
    <tr><th colspan="3">Beban Operasional</th></tr>
    @foreach($is['beban'] as $i)<tr><td>{{ $i['code'] }}</td><td>{{ $i['name'] }}</td><td class="n">{{ $rp($i['amount']) }}</td></tr>@endforeach
    <tr class="tot"><td></td><td>Laba Bersih</td><td class="n">{{ $rp($is['laba_bersih']) }}</td></tr>
</table>

<h2>Estimasi PPh Final UMKM</h2>
<table>
    <tr><td>Jenis WP</td><td class="n">{{ $tax['wp_type']==='op' ? 'Orang Pribadi' : 'Badan' }}</td></tr>
    <tr><td>Omzet periode</td><td class="n">Rp {{ $rp($tax['omzet_periode']) }}</td></tr>
    <tr><td>Omzet kumulatif tahun berjalan</td><td class="n">Rp {{ $rp($tax['omzet_ytd']) }}</td></tr>
    <tr><td>Ambang tidak kena pajak</td><td class="n">Rp {{ $rp($tax['ambang']) }}</td></tr>
    <tr><td>Dasar pengenaan pajak</td><td class="n">Rp {{ $rp($tax['dpp']) }}</td></tr>
    <tr><td>Tarif</td><td class="n">{{ $tax['tarif'] * 100 }}%</td></tr>
    <tr class="tot"><td>PPh Final terutang (estimasi)</td><td class="n">Rp {{ $rp($tax['pph_final']) }}</td></tr>
</table>
</body>
</html>
