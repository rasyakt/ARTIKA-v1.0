@extends('errors.layout')

@section('title', 'Sistem Sedang Diperbarui')
@section('code', '503')
@section('message', 'Oops! Sistem sedang dalam perbaikan rutin atau peningkatan fitur. Kami akan segera kembali beroperasi. Terima kasih atas kesabaran Anda.')

@section('icon')
    <i class="fa-solid fa-person-digging"></i>
@endsection

@section('custom_info')
    <strong>Maintenance Mode:</strong> Aplikasi saat ini dikunci untuk umum. Jika Anda adalah administrator/developer dan memiliki <em>secret token</em>, Anda bisa tetap mengakses sistem secara normal menggunakan token tersebut.
@endsection

@section('actions')
    <a href="#" class="btn-custom-outline" onclick="window.location.reload();">
        <i class="fa-solid fa-rotate-right"></i>Coba Muat Ulang
    </a>
@endsection
