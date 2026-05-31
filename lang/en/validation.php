<?php

return [
    'required' => 'The :attribute field is required.',
    'email' => 'Please enter a valid email address.',
    'string' => 'The :attribute field must be a string.',
    'integer' => 'The :attribute field must be an integer.',
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'exists' => 'The selected :attribute is invalid.',
    'unique' => 'The :attribute has already been taken.',
    'date' => 'The :attribute is not a valid date.',
    'file' => 'The :attribute must be a file.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'max' => [
        'string' => 'The :attribute may not be greater than :max characters.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
    ],
    'attributes' => [
        'club_id' => 'club',
        'club_name' => 'club name',
        'confirm_new' => 'confirmation',
        'email' => 'email address',
        'event_pdf' => 'PDF file',
        'first_name' => 'first name',
        'last_name' => 'last name',
        'locale' => 'language',
        'password' => 'password',
    ],
];