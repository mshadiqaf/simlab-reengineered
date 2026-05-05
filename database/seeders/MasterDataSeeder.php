<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgramStudy;
use App\Models\Ruangan;
use App\Models\Alat;
use App\Models\JenisPengujian;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Program Studies ─────────────────────────────────────────────
        $programStudies = [
            ['name' => 'S1 Teknik Elektro',           'code' => 'TLE'],
            ['name' => 'S1 Teknik Mesin',             'code' => 'TMS'],
            ['name' => 'S1 Teknik Sipil',             'code' => 'TSP'],
            ['name' => 'S1 Teknik Kimia',             'code' => 'TKI'],
            ['name' => 'S1 Teknik Informatika',       'code' => 'TIF'],
            ['name' => 'S1 Sistem Informasi',         'code' => 'SIF'],
            ['name' => 'S1 Fisika',                   'code' => 'FIS'],
            ['name' => 'S1 Kimia',                    'code' => 'KIM'],
        ];

        foreach ($programStudies as $ps) {
            ProgramStudy::create($ps);
        }

        // ─── Rooms ───────────────────────────────────────────────────────
        $rooms = [
            ['nama_ruangan' => 'Workshop B - Tuning & Assembly (1.5)', 'kapasitas' => 30],
            ['nama_ruangan' => 'Labter 1 - Lab. Fisika Dasar A (R202)', 'kapasitas' => 40],
            ['nama_ruangan' => 'Labter 1 - Bengkel B - Perakitan (R102)', 'kapasitas' => 20],
            ['nama_ruangan' => 'Labter 1 - Lab. Kimia Dasar B (R208)', 'kapasitas' => 40],
            ['nama_ruangan' => 'Labter 1 - Lab. Sistem Tenaga Listrik dan Otomasi (R106)', 'kapasitas' => 25],
        ];

        foreach ($rooms as $r) {
            Ruangan::create($r);
        }

        // ─── Equipment ───────────────────────────────────────────────────
        $equipment = [
            ['nama_alat' => 'Osiloskop Digital',    'satuan' => 'Unit', 'total_stock' => 10, 'available_stock' => 10],
            ['nama_alat' => 'Mikroskop Binokuler',  'satuan' => 'Unit', 'total_stock' => 15, 'available_stock' => 15],
            ['nama_alat' => 'Multimeter Digital',   'satuan' => 'Unit', 'total_stock' => 20, 'available_stock' => 20],
            ['nama_alat' => 'Gelas Kimia 100ml',    'satuan' => 'Pcs',  'total_stock' => 50, 'available_stock' => 50],
            ['nama_alat' => 'Solder Iron',          'satuan' => 'Unit', 'total_stock' => 15, 'available_stock' => 15],
        ];

        foreach ($equipment as $e) {
            Alat::create($e);
        }

        // ─── Test Types ──────────────────────────────────────────────────
        $testTypes = [
            ['nama_pengujian' => 'Uji Kuat Tekan Beton'],
            ['nama_pengujian' => 'Uji Spektrofotometri'],
            ['nama_pengujian' => 'Uji Kalibrasi Sensor'],
        ];

        foreach ($testTypes as $t) {
            JenisPengujian::create($t);
        }
    }
}
