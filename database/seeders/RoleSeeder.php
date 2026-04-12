<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'Mahasiswa']);
        Role::create(['name' => 'Kepala Laboratorium']);
        Role::create(['name' => 'Petugas Laboran']);
    }
}
