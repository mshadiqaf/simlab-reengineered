<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJenisPengujianRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'nama_pengujian' => ['required', 'string', 'max:100', "unique:jenis_pengujians,nama_pengujian,{$id}"],
            'deskripsi'      => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_pengujian.required' => 'Nama jenis pengujian wajib diisi.',
            'nama_pengujian.unique'   => 'Jenis pengujian sudah terdaftar.',
        ];
    }
}
