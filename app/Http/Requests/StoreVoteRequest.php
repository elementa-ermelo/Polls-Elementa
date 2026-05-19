<?php

namespace App\Http\Requests;

use App\Support\PollType;
use Illuminate\Foundation\Http\FormRequest;

class StoreVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $poll = $this->route('poll');
        
        $rules = [
            'respondent_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^(06|\+31|0031)[0-9\s\-]{8,}$/'],
            'age' => ['nullable', 'integer', 'min:16', 'max:120'],
        ];
        
        // All polls use multi-question validation
        foreach ($poll->questions as $question) {
            if (PollType::isOpenTextType((string) $question->type)) {
                $rules["question_{$question->id}"] = ['required', 'string', 'max:5000'];
            } else {
                $rules["question_{$question->id}"] = ['required', 'integer', 'exists:poll_options,id'];
            }
        }
        
        return $rules;
    }

    public function messages(): array
    {
        return [
            'age.min' => 'Je moet minimaal 16 jaar zijn om deze poll in te vullen.',
            'open_answer.required' => 'Vul een antwoord in bij deze open poll.',
            'phone.regex' => 'Voer een geldig Nederlands telefoonnummer in (06, +31 of 0031).',
            '*.required' => 'Dit antwoord is verplicht.',
            '*.exists' => 'Deze optie bestaat niet.',
        ];
    }
}
