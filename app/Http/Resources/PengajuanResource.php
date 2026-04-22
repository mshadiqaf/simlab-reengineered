<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengajuanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'tipe_pengajuan'  => $this->tipe_pengajuan,
            'status'          => $this->status,
            'nomor_hp'        => $this->nomor_hp,
            'judul_proyek'    => $this->judul_proyek,
            'tujuan_penggunaan' => $this->tujuan_penggunaan,
            'dosen_pembimbing'  => $this->dosen_pembimbing,
            'email_dosen'       => $this->email_dosen,
            'catatan_reviewer'  => $this->catatan_reviewer,
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
            'detail_ruangan'  => $this->whenLoaded('detailRuangan', fn() => [
                'ruangan'             => $this->detailRuangan?->ruangan?->nama_ruangan,
                'kapasitas'           => $this->detailRuangan?->ruangan?->kapasitas,
                'tanggal_mulai'       => $this->detailRuangan?->tanggal_mulai,
                'tanggal_selesai'     => $this->detailRuangan?->tanggal_selesai,
                'waktu_mulai'         => $this->detailRuangan?->waktu_mulai,
                'waktu_selesai'       => $this->detailRuangan?->waktu_selesai,
                'jumlah_pengguna'     => $this->detailRuangan?->jumlah_pengguna,
                'catatan_alat_bahan'  => $this->detailRuangan?->catatan_alat_bahan,
            ]),
            'detail_alat'     => $this->whenLoaded('detailAlat', fn() => [
                'nama_alat'          => $this->detailAlat?->alat?->nama_alat,
                'jumlah_dipinjam'    => $this->detailAlat?->jumlah_dipinjam,
                'tanggal_mulai'      => $this->detailAlat?->tanggal_mulai,
                'tanggal_selesai'    => $this->detailAlat?->tanggal_selesai,
                'keperluan_spesifik' => $this->detailAlat?->keperluan_spesifik,
            ]),
            'detail_pengujian' => $this->whenLoaded('detailUji', fn() => [
                'jenis_pengujian'    => $this->detailUji?->jenisPengujian?->nama_pengujian,
                'nama_sampel'        => $this->detailUji?->nama_sampel,
                'jumlah_sampel'      => $this->detailUji?->jumlah_sampel,
                'keterangan_tambahan'=> $this->detailUji?->keterangan_tambahan,
            ]),
        ];
    }
}
