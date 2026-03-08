@extends('errors.layout')

@section('title', 'Sesi Berakhir')
@section('code', '419')
@section('message', 'Demi keamanan, sesi login Anda telah berakhir karena Anda terlalu lama tidak melakukan aktivitas. Silakan muat ulang halaman atau login kembali.')

@section('icon')
    <i class="fa-solid fa-hourglass-end"></i>
@endsection

@section('actions')
    <a href="{{ route('login') }}" class="btn-custom-primary">
        <i class="fa-solid fa-right-to-bracket"></i>Login Kembali
    </a>
@endsection
