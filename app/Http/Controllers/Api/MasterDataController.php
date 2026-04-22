<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Models\Alat;
use App\Models\JenisPengujian;
use App\Http\Resources\RuanganResource;
use App\Http\Resources\AlatResource;
use App\Http\Resources\JenisPengujianResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MasterDataController extends Controller
{
    #[OA\Get(
        path: '/api/ruangan',
        summary: 'Mengambil daftar seluruh ruangan laboratorium',
        tags: ['Master Data'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar ruangan berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'nama_ruangan', type: 'string', example: 'Labter 1 - Lab. Fisika Dasar A (R202)'),
                                new OA\Property(property: 'kapasitas', type: 'integer', example: 40),
                            ]
                        ))
                    ]
                )
            )
        ]
    )]
    public function getRuangan()
    {
        $ruangans = Ruangan::orderBy('nama_ruangan', 'asc')->get();
        return RuanganResource::collection($ruangans);
    }

    #[OA\Get(
        path: '/api/alat',
        summary: 'Mengambil daftar alat yang tersedia (stok > 0)',
        tags: ['Master Data'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar alat berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'nama_alat', type: 'string', example: 'Osiloskop Digital'),
                                new OA\Property(property: 'satuan', type: 'string', example: 'Unit'),
                                new OA\Property(property: 'total_stok', type: 'integer', example: 10),
                            ]
                        ))
                    ]
                )
            )
        ]
    )]
    public function getAlat()
    {
        $alats = Alat::where('total_stok', '>', 0)->orderBy('nama_alat', 'asc')->get();
        return AlatResource::collection($alats);
    }

    #[OA\Get(
        path: '/api/pengujian',
        summary: 'Mengambil daftar jenis pengujian yang tersedia',
        tags: ['Master Data'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar jenis pengujian berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'nama_pengujian', type: 'string', example: 'Uji Kuat Tekan Beton'),
                            ]
                        ))
                    ]
                )
            )
        ]
    )]
    public function getJenisPengujian()
    {
        $ujis = JenisPengujian::orderBy('nama_pengujian', 'asc')->get();
        return JenisPengujianResource::collection($ujis);
    }
}
