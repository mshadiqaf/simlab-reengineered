<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePengajuanRequest;
use App\Http\Resources\PengajuanResource;
use App\Models\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class PengajuanController extends Controller
{
    #[OA\Post(
        path: '/api/pengajuan',
        summary: 'Membuat pengajuan baru (ruangan / alat / pengujian)',
        description: 'Endpoint tunggal untuk semua tipe pengajuan. Field yang diperlukan bervariasi tergantung `tipe_pengajuan`.',
        tags: ['Pengguna (Semua Role)'],
        security: [['cookieAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['tipe_pengajuan', 'nomor_hp', 'tujuan_penggunaan'],
                properties: [
                    new OA\Property(property: 'tipe_pengajuan', type: 'string', enum: ['ruangan', 'alat', 'pengujian'], example: 'ruangan'),
                    new OA\Property(property: 'nomor_hp', type: 'string', example: '08123456789'),
                    new OA\Property(property: 'judul_proyek', type: 'string', nullable: true, example: 'Praktikum Fisika Dasar'),
                    new OA\Property(property: 'tujuan_penggunaan', type: 'string', example: 'Digunakan untuk praktikum semester 2'),
                    new OA\Property(property: 'dosen_pembimbing', type: 'string', nullable: true, example: 'Dr. Ahmad Fauzi'),
                    new OA\Property(property: 'email_dosen', type: 'string', nullable: true, example: 'fauzi@lecturer.itk.ac.id'),
                    new OA\Property(property: 'ruangan_id', type: 'integer', nullable: true, description: 'Wajib jika tipe = ruangan', example: 1),
                    new OA\Property(property: 'tanggal_mulai', type: 'string', format: 'date', nullable: true, example: '2026-05-01'),
                    new OA\Property(property: 'tanggal_selesai', type: 'string', format: 'date', nullable: true, example: '2026-05-01'),
                    new OA\Property(property: 'waktu_mulai', type: 'string', nullable: true, example: '08:00'),
                    new OA\Property(property: 'waktu_selesai', type: 'string', nullable: true, example: '12:00'),
                    new OA\Property(property: 'jumlah_pengguna', type: 'integer', nullable: true, example: 20),
                    new OA\Property(property: 'alat_id', type: 'integer', nullable: true, description: 'Wajib jika tipe = alat', example: 2),
                    new OA\Property(property: 'jumlah_dipinjam', type: 'integer', nullable: true, example: 3),
                    new OA\Property(property: 'tanggal_mulai_alat', type: 'string', format: 'date', nullable: true, example: '2026-05-01'),
                    new OA\Property(property: 'tanggal_selesai_alat', type: 'string', format: 'date', nullable: true, example: '2026-05-03'),
                    new OA\Property(property: 'keperluan_spesifik', type: 'string', nullable: true, example: 'Untuk pengukuran tegangan rangkaian'),
                    new OA\Property(property: 'jenis_pengujian_id', type: 'integer', nullable: true, description: 'Wajib jika tipe = pengujian', example: 1),
                    new OA\Property(property: 'nama_sampel', type: 'string', nullable: true, example: 'Beton campuran A'),
                    new OA\Property(property: 'jumlah_sampel', type: 'integer', nullable: true, example: 5),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Pengajuan berhasil dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'tipe_pengajuan', type: 'string', example: 'ruangan'),
                            new OA\Property(property: 'status', type: 'string', example: 'diajukan'),
                            new OA\Property(property: 'dibuat_pada', type: 'string', example: '22 Apr 2026, 16:00'),
                        ])
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function store(StorePengajuanRequest $request)
    {
        $data = $request->validated();

        // Upload surat pengantar jika ada
        $suratPath = null;
        if ($request->hasFile('surat_pengantar')) {
            $suratPath = $request->file('surat_pengantar')
                ->store('surat_pengantar', 'public');
        }

        DB::transaction(function () use ($data, $suratPath, $request, &$pengajuan) {
            // 1. Buat record induk di tabel pengajuans
            $pengajuan = Pengajuan::create([
                'user_id'            => $request->user()->id,
                'nomor_hp'           => $data['nomor_hp'],
                'tipe_pengajuan'     => $data['tipe_pengajuan'],
                'status'             => 'diajukan',
                'judul_proyek'       => $data['judul_proyek'] ?? null,
                'tujuan_penggunaan'  => $data['tujuan_penggunaan'],
                'dosen_pembimbing'   => $data['dosen_pembimbing'] ?? null,
                'email_dosen'        => $data['email_dosen'] ?? null,
                'surat_pengantar_path' => $suratPath,
            ]);

            // 2. Buat record detail sesuai tipe
            match ($data['tipe_pengajuan']) {
                'ruangan' => $pengajuan->detailRuangan()->create([
                    'ruangan_id'            => $data['ruangan_id'],
                    'tanggal_mulai'         => $data['tanggal_mulai'],
                    'tanggal_selesai'       => $data['tanggal_selesai'],
                    'waktu_mulai'           => $data['waktu_mulai'],
                    'waktu_selesai'         => $data['waktu_selesai'],
                    'jumlah_pengguna'       => $data['jumlah_pengguna'],
                    'nama_pengguna_lainnya' => $data['nama_pengguna_lainnya'] ?? null,
                    'catatan_alat_bahan'    => $data['catatan_alat_bahan'] ?? null,
                ]),
                'alat' => $pengajuan->detailAlat()->create([
                    'alat_id'              => $data['alat_id'],
                    'jumlah_dipinjam'      => $data['jumlah_dipinjam'],
                    'tanggal_mulai'        => $data['tanggal_mulai_alat'],
                    'tanggal_selesai'      => $data['tanggal_selesai_alat'],
                    'keperluan_spesifik'   => $data['keperluan_spesifik'],
                    'durasi_jam'           => $data['durasi_jam'] ?? null,
                ]),
                'pengujian' => $pengajuan->detailUji()->create([
                    'jenis_pengujian_id'  => $data['jenis_pengujian_id'],
                    'nama_sampel'         => $data['nama_sampel'],
                    'jumlah_sampel'       => $data['jumlah_sampel'],
                    'keterangan_tambahan' => $data['keterangan_tambahan'] ?? null,
                ]),
            };
        });

        return new PengajuanResource(
            $pengajuan->load(['user', 'detailRuangan.ruangan', 'detailAlat.alat', 'detailUji.jenisPengujian'])
        );
    }

    #[OA\Get(
        path: '/api/pengajuan',
        summary: 'Mengambil riwayat seluruh pengajuan milik user yang sedang login',
        tags: ['Pengguna (Semua Role)'],
        security: [['cookieAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar riwayat pengajuan berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'tipe_pengajuan', type: 'string', example: 'ruangan'),
                                new OA\Property(property: 'status', type: 'string', example: 'diajukan'),
                                new OA\Property(property: 'nomor_hp', type: 'string', example: '08123456789'),
                                new OA\Property(property: 'judul_proyek', type: 'string', example: 'Praktikum Fisika Dasar'),
                                new OA\Property(property: 'dibuat_pada', type: 'string', example: '22 Apr 2026, 16:00'),
                            ]
                        ))
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request)
    {
        $pengajuans = Pengajuan::with(['detailRuangan.ruangan', 'detailAlat.alat', 'detailUji.jenisPengujian'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return PengajuanResource::collection($pengajuans);
    }

    #[OA\Get(
        path: '/api/pengajuan/{id}',
        summary: 'Mengambil detail satu pengajuan berdasarkan ID',
        tags: ['Pengguna (Semua Role)'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail pengajuan berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'tipe_pengajuan', type: 'string', example: 'ruangan'),
                            new OA\Property(property: 'status', type: 'string', example: 'diajukan'),
                            new OA\Property(property: 'judul_proyek', type: 'string', example: 'Praktikum Fisika Dasar'),
                            new OA\Property(property: 'tujuan_penggunaan', type: 'string', example: 'Digunakan untuk praktikum semester 2'),
                            new OA\Property(property: 'dibuat_pada', type: 'string', example: '22 Apr 2026, 16:00'),
                            new OA\Property(property: 'detail_ruangan', type: 'object', nullable: true, properties: [
                                new OA\Property(property: 'ruangan', type: 'string', example: 'Lab. Fisika Dasar A (R202)'),
                                new OA\Property(property: 'tanggal_mulai', type: 'string', example: '2026-05-01'),
                                new OA\Property(property: 'tanggal_selesai', type: 'string', example: '2026-05-01'),
                                new OA\Property(property: 'waktu_mulai', type: 'string', example: '08:00:00'),
                                new OA\Property(property: 'waktu_selesai', type: 'string', example: '12:00:00'),
                            ]),
                        ])
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Anda tidak memiliki akses ke pengajuan ini'),
            new OA\Response(response: 404, description: 'Pengajuan tidak ditemukan'),
        ]
    )]
    public function show(Request $request, int $id)
    {
        $pengajuan = Pengajuan::with(['detailRuangan.ruangan', 'detailAlat.alat', 'detailUji.jenisPengujian'])
            ->findOrFail($id);

        // Pastikan user hanya bisa melihat pengajuan miliknya sendiri
        if ($pengajuan->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke pengajuan ini.'], 403);
        }

        return new PengajuanResource($pengajuan);
    }
}
