<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailPengajuanRuangan;
use App\Models\DetailPengajuanAlat;
use App\Models\DetailPengajuanUji;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class KetersediaanController extends Controller
{
    #[OA\Get(
        path: '/api/ketersediaan',
        summary: 'Kalender ketersediaan ruangan, alat, dan pengujian per bulan',
        description: 'Mengembalikan data peminjaman/pengajuan yang distrukturisasi per tanggal dalam satu bulan. Cocok untuk tampilan kalender di frontend. Dapat difilter berdasarkan tipe dan kata kunci nama. Endpoint ini PUBLIK — tidak memerlukan login.',
        tags: ['Master Data'],
        parameters: [
            new OA\Parameter(
                name: 'bulan',
                in: 'query',
                required: true,
                description: 'Bulan yang ingin dilihat, format YYYY-MM',
                schema: new OA\Schema(type: 'string', example: '2026-05')
            ),
            new OA\Parameter(
                name: 'tipe',
                in: 'query',
                required: false,
                description: 'Filter berdasarkan tipe: ruangan, alat, atau pengujian',
                schema: new OA\Schema(type: 'string', enum: ['ruangan', 'alat', 'pengujian'])
            ),
            new OA\Parameter(
                name: 'cari',
                in: 'query',
                required: false,
                description: 'Kata kunci pencarian nama ruangan/alat/jenis pengujian',
                schema: new OA\Schema(type: 'string', example: 'Fisika')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data kalender ketersediaan berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'bulan', type: 'string', example: '2026-05'),
                        new OA\Property(property: 'filter', type: 'object', properties: [
                            new OA\Property(property: 'tipe', type: 'string', nullable: true, example: 'ruangan'),
                            new OA\Property(property: 'cari', type: 'string', nullable: true, example: 'Fisika'),
                        ]),
                        new OA\Property(
                            property: 'kalender',
                            type: 'object',
                            description: 'Key = tanggal (YYYY-MM-DD), value = array booking pada hari tersebut',
                            example: [
                                '2026-05-01' => [
                                    ['tipe' => 'ruangan', 'nama' => 'Lab. Fisika Dasar A (R202)', 'waktu_mulai' => '08:00', 'waktu_selesai' => '12:00', 'status' => 'disetujui']
                                ],
                                '2026-05-03' => [
                                    ['tipe' => 'alat', 'nama' => 'Osiloskop Digital', 'jumlah' => 3, 'status' => 'diverifikasi']
                                ],
                                '2026-05-07' => [],
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Format bulan tidak valid'),
        ]
    )]
    public function kalender(Request $request)
    {
        $request->validate([
            'bulan' => ['required', 'date_format:Y-m'],
            'tipe'  => ['nullable', 'in:ruangan,alat,pengujian'],
            'cari'  => ['nullable', 'string', 'max:100'],
        ]);

        $tipe  = $request->tipe;
        $cari  = $request->cari;
        $bulan = $request->bulan;

        $awalBulan  = Carbon::parse($bulan . '-01')->startOfMonth();
        $akhirBulan = Carbon::parse($bulan . '-01')->endOfMonth();

        // Inisialisasi semua hari dalam bulan sebagai array kosong
        $kalender = [];
        foreach (CarbonPeriod::create($awalBulan, $akhirBulan) as $hari) {
            $kalender[$hari->format('Y-m-d')] = [];
        }

        $statusMap = [
            'verified'  => 'diverifikasi',
            'approved'  => 'disetujui',
            'completed' => 'selesai',
        ];

        // ── 1. Booking Ruangan ──────────────────────────────────────────────
        if (!$tipe || $tipe === 'ruangan') {
            $bookings = DetailPengajuanRuangan::with(['submission', 'room'])
                ->whereHas('submission', fn($q) =>
                    $q->whereIn('status', ['verified', 'approved', 'completed'])
                )
                ->where('start_date', '<=', $akhirBulan->format('Y-m-d'))
                ->where('end_date', '>=', $awalBulan->format('Y-m-d'))
                ->when($cari, fn($q) =>
                    $q->whereHas('room', fn($r) =>
                        $r->where('nama_ruangan', 'like', "%{$cari}%")
                    )
                )
                ->get();

            foreach ($bookings as $b) {
                // Hitung irisan antara rentang booking dan rentang bulan
                $mulai   = max($b->start_date, $awalBulan->format('Y-m-d'));
                $selesai = min($b->end_date, $akhirBulan->format('Y-m-d'));

                foreach (CarbonPeriod::create($mulai, $selesai) as $hari) {
                    $key = $hari->format('Y-m-d');
                    if (isset($kalender[$key])) {
                        $kalender[$key][] = [
                            'tipe'          => 'ruangan',
                            'nama'          => $b->room->nama_ruangan ?? '-',
                            'waktu_mulai'   => $b->start_time ? substr($b->start_time, 0, 5) : null,
                            'waktu_selesai' => $b->end_time ? substr($b->end_time, 0, 5) : null,
                            'status'        => $statusMap[$b->submission->status] ?? $b->submission->status,
                        ];
                    }
                }
            }
        }

        // ── 2. Booking Alat ─────────────────────────────────────────────────
        if (!$tipe || $tipe === 'alat') {
            $bookings = DetailPengajuanAlat::with(['submission', 'equipment'])
                ->whereHas('submission', fn($q) =>
                    $q->whereIn('status', ['verified', 'approved', 'completed'])
                )
                ->where('start_date', '<=', $akhirBulan->format('Y-m-d'))
                ->where('end_date', '>=', $awalBulan->format('Y-m-d'))
                ->when($cari, fn($q) =>
                    $q->whereHas('equipment', fn($r) =>
                        $r->where('nama_alat', 'like', "%{$cari}%")
                    )
                )
                ->get();

            foreach ($bookings as $b) {
                $mulai   = max($b->start_date, $awalBulan->format('Y-m-d'));
                $selesai = min($b->end_date, $akhirBulan->format('Y-m-d'));

                foreach (CarbonPeriod::create($mulai, $selesai) as $hari) {
                    $key = $hari->format('Y-m-d');
                    if (isset($kalender[$key])) {
                        $kalender[$key][] = [
                            'tipe'           => 'alat',
                            'nama'           => $b->equipment->nama_alat ?? '-',
                            'jumlah_dipinjam'=> $b->quantity_borrowed,
                            'status'         => $statusMap[$b->submission->status] ?? $b->submission->status,
                        ];
                    }
                }
            }
        }

        // ── 3. Booking Pengujian ────────────────────────────────────────────
        // Tabel detail_pengujian tidak memiliki kolom tanggal tersendiri,
        // sehingga tanggal pengajuan (created_at) digunakan sebagai acuan.
        if (!$tipe || $tipe === 'pengujian') {
            $bookings = DetailPengajuanUji::with(['submission', 'testType'])
                ->whereHas('submission', fn($q) =>
                    $q->whereIn('status', ['verified', 'approved', 'completed'])
                      ->whereBetween('created_at', [$awalBulan, $akhirBulan])
                )
                ->when($cari, fn($q) =>
                    $q->whereHas('testType', fn($r) =>
                        $r->where('nama_pengujian', 'like', "%{$cari}%")
                    )
                )
                ->get();

            foreach ($bookings as $b) {
                $key = Carbon::parse($b->submission->created_at)->format('Y-m-d');
                if (isset($kalender[$key])) {
                    $kalender[$key][] = [
                        'tipe'          => 'pengujian',
                        'nama'          => $b->testType->nama_pengujian ?? '-',
                        'nama_sampel'   => $b->sample_name,
                        'jumlah_sampel' => $b->sample_count,
                        'status'        => $statusMap[$b->submission->status] ?? $b->submission->status,
                    ];
                }
            }
        }

        return response()->json([
            'data' => [
                'bulan'    => $bulan,
                'filter'   => [
                    'tipe' => $tipe,
                    'cari' => $cari,
                ],
                'kalender' => $kalender,
            ],
        ]);
    }
}
