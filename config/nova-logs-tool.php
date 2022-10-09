<?php

return [
    'perPage' => env('NOVA_LOGS_PER_PAGE', 25),
    'regexForFiles' => env('NOVA_LOGS_REGEX_FOR_FILES', '/^laravel|^worker|^queue|^websockets/'),
];
