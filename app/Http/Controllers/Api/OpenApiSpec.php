<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

/**
 * File ini berfungsi sebagai "Halaman Sampul" dokumentasi Swagger SIMLAB.
 * Di sinilah informasi global API (Judul, Versi, Server) dideklarasikan.
 * Endpoint Fortify (register/login) juga didokumentasikan di sini secara virtual
 * karena rutenya dikelola Fortify, bukan controller custom.
 */
#[OA\Info(
    version: '1.0.0',
    title: 'SIMLAB API',
    description: 'Dokumentasi REST API untuk Sistem Informasi Manajemen Laboratorium (SIMLAB) - Institut Teknologi Kalimantan'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Server Pengembangan Lokal'
)]
#[OA\Tag(
    name: 'Master Data',
    description: 'Endpoint untuk mengambil data referensi (Ruangan, Alat, Jenis Pengujian)'
)]
#[OA\Tag(
    name: 'Autentikasi',
    description: 'Endpoint untuk login, register, logout, dan data user aktif'
)]

// ============================================================
// Dokumentasi virtual untuk endpoint yang dikelola Fortify
// (tidak ada controller custom, tapi tetap perlu terdokumentasi)
// ============================================================

#[OA\Post(
    path: '/register',
    summary: 'Mendaftarkan akun baru (Mahasiswa / Dosen / Eksternal)',
    description: 'Dikelola oleh Laravel Fortify. Setelah berhasil, user langsung diarahkan ke halaman dashboard (redirect web, bukan JSON).',
    tags: ['Autentikasi'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'identitas', 'email', 'kategori_pendaftar', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                new OA\Property(property: 'identitas', type: 'string', description: 'NIM / NIP / NIPH / Identitas lainnya', example: '04231011'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@student.itk.ac.id'),
                new OA\Property(property: 'kategori_pendaftar', type: 'string', enum: ['mahasiswa', 'dosen', 'eksternal'], example: 'mahasiswa'),
                new OA\Property(property: 'program_studi', type: 'string', nullable: true, description: 'Wajib diisi jika kategori mahasiswa/dosen', example: 'Teknik Elektro'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'secret123'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 302, description: 'Redirect ke dashboard setelah registrasi berhasil'),
        new OA\Response(response: 422, description: 'Validasi gagal — data tidak lengkap atau tidak sesuai format'),
    ]
)]

#[OA\Post(
    path: '/login',
    summary: 'Login ke sistem menggunakan email dan password',
    description: 'Dikelola oleh Laravel Fortify. Menggunakan session cookie (bukan Bearer Token).',
    tags: ['Autentikasi'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@student.itk.ac.id'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 302, description: 'Redirect ke dashboard setelah login berhasil'),
        new OA\Response(response: 422, description: 'Kredensial salah'),
    ]
)]

#[OA\Post(
    path: '/logout',
    summary: 'Logout dari sistem',
    description: 'Dikelola oleh Laravel Fortify. Menghapus sesi aktif user.',
    tags: ['Autentikasi'],
    responses: [
        new OA\Response(response: 302, description: 'Redirect ke halaman utama setelah logout'),
    ]
)]
class OpenApiSpec {}
