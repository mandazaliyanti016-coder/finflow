@extends('layouts.app')
@section('title', 'Input Penjualan - FinFlow')
@section('content')
<div class="card" style="max-width:520px">
    <h2 style="margin-top:0">Input Penjualan Tunai</h2>
    <p style="color:#627d98">Sistem otomatis membuat jurnal: Debit Kas, Kredit Pendapatan Penjualan.</p>
    <form method="POST" action="{{ route('sales.store') }}">
        @csrf
        <label>Cabang</label>
        <select name="branch_id" required>
            @foreach($branches as $b)
                <option value="{{ $b->id }}">{{ $b->code }} - {{ $b->name }}</option>
            @endforeach
        </select>
        <label>Tanggal</label>
        <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}" required>
        <label>Jumlah (Rp, tanpa titik)</label>
        <input type="number" name="amount" min="1" step="1" value="{{ old('amount') }}" placeholder="100000" required>
        <label>Keterangan (opsional)</label>
        <input type="text" name="description" value="{{ old('description') }}" maxlength="255">
        <div style="margin-top:16px"><button type="submit">Simpan dan Posting Jurnal</button></div>
    </form>
</div>
@endsection
