<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'registration_deadline' => ['nullable', 'date', 'before_or_equal:starts_at'],
            'max_registrations' => ['nullable', 'integer', 'min:1'],
            'allow_waitlist' => ['nullable', 'boolean'],
            'entry_fee_cents' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'info_documents' => ['nullable', 'array'],
            'info_documents.*' => ['nullable', 'url'],
            'location' => ['nullable', 'string', 'max:255'],
            'sport_module' => ['nullable', 'string', 'max:80'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'size:2'],
            'boxing_package_key' => ['nullable', 'string', 'max:80'],
            'boxing_age_classes' => ['nullable', 'array'],
            'boxing_age_classes.*' => ['nullable', 'string', 'max:80'],
            'boxing_sexes' => ['nullable', 'array'],
            'boxing_sexes.*' => ['nullable', 'in:m,w'],
            'boxing_performance_classes' => ['nullable', 'array'],
            'boxing_performance_classes.*' => ['nullable', 'string', 'max:80'],
            'status' => ['sometimes', 'in:draft,published,cancelled'],
            'organizer_club_id' => ['required', 'integer', 'exists:clubs,id'],
        ];
    }
}
