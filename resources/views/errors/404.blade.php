@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')
@section('code', '404')
@section('message', 'Hmm... Halaman yang Anda cari sepertinya tidak ada. Mungkin alamatnya (URL) salah ketik, atau halamannya sudah dipindahkan atau dihapus.')

@section('icon')
    <i class="fa-solid fa-magnifying-glass-location"></i>
@endsection

@section('actions')
    <a href="{{ url('/') }}" class="btn-custom-primary">
        <i class="fa-solid fa-house"></i>Kembali ke Beranda
    </a>
    <a href="javascript:history.back()" class="btn-custom-outline">
        <i class="fa-solid fa-arrow-left"></i>Kembali ke Sebelumnya
    </a>
@endsection
