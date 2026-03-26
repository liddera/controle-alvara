<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlertConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Middleware auth handles this
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tipo_alvara_id' => ['nullable', 'exists:tipo_alvaras,id'],
            'days_before' => ['required', 'integer', 'min:0', 'max:365'],
            'recipient_emails' => ['nullable', 'array', 'max:10'],
            'recipient_emails.*' => ['required', 'string', 'email', 'max:255', 'distinct'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $recipientEmails = collect($this->input('recipient_emails', []))
            ->filter(fn ($email) => filled($email))
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->unique()
            ->values()
            ->all();

        $this->merge([
            'recipient_emails' => $recipientEmails,
        ]);
    }
}
