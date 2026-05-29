<?php

return [
    'uploaded' => ':attribute gagal diunggah. Cek ukuran file, upload_max_filesize, post_max_size, dan koneksi upload.',
    'file' => ':attribute harus berupa file yang valid.',
    'max' => [
        'file' => ':attribute maksimal :max kilobyte.',
        'string' => ':attribute maksimal :max karakter.',
        'numeric' => ':attribute maksimal :max.',
        'array' => ':attribute maksimal :max item.',
    ],
    'required' => ':attribute wajib diisi.',
    'mimes' => ':attribute harus berupa file dengan tipe: :values.',
    'image' => ':attribute harus berupa gambar.',
    'attributes' => [
        'file' => 'File',
        'logo' => 'Logo',
    ],
];
