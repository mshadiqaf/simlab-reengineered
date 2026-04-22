<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Mengembalikan data user yang sedang login beserta role-nya.
     * Endpoint ini diproteksi middleware auth — hanya user yang sudah login yang bisa mengakses.
     */
    #[OA\Get(
        path: '/api/user',
        summary: 'Mengambil data profil user yang sedang login',
        description: 'Mengembalikan data lengkap user yang terautentikasi beserta role Spatie-nya. Digunakan frontend untuk menampilkan menu yang berbeda berdasarkan peran.',
        tags: ['Autentikasi'],
        security: [['cookieAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data user berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                                new OA\Property(property: 'nim', type: 'string', example: '04231011'),
                                new OA\Property(property: 'program_studi', type: 'string', example: 'Teknik Elektro'),
                                new OA\Property(property: 'email', type: 'string', example: 'budi@student.itk.ac.id'),
                                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['Mahasiswa']),
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated — User belum login'),
        ]
    )]
    public function user(Request $request)
    {
        return new UserResource($request->user());
    }
}
