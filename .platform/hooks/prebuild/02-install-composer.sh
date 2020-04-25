#!/bin/bash

# Get Composer, and install to /usr/local/bin
if [ ! -f "/usr/bin/composer" ]; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/bin --filename=composer
    php -r "unlink('composer-setup.php');"
else
    /usr/bin/composer self-update --stable --no-ansi --no-interaction
fi
