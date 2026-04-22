<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Aturan validasi untuk EDIT PROFIL (Settings).
     * Hanya name + email. Tidak perlu identitas/kategori.
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name'  => $this->nameRules(),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Aturan validasi TAMBAHAN khusus untuk form REGISTER.
     * Dipanggil hanya oleh CreateNewUser, bukan ProfileUpdateRequest.
     */
    protected function registrationRules(): array
    {
        return [
            'identitas'          => $this->identitasRules(),
            'kategori_pendaftar' => $this->kategoriRules(),
            'program_studi'      => $this->programStudiRules(),
        ];
    }

    /**
     * NIM / NIP / NIPH / Identitas Lainnya — selalu required saat daftar.
     */
    protected function identitasRules(): array
    {
        return ['required', 'string', 'max:50'];
    }

    /**
     * Kategori pendaftar: mahasiswa | dosen | eksternal.
     */
    protected function kategoriRules(): array
    {
        return ['required', 'string', 'in:mahasiswa,dosen,eksternal'];
    }

    /**
     * Program Studi / Instansi — wajib hanya jika kategori mahasiswa/dosen.
     */
    protected function programStudiRules(): array
    {
        return ['nullable', 'string', 'max:255', 'required_if:kategori_pendaftar,mahasiswa', 'required_if:kategori_pendaftar,dosen'];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }
}
