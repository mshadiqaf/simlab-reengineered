<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Memformat data user + roles untuk dikonsumsi Frontend.
     * Kolom sensitif (password, remember_token, dll) otomatis tersembunyi
     * karena sudah didefinisikan di #[Hidden] pada model User.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'nim'           => $this->nim,
            'program_studi' => $this->program_studi,
            'email'         => $this->email,
            // Mengembalikan daftar nama role sebagai array string sederhana
            // Contoh: ['Mahasiswa'] atau ['Kepala Laboratorium']
            'roles'         => $this->getRoleNames(),
        ];
    }
}
