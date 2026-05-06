<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlatRequest;
use App\Http\Requests\StoreJenisPengujianRequest;
use App\Http\Requests\StoreRuanganRequest;
use App\Models\Alat;
use App\Models\JenisPengujian;
use App\Models\Ruangan;
use App\Traits\ApiResponse;
use OpenApi\Attributes as OA;

class AdminMasterDataController extends Controller
{
    use ApiResponse;

    // ═══════════════════════════════════════════════════════════════
    // RUANGAN
    // ═══════════════════════════════════════════════════════════════

    #[OA\Post(
        path: '/api/kepala-lab/ruangan',
        summary: 'Tambah ruangan baru',
        tags: ['Admin Master Data (Kepala Lab)'],
        security: [['cookieAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama_ruangan', 'kapasitas'],
                properties: [
                    new OA\Property(property: 'nama_ruangan', type: 'string', example: 'Lab. Fisika Dasar A'),
                    new OA\Property(property: 'deskripsi', type: 'string', nullable: true, example: 'Laboratorium untuk praktikum fisika dasar'),
                    new OA\Property(property: 'kapasitas', type: 'integer', example: 30),
                    new OA\Property(property: 'status_aktif', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Ruangan berhasil ditambahkan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Ruangan berhasil ditambahkan.'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'nama_ruangan', type: 'string', example: 'Lab. Fisika Dasar A'),
                            new OA\Property(property: 'kapasitas', type: 'integer', example: 30),
                            new OA\Property(property: 'status_aktif', type: 'boolean', example: true),
                        ])
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function storeRuangan(StoreRuanganRequest $request)
    {
        $ruangan = Ruangan::create($request->validated());
        return $this->successResponse($ruangan, 'Ruangan berhasil ditambahkan.', 201);
    }

    #[OA\Put(
        path: '/api/kepala-lab/ruangan/{id}',
        summary: 'Update data ruangan',
        tags: ['Admin Master Data (Kepala Lab)'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nama_ruangan', type: 'string', example: 'Lab. Fisika Dasar B'),
                    new OA\Property(property: 'deskripsi', type: 'string', nullable: true, example: 'Update deskripsi'),
                    new OA\Property(property: 'kapasitas', type: 'integer', example: 25),
                    new OA\Property(property: 'status_aktif', type: 'boolean', example: false),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ruangan berhasil diperbarui',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Ruangan berhasil diperbarui.'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'nama_ruangan', type: 'string', example: 'Lab. Fisika Dasar B'),
                            new OA\Property(property: 'kapasitas', type: 'integer', example: 25),
                        ])
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Ruangan tidak ditemukan'),
        ]
    )]
    public function updateRuangan(StoreRuanganRequest $request, int $id)
    {
        $ruangan = Ruangan::findOrFail($id);
        $ruangan->update($request->validated());
        return $this->successResponse($ruangan, 'Ruangan berhasil diperbarui.');
    }

    #[OA\Delete(
        path: '/api/kepala-lab/ruangan/{id}',
        summary: 'Hapus ruangan',
        description: 'Ruangan yang sudah memiliki riwayat pengajuan tidak bisa dihapus, hanya bisa dinonaktifkan (`status_aktif: false`).',
        tags: ['Admin Master Data (Kepala Lab)'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ruangan berhasil dihapus',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Ruangan berhasil dihapus.'),
                    ]
                )
            ),
            new OA\Response(response: 409, description: 'Ruangan tidak bisa dihapus karena memiliki riwayat pengajuan'),
            new OA\Response(response: 404, description: 'Ruangan tidak ditemukan'),
        ]
    )]
    public function destroyRuangan(int $id)
    {
        $ruangan = Ruangan::findOrFail($id);

        // Guard: jangan hapus jika masih ada riwayat pengajuan
        if ($ruangan->detailPengajuan()->exists()) {
            return $this->errorResponse(
                'Ruangan tidak dapat dihapus karena memiliki riwayat pengajuan. Nonaktifkan saja dengan mengubah status_aktif menjadi false.',
                409
            );
        }

        $ruangan->delete();
        return $this->successResponse(null, 'Ruangan berhasil dihapus.');
    }

    // ═══════════════════════════════════════════════════════════════
    // ALAT
    // ═══════════════════════════════════════════════════════════════

    #[OA\Post(
        path: '/api/kepala-lab/alat',
        summary: 'Tambah alat baru',
        tags: ['Admin Master Data (Kepala Lab)'],
        security: [['cookieAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama_alat', 'satuan', 'total_stok'],
                properties: [
                    new OA\Property(property: 'nama_alat', type: 'string', example: 'Osiloskop Digital'),
                    new OA\Property(property: 'deskripsi', type: 'string', nullable: true, example: 'Alat ukur sinyal listrik'),
                    new OA\Property(property: 'satuan', type: 'string', example: 'unit'),
                    new OA\Property(property: 'total_stok', type: 'integer', example: 10),
                    new OA\Property(property: 'status_aktif', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Alat berhasil ditambahkan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Alat berhasil ditambahkan.'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'nama_alat', type: 'string', example: 'Osiloskop Digital'),
                            new OA\Property(property: 'satuan', type: 'string', example: 'unit'),
                            new OA\Property(property: 'total_stok', type: 'integer', example: 10),
                        ])
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function storeAlat(StoreAlatRequest $request)
    {
        $alat = Alat::create($request->validated());
        return $this->successResponse($alat, 'Alat berhasil ditambahkan.', 201);
    }

    #[OA\Put(
        path: '/api/kepala-lab/alat/{id}',
        summary: 'Update data alat',
        tags: ['Admin Master Data (Kepala Lab)'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nama_alat', type: 'string', example: 'Osiloskop Analog'),
                    new OA\Property(property: 'satuan', type: 'string', example: 'unit'),
                    new OA\Property(property: 'total_stok', type: 'integer', example: 8),
                    new OA\Property(property: 'status_aktif', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Alat berhasil diperbarui',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Alat berhasil diperbarui.'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'nama_alat', type: 'string', example: 'Osiloskop Analog'),
                            new OA\Property(property: 'total_stok', type: 'integer', example: 8),
                        ])
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Alat tidak ditemukan'),
        ]
    )]
    public function updateAlat(StoreAlatRequest $request, int $id)
    {
        $alat = Alat::findOrFail($id);
        $alat->update($request->validated());
        return $this->successResponse($alat, 'Alat berhasil diperbarui.');
    }

    #[OA\Delete(
        path: '/api/kepala-lab/alat/{id}',
        summary: 'Hapus alat',
        tags: ['Admin Master Data (Kepala Lab)'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Alat berhasil dihapus',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'message', type: 'string', example: 'Alat berhasil dihapus.')]
                )
            ),
            new OA\Response(response: 409, description: 'Alat memiliki riwayat pengajuan'),
        ]
    )]
    public function destroyAlat(int $id)
    {
        $alat = Alat::findOrFail($id);

        if ($alat->detailPengajuan()->exists()) {
            return $this->errorResponse(
                'Alat tidak dapat dihapus karena memiliki riwayat pengajuan. Nonaktifkan saja.',
                409
            );
        }

        $alat->delete();
        return $this->successResponse(null, 'Alat berhasil dihapus.');
    }

    // ═══════════════════════════════════════════════════════════════
    // JENIS PENGUJIAN
    // ═══════════════════════════════════════════════════════════════

    #[OA\Post(
        path: '/api/kepala-lab/jenis-pengujian',
        summary: 'Tambah jenis pengujian baru',
        tags: ['Admin Master Data (Kepala Lab)'],
        security: [['cookieAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama_pengujian'],
                properties: [
                    new OA\Property(property: 'nama_pengujian', type: 'string', example: 'Uji Tarik Material'),
                    new OA\Property(property: 'deskripsi', type: 'string', nullable: true, example: 'Pengujian kekuatan tarik bahan logam/polimer'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Jenis pengujian berhasil ditambahkan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Jenis pengujian berhasil ditambahkan.'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'nama_pengujian', type: 'string', example: 'Uji Tarik Material'),
                        ])
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function storeJenisPengujian(StoreJenisPengujianRequest $request)
    {
        $jenis = JenisPengujian::create($request->validated());
        return $this->successResponse($jenis, 'Jenis pengujian berhasil ditambahkan.', 201);
    }

    #[OA\Put(
        path: '/api/kepala-lab/jenis-pengujian/{id}',
        summary: 'Update jenis pengujian',
        tags: ['Admin Master Data (Kepala Lab)'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nama_pengujian', type: 'string', example: 'Uji Lentur Material'),
                    new OA\Property(property: 'deskripsi', type: 'string', nullable: true, example: 'Update deskripsi'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Jenis pengujian berhasil diperbarui'),
            new OA\Response(response: 404, description: 'Tidak ditemukan'),
        ]
    )]
    public function updateJenisPengujian(StoreJenisPengujianRequest $request, int $id)
    {
        $jenis = JenisPengujian::findOrFail($id);
        $jenis->update($request->validated());
        return $this->successResponse($jenis, 'Jenis pengujian berhasil diperbarui.');
    }

    #[OA\Delete(
        path: '/api/kepala-lab/jenis-pengujian/{id}',
        summary: 'Hapus jenis pengujian',
        tags: ['Admin Master Data (Kepala Lab)'],
        security: [['cookieAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Jenis pengujian berhasil dihapus'),
            new OA\Response(response: 409, description: 'Masih memiliki riwayat pengajuan'),
        ]
    )]
    public function destroyJenisPengujian(int $id)
    {
        $jenis = JenisPengujian::findOrFail($id);

        if ($jenis->detailPengajuan()->exists()) {
            return $this->errorResponse(
                'Jenis pengujian tidak dapat dihapus karena memiliki riwayat pengajuan.',
                409
            );
        }

        $jenis->delete();
        return $this->successResponse(null, 'Jenis pengujian berhasil dihapus.');
    }
}
