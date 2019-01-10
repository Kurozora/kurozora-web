#!/usr/bin/env bash

# Starts Laravel queue background process
(trap "" SIGINT; exec -a KurozoraQueue php artisan queue:work &)