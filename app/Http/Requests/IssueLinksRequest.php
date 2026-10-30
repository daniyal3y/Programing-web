<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IssueLinksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // The admin.token middleware protects this endpoint.
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
