@extends('layouts.app')
@section('title', 'Rekonsiliasi Kas - FinFlow')
@section('content')
@php $f = fn($n) => number_format($n, 0, ',', '.'); @endphp
<div class="card">
    <h2 style="margin-top:0">Rekonsiliasi Kas</h2>
    <form method="GET" class="row">
        <div><label>Cabang</label>
            <select name="branch_id">
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" @selected($b->id === $branchId)>{{ $b->code }} - {{ $b->name }}</option>
                @endforeach
            </select></div>
        <div><label>Tanggal</label><input type="date" name="date" value="{{ $date }}"></div>
        <div><button type="submit">Hitung Saldo Sistem</button></div>
    </form>

    <p style="margin-top:16px">Saldo kas menurut sistem sampai {{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}:
        <strong>Rp {{ $f($systemCash) }}</strong></p>

    <form method="POST" action="{{ route('reconciliation.store') }}">
        @csrf
        <input type="hidden" name="branch_id" value="{{ $branchId }}">
        <input type="hidden" name="recon_date" value="{{ $date }}">
        <label>Kas fisik hasil hitung (Rp)</label>
        <input type="number" name="physical_cash" min="0" step="1" required>
        <label>Catatan (opsional)</label>
        <input type="text" name="note" maxlength="255">
        <div style="margin-top:16px"><button type="submit">Simpan Rekonsiliasi</button></div>
    </form>

    <h3 style="margin-top:24px">Riwayat</h3>
    <table>
        <thead><tr><th>Tanggal</th><th>Cabang</th><th class="num">Sistem</th><th class="num">Fisik</th><th class="num">Selisih</th><th>Catatan</th></tr></thead>
        <tbody>
        @forelse($history as $h)
            <tr>
                <td>{{ \Illuminate\Support\Carbon::parse($h->recon_date)->format('d/m/Y') }}</td>
                <td>{{ $h->branch_code }}</td>
                <td class="num">{{ $f($h->system_cash) }}</td><td class="num">{{ $f($h->physical_cash) }}</td>
                <td class="num" style="color:{{ $h->difference == 0 ? '#1f6f43' : '#a61b1b' }}">{{ $f($h->difference) }}</td>
                <td>{{ $h->note }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="color:#627d98">Belum ada rekonsiliasi.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
