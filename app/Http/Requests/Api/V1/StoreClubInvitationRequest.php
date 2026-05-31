<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreClubInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:club_manager,event_manager,trainer'],
            'expires_in_days' => ['sometimes', 'integer', 'min:1', 'max:60'],
        ];
    }
}
