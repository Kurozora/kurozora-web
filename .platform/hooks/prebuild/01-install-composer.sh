#!/bin/bash

# Get Composer, and install to /usr/local/bin
if [ ! -f "/usr/local/bin/composer" ]; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
else
    echo "Composer already installed, updating..."
    /usr/bin/composer.phar self-update --stable --no-ansi --no-interaction
fi
