<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifikasiPengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Sudah diproteksi middleware role:Kepala Laboratorium di Route
    }

    public function rules(): array
    {
        return [
            // diverifikasi = disetujui Kepala Lab, ditolak = ditolak Kepala Lab
            'status'            => ['required', 'in:diverifikasi,ditolak'],
            'catatan_reviewer'  => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status keputusan wajib diisi.',
            'status.in'       => 'Status hanya boleh: diverifikasi atau ditolak.',
        ];
    }
}
