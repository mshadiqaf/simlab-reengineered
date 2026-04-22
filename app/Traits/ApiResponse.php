<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    /**
     * Response sukses untuk aksi mutasi (POST create, PATCH action).
     * Format: { "message": "...", "data": { ... } }
     */
    protected function successResponse(
        mixed $data,
        string $message = 'Berhasil.',
        int $code = 200
    ): JsonResponse {
        // Jika $data adalah Resource Laravel, panggil resolve() agar tidak double-wrap
        $payload = ($data instanceof JsonResource || $data instanceof ResourceCollection)
            ? $data->resolve(request())
            : $data;

        return response()->json([
            'message' => $message,
            'data'    => $payload,
        ], $code);
    }

    /**
     * Response error sederhana.
     * Format: { "message": "..." }
     */
    protected function errorResponse(string $message, int $code): JsonResponse
    {
        return response()->json(['message' => $message], $code);
    }
}
