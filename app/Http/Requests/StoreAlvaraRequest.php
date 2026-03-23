<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlvaraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empresa_id' => ['required', 'exists:empresas,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'tipo' => ['required', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:255'],
            'data_emissao' => ['nullable', 'date'],
            'data_vencimento' => ['required', 'date'],
            'observacoes' => ['nullable', 'string'],
        ];
    }
}
