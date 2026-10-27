@extends('layouts.app')
@section('title', 'Konsolidasi - FinFlow')
@section('content')
@php $f = fn($n) => number_format($n, 0, ',', '.'); @endphp
<div class="card">
    <h2 style="margin-top:0">Konsolidasi Multi-Cabang</h2>
    <form method="GET" class="row">
        <div><label>Dari</label><input type="date" name="from" value="{{ $from }}"></div>
        <div><label>Sampai</label><input type="date" name="to" value="{{ $to }}"></div>
        <div><button type="submit">Tampilkan</button></div>
    </form>

    <h3>Per Cabang</h3>
    <table>
        <thead><tr><th>Cabang</th><th class="num">Omzet</th><th class="num">HPP</th><th class="num">Laba Kotor</th><th class="num">Biaya Operasional</th><th class="num">Laba Operasional</th></tr></thead>
        <tbody>
        @forelse($perBranch as $r)
            <tr>
                <td>{{ $r->code }} - {{ $r->name }}</td>
                <td class="num">{{ $f($r->revenue) }}</td><td class="num">{{ $f($r->cogs) }}</td>
                <td class="num">{{ $f($r->gross_profit) }}</td><td class="num">{{ $f($r->opex) }}</td>
                <td class="num">{{ $f($r->gross_profit - $r->opex) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="color:#627d98">Belum ada data pada periode ini.</td></tr>
        @endforelse
            <tr style="font-weight:700;background:#f0f4f8">
                <td>GABUNGAN</td>
                <td class="num">{{ $f($total->revenue) }}</td><td class="num">{{ $f($total->cogs) }}</td>
                <td class="num">{{ $f($total->gross_profit) }}</td><td class="num">{{ $f($total->opex) }}</td>
                <td class="num">{{ $f($total->gross_profit - $total->opex) }}</td>
            </tr>
        </tbody>
    </table>

    <h3 style="margin-top:24px">Per Hari (semua cabang)</h3>
    <table>
        <thead><tr><th>Tanggal</th><th class="num">Omzet</th><th class="num">HPP</th><th class="num">Laba Kotor</th><th class="num">Biaya Operasional</th></tr></thead>
        <tbody>
        @foreach($perDay as $d)
            <tr>
                <td>{{ \Illuminate\Support\Carbon::parse($d->summary_date)->format('d/m/Y') }}</td>
                <td class="num">{{ $f($d->revenue) }}</td><td class="num">{{ $f($d->cogs) }}</td>
                <td class="num">{{ $f($d->gross_profit) }}</td><td class="num">{{ $f($d->opex) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
