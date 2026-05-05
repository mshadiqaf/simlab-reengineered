<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // ── Field Induk (wajib untuk semua tipe) ──────────────────────────
            'tipe_pengajuan'   => ['required', 'in:ruangan,alat,pengujian'],
            'nomor_hp'         => ['required', 'string', 'max:20'],
            'judul_proyek'     => ['nullable', 'string', 'max:255'],
            'tujuan_penggunaan'=> ['required', 'string'],
            'dosen_pembimbing' => ['nullable', 'string', 'max:255'],
            'email_dosen'      => ['nullable', 'email', 'max:255'],
            'surat_pengantar'  => ['nullable', 'file', 'mimes:pdf', 'max:5120'],

            // ── Detail Ruangan (wajib jika tipe = ruangan) ────────────────────
            'ruangan_id'           => ['required_if:tipe_pengajuan,ruangan', 'exists:rooms,id'],
            'tanggal_mulai'        => ['required_if:tipe_pengajuan,ruangan', 'required_if:tipe_pengajuan,alat', 'nullable', 'date'],
            'tanggal_selesai'      => ['required_if:tipe_pengajuan,ruangan', 'required_if:tipe_pengajuan,alat', 'nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'waktu_mulai'          => ['nullable', 'date_format:H:i'],
            'waktu_selesai'        => ['nullable', 'date_format:H:i', 'after:waktu_mulai'],
            'jumlah_pengguna'      => ['required_if:tipe_pengajuan,ruangan', 'integer', 'min:1'],
            'nama_pengguna_lainnya'=> ['nullable', 'string'],
            'catatan_alat_bahan'   => ['nullable', 'string'],

            // ── Detail Alat (wajib jika tipe = alat) ─────────────────────────
            'alat_id'              => ['required_if:tipe_pengajuan,alat', 'exists:equipment,id'],
            'jumlah_dipinjam'      => ['required_if:tipe_pengajuan,alat', 'integer', 'min:1'],
            'keperluan_spesifik'   => ['nullable', 'string'],
            'durasi_jam'           => ['nullable', 'integer', 'min:1'],

            // ── Detail Pengujian (wajib jika tipe = pengujian) ────────────────
            'jenis_pengujian_id'  => ['required_if:tipe_pengajuan,pengujian', 'exists:test_types,id'],
            'nama_sampel'         => ['required_if:tipe_pengajuan,pengujian', 'string', 'max:255'],
            'jumlah_sampel'       => ['required_if:tipe_pengajuan,pengujian', 'integer', 'min:1'],
            'keterangan_tambahan' => ['nullable', 'string'],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tipe_pengajuan.required' => 'Tipe pengajuan wajib dipilih.',
            'tipe_pengajuan.in'       => 'Tipe pengajuan harus salah satu dari: ruangan, alat, pengujian.',
            'nomor_hp.required'       => 'Nomor HP wajib diisi.',
            'tujuan_penggunaan.required' => 'Tujuan penggunaan wajib diisi.',
            'surat_pengantar.mimes'   => 'Surat pengantar harus berformat PDF.',
            'surat_pengantar.max'     => 'Ukuran surat pengantar tidak boleh lebih dari 5MB.',
        ];
    }
}
