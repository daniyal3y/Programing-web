<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:120'],
            // Canonical E.164 representation avoids duplicate variants of one number.
            'phone' => ['required', 'string', 'regex:/^\+[1-9][0-9]{7,14}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'شماره همراه را با قالب بین‌المللی وارد کنید؛ نمونه: +989121234567',
        ];
    }
}
