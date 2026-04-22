<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            // Aturan dasar profil (name + email)
            ...$this->profileRules(),
            // Aturan tambahan khusus register (identitas, kategori, prodi)
            ...$this->registrationRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name'          => $input['name'],
            'email'         => $input['email'],
            'password'      => $input['password'],
            'nim'           => $input['identitas'],
            'program_studi' => $input['program_studi'] ?? null,
        ]);

        // Mahasiswa dan dosen (serta eksternal) semua mendapat role 'Mahasiswa'.
        // Role Kepala Laboratorium & Laboran diberikan secara manual oleh admin.
        $user->assignRole('Mahasiswa');

        return $user;
    }
}
