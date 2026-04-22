<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Sudah diproteksi middleware auth di Route
    }

    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'required', 'string', 'max:255'],
            'nim'           => ['sometimes', 'nullable', 'string', 'max:50'],
            'program_studi' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
