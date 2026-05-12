<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'question' => ['nullable', 'string', 'max:1000'],
            'type' => ['nullable', 'string', Rule::in(array_keys(\App\Support\PollType::labels()))],
            'status' => ['required', Rule::in(['active', 'archived'])],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:opens_at'],
            'is_public' => ['nullable', 'boolean'],
            'questions_json' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'closes_at.after_or_equal' => '"Sluit op" moet gelijk of later zijn dan "Open vanaf".',
            'opens_at.date' => '"Open vanaf" heeft geen geldige datum/tijd.',
            'closes_at.date' => '"Sluit op" heeft geen geldige datum/tijd.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'titel',
            'question' => 'beschrijving',
            'type' => 'type poll',
            'status' => 'status',
            'opens_at' => 'open vanaf',
            'closes_at' => 'sluit op',
        ];
    }
}
