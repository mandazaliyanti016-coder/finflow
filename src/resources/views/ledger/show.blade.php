@extends('layouts.app')
@section('title', 'Buku Besar - FinFlow')
@section('content')
<div class="card">
    <h2 style="margin-top:0">Buku Besar: {{ $account->code }} {{ $account->name }}</h2>
    <form method="GET" action="" class="row" id="f">
        <div>
            <label>Akun</label>
            <select onchange="location='{{ url('/ledger') }}/'+this.value">
                @foreach($accounts as $a)
                    <option value="{{ $a->id }}" @selected($a->id === $account->id)>{{ $a->code }} - {{ $a->name }}</option>
                @endforeach
            </select>
        </div>
        <div><label>Dari</label><input type="date" name="from" value="{{ $from }}"></div>
        <div><label>Sampai</label><input type="date" name="to" value="{{ $to }}"></div>
        <div><button type="submit">Filter</button></div>
    </form>
    <table style="margin-top:16px">
        <thead><tr><th>Tanggal</th><th>Jurnal</th><th>Keterangan</th><th class="num">Debit</th><th class="num">Kredit</th><th class="num">Saldo</th></tr></thead>
        <tbody>
            @if($from)
            <tr><td colspan="5"><em>Saldo awal</em></td><td class="num">{{ number_format($opening, 0, ',', '.') }}</td></tr>
            @endif
            @forelse($rows as $r)
            <tr>
                <td>{{ \Illuminate\Support\Carbon::parse($r->entry_date)->format('d/m/Y') }}</td>
                <td>#{{ $r->entry_id }}</td>
                <td>{{ $r->description }}</td>
                <td class="num">{{ $r->debit ? number_format($r->debit, 0, ',', '.') : '' }}</td>
                <td class="num">{{ $r->credit ? number_format($r->credit, 0, ',', '.') : '' }}</td>
                <td class="num">{{ number_format($r->balance, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="color:#627d98">Belum ada transaksi pada akun ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
