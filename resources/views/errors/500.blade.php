@extends('errors.layout')

@section('title', 'Kesalahan Sistem')
@section('code', '500')
@section('message', 'Oops! Pekerja kami menemukan sedikit masalah pada server. Tim teknis telah diberitahu dan sedang memperbaikinya. Silakan coba beberapa saat lagi.')

@section('icon')
    <i class="fa-solid fa-triangle-exclamation"></i>
@endsection

@section('actions')
    <a href="{{ url('/') }}" class="btn-custom-primary">
        <i class="fa-solid fa-house"></i>Kembali ke Beranda
    </a>
    <a href="javascript:history.back()" class="btn-custom-outline">
        <i class="fa-solid fa-arrow-left"></i>Kembali ke Sebelumnya
    </a>
@endsection
