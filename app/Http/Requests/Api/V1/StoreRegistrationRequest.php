<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fighter_id' => ['required', 'integer', 'exists:fighters,id'],
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
