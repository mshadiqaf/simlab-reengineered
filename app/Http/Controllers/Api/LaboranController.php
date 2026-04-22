<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidasiPengajuanRequest;
use App\Http\Resources\PengajuanResource;
use App\Models\Pengajuan;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LaboranController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: '/api/laboran/pengajuan',
        summary: 'Daftar pengajuan yang siap divalidasi Laboran',
        description: 'Mengembalikan pengajuan yang sudah diverifikasi Kepala Lab (status: diverifikasi) dan pengajuan yang sudah selesai. Dapat difilter berdasarkan status dan tipe. Hanya dapat diakses oleh Petugas Laboran.',
        tags: ['Petugas Laboran'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Default: diverifikasi. Isi kosong untuk tampilkan semua.',
                schema: new OA\Schema(type: 'string', enum: ['diverifikasi', 'disetujui', 'ditolak', 'selesai'])
            ),
            new OA\Parameter(
                name: 'tipe',
                in: 'query',
                required: false,
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
                                new OA\Property(property: 'tipe_pengajuan', type: 'string', example: 'alat'),
                                new OA\Property(property: 'status', type: 'string', example: 'diverifikasi'),
                                new OA\Property(property: 'judul_proyek', type: 'string', example: 'Pengukuran Rangkaian'),
                                new OA\Property(property: 'dibuat_pada', type: 'string', example: '22 Apr 2026, 16:00'),
                                new OA\Property(property: 'pengaju', type: 'object', properties: [
                                    new OA\Property(property: 'nama', type: 'string', example: 'Budi Santoso'),
                                    new OA\Property(property: 'nim', type: 'string', example: '04231011'),
                                ]),
                            ]
                        ))
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak — bukan Petugas Laboran'),
        ]
    )]
    public function index(Request $request)
    {
        // Default: tampilkan yang menunggu validasi Laboran (status = diverifikasi)
        $defaultStatus = $request->status ?? 'diverifikasi';

        $pengajuans = Pengajuan::with([
                'user',
                'detailRuangan.ruangan',
                'detailAlat.alat',
                'detailUji.jenisPengujian',
            ])
            ->where('status', $defaultStatus)
            ->when($request->tipe, fn($q) => $q->where('tipe_pengajuan', $request->tipe))
            ->latest()
            ->get();

        return PengajuanResource::collection($pengajuans);
    }

    #[OA\Get(
        path: '/api/laboran/pengajuan/{id}',
        summary: 'Detail satu pengajuan untuk Laboran',
        description: 'Menampilkan detail lengkap pengajuan beserta info pengaju. Digunakan Laboran untuk mengecek detail sebelum validasi fisik.',
        tags: ['Petugas Laboran'],
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
                            new OA\Property(property: 'tipe_pengajuan', type: 'string', example: 'alat'),
                            new OA\Property(property: 'status', type: 'string', example: 'diverifikasi'),
                            new OA\Property(property: 'catatan_reviewer', type: 'string', nullable: true, example: 'Dokumen lengkap'),
                            new OA\Property(property: 'pengaju', type: 'object', properties: [
                                new OA\Property(property: 'nama', type: 'string', example: 'Budi Santoso'),
                                new OA\Property(property: 'nim', type: 'string', example: '04231011'),
                            ]),
                            new OA\Property(property: 'detail_alat', type: 'object', nullable: true, properties: [
                                new OA\Property(property: 'nama_alat', type: 'string', example: 'Osiloskop Digital'),
                                new OA\Property(property: 'jumlah_dipinjam', type: 'integer', example: 3),
                                new OA\Property(property: 'tanggal_mulai', type: 'string', example: '2026-05-01'),
                                new OA\Property(property: 'tanggal_selesai', type: 'string', example: '2026-05-03'),
                                new OA\Property(property: 'keperluan_spesifik', type: 'string', example: 'Pengukuran tegangan'),
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
        path: '/api/laboran/pengajuan/{id}/validasi',
        summary: 'Validasi logistik — setujui atau tolak pengajuan yang sudah diverifikasi',
        description: 'Laboran mengkonfirmasi ketersediaan fisik (stok alat, ketersediaan ruangan). Hanya pengajuan berstatus `diverifikasi` yang bisa divalidasi.',
        tags: ['Petugas Laboran'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['disetujui', 'ditolak'], example: 'disetujui'),
                    new OA\Property(property: 'catatan_reviewer', type: 'string', nullable: true, example: 'Stok alat tersedia, pengajuan disetujui.'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Validasi berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Pengajuan berhasil disetujui.'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'status', type: 'string', example: 'disetujui'),
                        ])
                    ]
                )
            ),
            new OA\Response(response: 409, description: 'Pengajuan belum diverifikasi Kepala Lab atau sudah diproses'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function validasi(ValidasiPengajuanRequest $request, int $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);

        // Guard: hanya pengajuan berstatus 'diverifikasi' yang bisa disetujui/ditolak Laboran
        if ($pengajuan->status !== 'diverifikasi') {
            return $this->errorResponse(
                "Pengajuan tidak dapat divalidasi karena statusnya '{$pengajuan->status}', bukan 'diverifikasi'.",
                409
            );
        }

        $pengajuan->update([
            'status'           => $request->status,
            'catatan_reviewer' => $request->catatan_reviewer ?? $pengajuan->catatan_reviewer,
        ]);

        $aksi = $request->status === 'disetujui' ? 'disetujui' : 'ditolak';

        return $this->successResponse(
            new PengajuanResource($pengajuan->fresh()),
            "Pengajuan berhasil {$aksi}."
        );
    }

    #[OA\Patch(
        path: '/api/laboran/pengajuan/{id}/selesai',
        summary: 'Tandai pengajuan sebagai selesai (pengembalian alat/ruangan)',
        description: 'Digunakan Laboran untuk menutup pengajuan setelah alat dikembalikan atau masa pemakaian ruangan berakhir. Hanya pengajuan berstatus `disetujui` yang bisa ditandai selesai.',
        tags: ['Petugas Laboran'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pengajuan berhasil ditandai selesai',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Pengajuan berhasil ditandai sebagai selesai.'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'status', type: 'string', example: 'selesai'),
                        ])
                    ]
                )
            ),
            new OA\Response(response: 409, description: 'Pengajuan belum berstatus disetujui'),
        ]
    )]
    public function selesai(int $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);

        // Guard: hanya pengajuan berstatus 'disetujui' yang bisa ditutup
        if ($pengajuan->status !== 'disetujui') {
            return $this->errorResponse(
                "Pengajuan tidak bisa ditandai selesai karena statusnya '{$pengajuan->status}', bukan 'disetujui'.",
                409
            );
        }

        $pengajuan->update(['status' => 'selesai']);

        return $this->successResponse(
            new PengajuanResource($pengajuan->fresh()),
            'Pengajuan berhasil ditandai sebagai selesai.'
        );
    }
}
