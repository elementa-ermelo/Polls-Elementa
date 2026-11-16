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
        
        $rules = $poll->anonymous ? [] : [
            'respondent_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'age' => ['nullable', 'integer', 'min:16', 'max:120'],
        ];
        
        // All polls use multi-question validation
        foreach ($poll->questions as $question) {
            if (PollType::isOpenTextType((string) $question->type)) {
                $rules["question_{$question->id}"] = ['nullable', 'string', 'max:5000'];
            } else {
                // De gekozen optie moet bij DEZE vraag horen, niet bij een willekeurige andere.
                $rules["toelichting_{$question->id}"] = ['nullable', 'string', 'max:2000'];
                $rules["question_{$question->id}"] = [
                    'required', 'integer',
                    \Illuminate\Validation\Rule::exists('poll_options', 'id')->where('poll_question_id', $question->id),
                ];
            }
        }
        
        return $rules;
    }

    public function messages(): array
    {
        return [
            'open_answer.required' => 'Vul een antwoord in bij deze open poll.',
            '*.required' => 'Dit antwoord is verplicht.',
            '*.exists' => 'Deze optie bestaat niet.',
        ];
    }
}
