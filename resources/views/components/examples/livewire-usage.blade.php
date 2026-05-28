{{-- Contoh pemasangan komponen Livewire LMS Praktikum --}}

{{-- Navbar / layout utama --}}
<livewire:notification-dropdown />

{{-- Halaman chatbot mahasiswa: resources/views/student/chatbot/index.blade.php --}}
<livewire:chatbot-widget />

{{-- Halaman absensi mahasiswa atau asisten --}}
<livewire:absensi-live />

{{-- Kalau ingin dibatasi ke kelas tertentu --}}
<livewire:absensi-live :class-id="$class->id" />
