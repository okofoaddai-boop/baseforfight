<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFighterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'weight_class' => ['nullable', 'string', 'max:50'],
            'sport_modules' => ['nullable', 'array'],
            'sport_modules.*' => ['string', 'max:80'],
            'boxing_weight_entries' => ['nullable', 'array'],
            'boxing_weight_entries.*.date' => ['nullable', 'date'],
            'boxing_weight_entries.*.weight_kg' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'boxing_bout_count_entries' => ['nullable', 'array'],
            'boxing_bout_count_entries.*.date' => ['nullable', 'date'],
            'boxing_bout_count_entries.*.wins' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'boxing_bout_count_entries.*.losses' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'boxing_bout_count_entries.*.draws' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'boxing_pass_entries' => ['nullable', 'array'],
            'boxing_pass_entries.*.keyword' => ['nullable', 'string', 'max:120'],
            'boxing_pass_entries.*.date' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:active,inactive,suspended'],
        ];
    }
}
