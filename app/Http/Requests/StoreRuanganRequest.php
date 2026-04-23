<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRuanganRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('id'); // null saat store, ada nilainya saat update

        return [
            'nama_ruangan' => ['required', 'string', 'max:100', "unique:ruangans,nama_ruangan,{$id}"],
            'deskripsi'    => ['nullable', 'string'],
            'kapasitas'    => ['required', 'integer', 'min:1'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_ruangan.required' => 'Nama ruangan wajib diisi.',
            'nama_ruangan.unique'   => 'Nama ruangan sudah terdaftar.',
            'kapasitas.required'    => 'Kapasitas wajib diisi.',
            'kapasitas.min'         => 'Kapasitas minimal 1 orang.',
        ];
    }
}
