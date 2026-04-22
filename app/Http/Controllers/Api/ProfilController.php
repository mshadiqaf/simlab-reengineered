<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfilRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProfilController extends Controller
{
    #[OA\Get(
        path: '/api/profil',
        summary: 'Mengambil profil user yang sedang login beserta role-nya',
        tags: ['Pengguna (Semua Role)'],
        security: [['cookieAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                            new OA\Property(property: 'nim', type: 'string', example: '04231011'),
                            new OA\Property(property: 'program_studi', type: 'string', example: 'Teknik Elektro'),
                            new OA\Property(property: 'email', type: 'string', example: 'budi@student.itk.ac.id'),
                            new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['Mahasiswa']),
                        ])
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    #[OA\Put(
        path: '/api/profil',
        summary: 'Memperbarui profil user (nama, NIM, program studi)',
        tags: ['Pengguna (Semua Role)'],
        security: [['cookieAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                    new OA\Property(property: 'nim', type: 'string', example: '04231011'),
                    new OA\Property(property: 'program_studi', type: 'string', example: 'Teknik Elektro'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Profil berhasil diperbarui'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function update(UpdateProfilRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user->fresh());
    }
}
