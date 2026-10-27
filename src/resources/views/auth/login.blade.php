@extends('layouts.app')
@section('title', 'Masuk - FinFlow')
@section('content')
<div class="card" style="max-width:380px;margin:60px auto">
    <h2 style="margin-top:0">FinFlow</h2>
    <p style="color:#627d98;margin-top:-8px">Platform Akuntansi UMKM Multi-Outlet</p>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', 'admin@finflow.test') }}" required>
        <label>Kata sandi</label>
        <input type="password" name="password" required>
        <div style="margin-top:16px"><button type="submit">Masuk</button></div>
    </form>
</div>
@endsection
