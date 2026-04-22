<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifikasiPengajuanRequest;
use App\Http\Resources\PengajuanResource;
use App\Models\Pengajuan;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class KepalaLabController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/api/kepala-lab/pengajuan',
        summary: 'Daftar semua pengajuan masuk (Kepala Lab)',
        description: 'Mengembalikan seluruh pengajuan dari semua user. Dapat difilter berdasarkan status dan tipe. Hanya dapat diakses oleh user dengan role Kepala Laboratorium.',
        tags: ['Kepala Laboratorium'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter berdasarkan status pengajuan',
                schema: new OA\Schema(type: 'string', enum: ['diajukan', 'diverifikasi', 'ditolak', 'disetujui', 'selesai'])
            ),
            new OA\Parameter(
                name: 'tipe',
                in: 'query',
                required: false,
                description: 'Filter berdasarkan tipe pengajuan',
                schema: new OA\Schema(type: 'string', enum: ['ruangan', 'alat', 'pengujian'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar pengajuan berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'tipe_pengajuan', type: 'string', example: 'ruangan'),
                                new OA\Property(property: 'status', type: 'string', example: 'diajukan'),
                                new OA\Property(property: 'judul_proyek', type: 'string', example: 'Praktikum Fisika Dasar'),
                                new OA\Property(property: 'dibuat_pada', type: 'string', example: '22 Apr 2026, 16:00'),
                                new OA\Property(property: 'pengaju', type: 'object', properties: [
                                    new OA\Property(property: 'nama', type: 'string', example: 'Budi Santoso'),
                                    new OA\Property(property: 'nim', type: 'string', example: '04231011'),
                                    new OA\Property(property: 'program_studi', type: 'string', example: 'Teknik Elektro'),
                                ]),
                            ]
                        ))
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak — bukan Kepala Laboratorium'),
        ]
    )]
    public function index(Request $request)
    {
        $pengajuans = Pengajuan::with([
                'user',
                'detailRuangan.ruangan',
                'detailAlat.alat',
                'detailUji.jenisPengujian',
            ])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->tipe,   fn($q) => $q->where('tipe_pengajuan', $request->tipe))
            ->latest()
            ->get();

        return PengajuanResource::collection($pengajuans);
    }

    #[OA\Get(
        path: '/api/kepala-lab/pengajuan/{id}',
        summary: 'Detail satu pengajuan (Kepala Lab)',
        description: 'Menampilkan seluruh detail pengajuan beserta data pengaju. Digunakan Kepala Lab sebelum mengambil keputusan verifikasi.',
        tags: ['Kepala Laboratorium'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
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
                            new OA\Property(property: 'catatan_reviewer', type: 'string', nullable: true, example: null),
                            new OA\Property(property: 'dibuat_pada', type: 'string', example: '22 Apr 2026, 16:00'),
                            new OA\Property(property: 'pengaju', type: 'object', properties: [
                                new OA\Property(property: 'nama', type: 'string', example: 'Budi Santoso'),
                                new OA\Property(property: 'nim', type: 'string', example: '04231011'),
                                new OA\Property(property: 'program_studi', type: 'string', example: 'Teknik Elektro'),
                                new OA\Property(property: 'email', type: 'string', example: 'budi@student.itk.ac.id'),
                            ]),
                            new OA\Property(property: 'detail_ruangan', type: 'object', nullable: true, properties: [
                                new OA\Property(property: 'ruangan', type: 'string', example: 'Lab. Fisika Dasar A (R202)'),
                                new OA\Property(property: 'tanggal_mulai', type: 'string', example: '2026-05-01'),
                                new OA\Property(property: 'tanggal_selesai', type: 'string', example: '2026-05-01'),
                                new OA\Property(property: 'waktu_mulai', type: 'string', example: '08:00:00'),
                                new OA\Property(property: 'waktu_selesai', type: 'string', example: '12:00:00'),
                                new OA\Property(property: 'jumlah_pengguna', type: 'integer', example: 20),
                            ]),
                        ])
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Pengajuan tidak ditemukan'),
        ]
    )]
    public function show(int $id)
    {
        $pengajuan = Pengajuan::with([
            'user',
            'detailRuangan.ruangan',
            'detailAlat.alat',
            'detailUji.jenisPengujian',
        ])->findOrFail($id);

        return new PengajuanResource($pengajuan);
    }

    #[OA\Patch(
        path: '/api/kepala-lab/pengajuan/{id}/verifikasi',
        summary: 'Verifikasi (setujui atau tolak) sebuah pengajuan',
        description: 'Mengubah status pengajuan menjadi `diverifikasi` (lanjut ke Laboran) atau `ditolak`. Catatan reviewer bersifat opsional namun sangat disarankan diisi saat menolak.',
        tags: ['Kepala Laboratorium'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['diverifikasi', 'ditolak'], example: 'diverifikasi'),
                    new OA\Property(property: 'catatan_reviewer', type: 'string', nullable: true, example: 'Dokumen lengkap, pengajuan disetujui untuk diproses Laboran.'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status pengajuan berhasil diperbarui',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Pengajuan berhasil diverifikasi.'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'status', type: 'string', example: 'diverifikasi'),
                            new OA\Property(property: 'catatan_reviewer', type: 'string', example: 'Dokumen lengkap.'),
                        ])
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validasi gagal atau status tidak valid'),
            new OA\Response(response: 409, description: 'Pengajuan sudah pernah diverifikasi sebelumnya'),
            new OA\Response(response: 404, description: 'Pengajuan tidak ditemukan'),
        ]
    )]
    public function verifikasi(VerifikasiPengajuanRequest $request, int $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);

        // Guard: hanya pengajuan berstatus 'diajukan' yang bisa diverifikasi
        if ($pengajuan->status !== 'diajukan') {
            return $this->errorResponse(
                "Pengajuan tidak dapat diverifikasi karena statusnya sudah '{$pengajuan->status}'.",
                409
            );
        }

        $pengajuan->update([
            'status'           => $request->status,
            'catatan_reviewer' => $request->catatan_reviewer,
        ]);

        $aksi = $request->status === 'diverifikasi' ? 'diverifikasi' : 'ditolak';

        return $this->successResponse(
            new PengajuanResource($pengajuan->fresh()),
            "Pengajuan berhasil {$aksi}."
        );
    }
}
