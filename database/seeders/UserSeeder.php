<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $mahasiswa = User::create([
            'name' => 'Budi Santoso',
            'nim' => '04231011',
            'program_studi' => 'Teknik Elektro',
            'email' => 'budi@student.itk.ac.id',
            'password' => Hash::make('password')
        ]);
        $mahasiswa->assignRole('Mahasiswa');

        $kalab = User::create([
            'name' => 'Dr. Ing. Ahmad Fauzi',
            'nim' => '198001012005011001',
            'program_studi' => '-',
            'email' => 'fauzi@lecturer.itk.ac.id',
            'password' => Hash::make('password')
        ]);
        $kalab->assignRole('Kepala Laboratorium');

        $laboran = User::create([
            'name' => 'Siti Aminah, A.Md',
            'nim' => '-',
            'program_studi' => '-',
            'email' => 'siti.laboran@itk.ac.id',
            'password' => Hash::make('password')
        ]);
        $laboran->assignRole('Petugas Laboran');
    }
}
