<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message'                  => ['required', 'string', 'max:2000'],
            'history'                  => ['nullable', 'array', 'max:20'],
            'history.*.role'           => ['required_with:history', 'in:user,assistant'],
            'history.*.content'        => ['required_with:history', 'string', 'max:4000'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.max'      => 'Pesan terlalu panjang (maksimal 2000 karakter).',
            'history.max'      => 'Riwayat percakapan terlalu panjang.',
        ];
    }
}
