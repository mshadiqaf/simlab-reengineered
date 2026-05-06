<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlatRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'nama_alat'    => ['required', 'string', 'max:100', "unique:alats,nama_alat,{$id}"],
            'deskripsi'    => ['nullable', 'string'],
            'satuan'       => ['required', 'string', 'max:30'],
            'total_stok'   => ['required', 'integer', 'min:1'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_alat.required'  => 'Nama alat wajib diisi.',
            'nama_alat.unique'    => 'Nama alat sudah terdaftar.',
            'satuan.required'     => 'Satuan wajib diisi (contoh: unit, buah).',
            'total_stok.required' => 'Total stok wajib diisi.',
            'total_stok.min'      => 'Total stok minimal 1.',
        ];
    }
}
