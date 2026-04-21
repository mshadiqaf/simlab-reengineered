<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

/**
 * File ini berfungsi sebagai "Halaman Sampul" dokumentasi Swagger SIMLAB.
 * Di sinilah informasi global API (Judul, Versi, Server) dideklarasikan.
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
class OpenApiSpec {}
