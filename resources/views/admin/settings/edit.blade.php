@extends('layouts.app', ['title' => 'Pengaturan Sistem'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Pengaturan Sistem', 'description' => 'Konfigurasi identitas aplikasi. Data disimpan sederhana melalui file/config sesuai implementasi controller.'])
<form action="{{ route('admin.settings.update') }}" method="POST" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
<div class="grid gap-5 md:grid-cols-2">
@include('partials.form.input', ['label' => 'Nama Kampus', 'name' => 'campus_name', 'value' => $settings['campus_name'] ?? ''])
@include('partials.form.input', ['label' => 'Nama Sistem', 'name' => 'system_name', 'value' => $settings['system_name'] ?? 'LMS Praktikum'])
@include('partials.form.input', ['label' => 'Email Kontak', 'name' => 'contact_email', 'type' => 'email', 'value' => $settings['contact_email'] ?? ''])
@include('partials.form.input', ['label' => 'URL Logo', 'name' => 'logo_url', 'value' => $settings['logo_url'] ?? ''])
</div>
@include('partials.form.actions', ['cancel' => route('admin.dashboard'), 'label' => 'Simpan Pengaturan'])
</form>
@endsection
