<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterAlvaraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'empresa_id' => ['nullable', 'integer', 'exists:empresas,id'],
            'tipo_alvara_id' => ['nullable', 'integer', 'exists:tipo_alvaras,id'],
            'tipo' => ['nullable', 'string', 'exists:tipo_alvaras,slug'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:todos,vigente,proximo,vencido'],
            'vencimento_de' => ['nullable', 'date'],
            'vencimento_ate' => ['nullable', 'date', 'after_or_equal:vencimento_de'],
        ];
    }
}
