@extends('errors.layout')

@section('title', 'Akses Ditolak')
@section('code', '403')
@section('message', 'Maaf, Anda tidak memiliki izin atau hak akses yang cukup untuk melihat halaman ini. Silakan hubungi Administrator jika Anda merasa ini adalah sebuah kesalahan.')

@section('icon')
    <i class="fa-solid fa-ban"></i>
@endsection

@section('actions')
    <a href="{{ url('/') }}" class="btn-custom-primary">
        <i class="fa-solid fa-house"></i>Kembali ke Beranda
    </a>
    <a href="javascript:history.back()" class="btn-custom-outline">
        <i class="fa-solid fa-arrow-left"></i>Kembali ke Sebelumnya
    </a>
@endsection
