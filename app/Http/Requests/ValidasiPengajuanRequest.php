<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidasiPengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Sudah diproteksi middleware role:Petugas Laboran di Route
    }

    public function rules(): array
    {
        return [
            // disetujui = Laboran konfirmasi stok/ruangan tersedia, ditolak = stok habis/konflik jadwal
            'status'           => ['required', 'in:disetujui,ditolak'],
            'catatan_reviewer' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status keputusan wajib diisi.',
            'status.in'       => 'Status hanya boleh: disetujui atau ditolak.',
        ];
    }
}
