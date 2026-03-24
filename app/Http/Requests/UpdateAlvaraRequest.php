<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlvaraRequest extends FormRequest
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
            'tipo_alvara_id' => ['required', 'exists:tipo_alvaras,id'],
            'tipo' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:255'],
            'data_emissao' => ['nullable', 'date'],
            'data_vencimento' => ['required', 'date'],
            'status' => ['required', 'string', 'in:vigente,proximo,vencido'],
            'observacoes' => ['nullable', 'string'],
        ];
    }
}
