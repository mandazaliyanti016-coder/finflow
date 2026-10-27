@extends('layouts.app')
@section('title', 'Daftar Akun - FinFlow')
@section('content')
<div class="card">
    <h2 style="margin-top:0">Daftar Akun (COA SAK EMKM)</h2>
    <table>
        <thead><tr><th>Kode</th><th>Nama Akun</th><th>Tipe</th><th>Saldo Normal</th><th class="num">Saldo (Rp)</th><th></th></tr></thead>
        <tbody>
        @foreach($accounts as $a)
            <tr>
                <td>{{ $a->code }}</td><td>{{ $a->name }}</td><td>{{ ucfirst($a->type) }}</td>
                <td>{{ ucfirst($a->normal_balance) }}</td>
                <td class="num">{{ number_format($a->balance, 0, ',', '.') }}</td>
                <td><a href="{{ route('ledger.show', $a->id) }}">Buku besar</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
