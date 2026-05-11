<?php

return [
    'superuser_emails' => array_values(array_filter(array_map(
        static fn (string $email) => strtolower(trim($email)),
        explode(',', (string) env('SUPERUSER_EMAILS', ''))
    ))),
];
