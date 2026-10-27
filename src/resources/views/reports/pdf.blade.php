<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#111}
        h1{font-size:16px;margin:0}h2{font-size:13px;margin:18px 0 6px}
        table{width:100%;border-collapse:collapse}
        th,td{padding:4px 6px;border-bottom:1px solid #ccc;text-align:left}
        .n{text-align:right}.tot td{font-weight:bold;background:#f0f0f0}
    </style>
</head>
<body>
@php $rp = fn ($v) => number_format($v, 0, ',', '.'); @endphp
<h1>FinFlow - Laporan Keuangan</h1>
<p>Periode {{ $from }} s.d. {{ $to }}</p>

<h2>Neraca Saldo</h2>
<table>
    <tr><th>Kode</th><th>Akun</th><th class="n">Saldo Debit</th><th class="n">Saldo Kredit</th></tr>
    @foreach($tb['rows'] as $x)
        <tr><td>{{ $x['code'] }}</td><td>{{ $x['name'] }}</td><td class="n">{{ $rp($x['saldo_debit']) }}</td><td class="n">{{ $rp($x['saldo_kredit']) }}</td></tr>
    @endforeach
    <tr class="tot"><td></td><td>TOTAL</td><td class="n">{{ $rp($tb['total_debit']) }}</td><td class="n">{{ $rp($tb['total_kredit']) }}</td></tr>
</table>

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
    <tr><td>Dasar pengenaan pajak</td><td class="n">Rp {{ $rp($tax['dpp']) }}</td></tr>
    <tr><td>Tarif</td><td class="n">{{ $tax['tarif'] * 100 }}%</td></tr>
    <tr class="tot"><td>PPh Final terutang (estimasi)</td><td class="n">Rp {{ $rp($tax['pph_final']) }}</td></tr>
</table>
<p style="margin-top:16px;color:#666">Estimasi, bukan pengganti perhitungan resmi. Dibuat {{ now()->format('d/m/Y H:i') }}.</p>
</body>
</html>
