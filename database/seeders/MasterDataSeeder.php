<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Ruangan;
use App\Models\Alat;
use App\Models\JenisPengujian;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Data Ruangan (Sesuai Screenshot User)
        $ruangans = [
            ['nama_ruangan' => 'Workshop B - Tuning & Assembly (1.5)', 'kapasitas' => 30],
            ['nama_ruangan' => 'Labter 1 - Lab. Fisika Dasar A (R202)', 'kapasitas' => 40],
            ['nama_ruangan' => 'Labter 1 - Bengkel B - Perakitan (R102)', 'kapasitas' => 20],
            ['nama_ruangan' => 'Labter 1 - Lab. Kimia Dasar B (R208)', 'kapasitas' => 40],
            ['nama_ruangan' => 'Labter 1 - Lab. Sistem Tenaga Listrik dan Otomasi (R106)', 'kapasitas' => 25],
        ];

        foreach ($ruangans as $r) {
            Ruangan::create($r);
        }

        // Data Alat
        $alats = [
            ['nama_alat' => 'Osiloskop Digital', 'satuan' => 'Unit', 'total_stok' => 10],
            ['nama_alat' => 'Mikroskop Binokuler', 'satuan' => 'Unit', 'total_stok' => 15],
            ['nama_alat' => 'Multimeter Digital', 'satuan' => 'Unit', 'total_stok' => 20],
            ['nama_alat' => 'Gelas Kimia 100ml', 'satuan' => 'Pcs', 'total_stok' => 50],
            ['nama_alat' => 'Solder Iron', 'satuan' => 'Unit', 'total_stok' => 15],
        ];

        foreach ($alats as $a) {
            Alat::create($a);
        }

        // Data Jenis Pengujian
        $ujis = [
            ['nama_pengujian' => 'Uji Kuat Tekan Beton'],
            ['nama_pengujian' => 'Uji Spektrofotometri'],
            ['nama_pengujian' => 'Uji Kalibrasi Sensor'],
        ];

        foreach ($ujis as $u) {
            JenisPengujian::create($u);
        }
    }
}
