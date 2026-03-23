<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmpresaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cnpj' => ['required', 'string', 'max:18', 'unique:empresas,cnpj,' . $this->empresa->id],
            'responsavel' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}
