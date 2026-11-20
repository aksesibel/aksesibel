<?php

return [
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'unique' => ':attribute sudah digunakan.',
    'exists' => ':attribute yang dipilih tidak tersedia.',
    'confirmed' => 'Konfirmasi :attribute tidak sesuai.',
    'boolean' => ':attribute harus bernilai benar atau salah.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'date' => ':attribute harus berupa tanggal yang valid.',
    'date_format' => 'Format :attribute harus :format.',
    'after_or_equal' => ':attribute harus sama atau sesudah :date.',
    'in' => ':attribute yang dipilih tidak valid.',
    'prohibited' => ':attribute tidak boleh diubah melalui halaman ini.',
    'min' => ['string' => ':attribute minimal :min karakter.', 'numeric' => ':attribute minimal :min.'],
    'max' => ['string' => ':attribute maksimal :max karakter.', 'numeric' => ':attribute maksimal :max.'],
    'attributes' => [
        'name' => 'Nama', 'email' => 'Email', 'password' => 'Password',
        'role_id' => 'Peran', 'account_status' => 'Status akun', 'category_name' => 'Nama kategori',
        'description' => 'Deskripsi', 'is_active' => 'Status aktif', 'period_name' => 'Nama periode',
        'start_date' => 'Tanggal mulai', 'end_date' => 'Tanggal selesai', 'q' => 'Pencarian',
        'period_id' => 'Periode pelaporan', 'category_id' => 'Kategori fasilitas', 'issue_category_id' => 'Kategori kendala',
    ],
];
