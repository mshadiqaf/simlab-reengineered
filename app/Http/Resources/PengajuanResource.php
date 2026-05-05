<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengajuanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $tipeMap = ['room' => 'ruangan', 'equipment' => 'alat', 'testing' => 'pengujian'];
        $statusMap = [
            'submitted' => 'diajukan',
            'verified'  => 'diverifikasi',
            'rejected'  => 'ditolak',
            'approved'  => 'disetujui',
            'completed' => 'selesai',
        ];

        return [
            'id'                => $this->id,
            'tipe_pengajuan'    => $tipeMap[$this->submission_type] ?? $this->submission_type,
            'status'            => $statusMap[$this->status] ?? $this->status,
            'nomor_hp'          => $this->phone_number,
            'judul_proyek'      => $this->project_title,
            'tujuan_penggunaan' => $this->purpose,
            'dosen_pembimbing'  => $this->supervisor_name,
            'email_dosen'       => $this->supervisor_email,
            'catatan_reviewer'  => $this->reviewer_notes,
            'dibuat_pada'       => $this->created_at->format('d M Y, H:i'),

            // Info pengaju — hanya muncul jika relasi user di-load (untuk tampilan Kepala Lab)
            'pengaju' => $this->whenLoaded('user', fn() => [
                'id'            => $this->user->id,
                'nama'          => $this->user->name,
                'nim'           => $this->user->nim,
                'program_studi' => $this->user->program_studi,
                'email'         => $this->user->email,
            ]),

            // Detail berbeda sesuai tipe — hanya yang relevan yang akan terisi
            'detail_ruangan' => $this->whenLoaded('detailRuangan', fn() => $this->detailRuangan ? [
                'ruangan'            => $this->detailRuangan->room?->nama_ruangan,
                'kapasitas'          => $this->detailRuangan->room?->kapasitas,
                'tanggal_mulai'      => $this->detailRuangan->start_date,
                'tanggal_selesai'    => $this->detailRuangan->end_date,
                'waktu_mulai'        => $this->detailRuangan->start_time,
                'waktu_selesai'      => $this->detailRuangan->end_time,
                'jumlah_pengguna'    => $this->detailRuangan->participant_count,
                'catatan_alat_bahan' => $this->detailRuangan->equipment_notes,
            ] : null),

            'detail_alat' => $this->whenLoaded('detailAlat', fn() => $this->detailAlat ? [
                'nama_alat'          => $this->detailAlat->equipment?->nama_alat,
                'jumlah_dipinjam'    => $this->detailAlat->quantity_borrowed,
                'tanggal_mulai'      => $this->detailAlat->start_date,
                'tanggal_selesai'    => $this->detailAlat->end_date,
                'keperluan_spesifik' => $this->detailAlat->specific_purpose,
            ] : null),

            'detail_pengujian' => $this->whenLoaded('detailUji', fn() => $this->detailUji ? [
                'jenis_pengujian'     => $this->detailUji->testType?->nama_pengujian,
                'nama_sampel'         => $this->detailUji->sample_name,
                'jumlah_sampel'       => $this->detailUji->sample_count,
                'keterangan_tambahan' => $this->detailUji->additional_notes,
            ] : null),
        ];
    }
}
