<?php

return [
    'required' => 'Le champ :attribute est obligatoire.',
    'email' => 'Veuillez saisir une adresse e-mail valide.',
    'string' => 'Le champ :attribute doit etre une chaine de caracteres.',
    'integer' => 'Le champ :attribute doit etre un entier.',
    'boolean' => 'Le champ :attribute doit etre vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'exists' => 'La valeur selectionnee pour :attribute est invalide.',
    'unique' => 'La valeur du champ :attribute est deja utilisee.',
    'date' => 'Le champ :attribute doit etre une date valide.',
    'file' => 'Le champ :attribute doit etre un fichier.',
    'mimetypes' => 'Le champ :attribute doit etre un fichier du type :values.',
    'min' => [
        'string' => 'Le champ :attribute doit contenir au moins :min caracteres.',
    ],
    'max' => [
        'string' => 'Le champ :attribute ne peut pas depasser :max caracteres.',
        'file' => 'Le champ :attribute ne peut pas depasser :max kilo-octets.',
    ],
    'attributes' => [
        'club_id' => 'club',
        'club_name' => 'nom du club',
        'confirm_new' => 'confirmation',
        'email' => 'adresse e-mail',
        'event_pdf' => 'fichier PDF',
        'first_name' => 'prenom',
        'last_name' => 'nom',
        'locale' => 'langue',
        'password' => 'mot de passe',
    ],
];