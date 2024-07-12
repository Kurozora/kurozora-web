FROM php:8.3-fpm-alpine AS app

# Set labels
LABEL app.kurozora.authors="Kiritokatklian"
LABEL app.kurozora.maintainer="Kiritokatklian"
LABEL app.kurozora.version="1.0"

# Set arguments
ARG WORKDIR=/var/www/html

# Set environment variables
ENV DOCUMENT_ROOT=${WORKDIR}
ENV TZ=UTC

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Update packages
RUN apk update && apk upgrade --no-cache

# Install Git
RUN apk add git nginx supervisor --no-cache

# Install PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions exif \
    bcmath \
    gd imagick/imagick@master \
    intl \
    opcache \
    pdo_mysql \
    zip

# Set working directory
WORKDIR $WORKDIR

# Copy project files
COPY --chown=www-data . /var/www/html

# Copy configs
COPY ./docker/php/conf.d/php.ini /usr/local/etc/php/conf.d/php.ini
COPY ./docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Install Composer and dependencies
ENV COMPOSER_HOME=/composer
ENV PATH=./vendor/bin:/composer/vendor/bin:$PATH
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer && \
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-cache && \
    rm $WORKDIR/composer.lock && \
    rm $WORKDIR/package-lock.json && \
    rm /usr/local/bin/composer

# Delete packages and cleanup cache
RUN apk del git && \
    apk cache clean

# Copy entrypoint script
COPY ./docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    ln -s /usr/local/bin/entrypoint.sh /

ENTRYPOINT [ "entrypoint.sh" ]

# Expose port
EXPOSE 80

# Execute entrypoint
CMD [ "entrypoint" ]
