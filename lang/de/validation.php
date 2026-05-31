<?php

return [
    'required' => 'Das Feld :attribute ist erforderlich.',
    'email' => 'Bitte gib eine gültige E-Mail-Adresse ein.',
    'string' => 'Das Feld :attribute muss ein Text sein.',
    'integer' => 'Das Feld :attribute muss eine Zahl sein.',
    'boolean' => 'Das Feld :attribute muss wahr oder falsch sein.',
    'confirmed' => 'Die Bestätigung für :attribute stimmt nicht überein.',
    'exists' => 'Die ausgewählte Auswahl für :attribute ist ungültig.',
    'unique' => 'Der Wert für :attribute ist bereits vergeben.',
    'date' => 'Das Feld :attribute muss ein gültiges Datum sein.',
    'file' => 'Das Feld :attribute muss eine Datei sein.',
    'mimetypes' => 'Das Feld :attribute muss eine Datei vom Typ :values sein.',
    'min' => [
        'string' => 'Das Feld :attribute muss mindestens :min Zeichen haben.',
    ],
    'max' => [
        'string' => 'Das Feld :attribute darf hoechstens :max Zeichen haben.',
        'file' => 'Das Feld :attribute darf nicht groesser als :max Kilobyte sein.',
    ],
    'attributes' => [
        'club_id' => 'Verein',
        'club_name' => 'Vereinsname',
        'confirm_new' => 'Bestätigung',
        'email' => 'E-Mail-Adresse',
        'event_pdf' => 'PDF-Datei',
        'first_name' => 'Vorname',
        'last_name' => 'Nachname',
        'locale' => 'Sprache',
        'password' => 'Passwort',
    ],
];