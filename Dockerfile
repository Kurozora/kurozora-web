### Build stage
FROM php:8.4-fpm-alpine AS build

# Set labels
LABEL app.kurozora.authors="Kiritokatklian"
LABEL app.kurozora.maintainer="Kiritokatklian"
LABEL app.kurozora.version="1.0"

ARG WORKDIR=/var/www/html

# Set environment variables
ENV DOCUMENT_ROOT=${WORKDIR}
ENV TZ=UTC
ENV COMPOSER_HOME=/composer
ENV PATH=./vendor/bin:/composer/vendor/bin:$PATH
ENV COMPOSER_ALLOW_SUPERUSER=1

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install system dependencies for building
RUN apk add --no-cache git curl unzip bash

# Install PHP extensions
RUN curl -fsSL https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions -o /usr/local/bin/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions exif \
    bcmath \
    gd imagick/imagick@master \
    intl \
    opcache \
    pdo_mysql \
    zip

# Set working directory before copying
WORKDIR $WORKDIR

# Copy the rest of the project files
COPY --chown=www-data . .

# Install Composer
RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer && \
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-cache

###

### Final runtime stage
FROM php:8.4-fpm-alpine AS app

ARG WORKDIR=/var/www/html

# Set environment variables
ENV DOCUMENT_ROOT=${WORKDIR}
ENV TZ=UTC
ENV PATH=./vendor/bin:/composer/vendor/bin:$PATH

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install runtime dependencies only
RUN apk add --no-cache nginx supervisor

# Install PHP extensions
RUN curl -fsSL https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions -o /usr/local/bin/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions exif \
    bcmath \
    gd imagick/imagick@master \
    intl \
    opcache \
    pdo_mysql \
    zip

# Set working directory
WORKDIR $WORKDIR

# Copy built app from previous stage
COPY --from=build --chown=www-data /var/www/html /var/www/html

# Copy configs
COPY ./docker/php/conf.d/php.ini /usr/local/etc/php/conf.d/php.ini
COPY ./docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf

RUN rm $WORKDIR/composer.lock && \
    rm $WORKDIR/package-lock.json

# Copy entrypoint script
COPY ./docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    ln -s /usr/local/bin/entrypoint.sh /

ENTRYPOINT [ "entrypoint.sh" ]

# Expose port
EXPOSE 80

# Execute entrypoint
CMD [ "entrypoint" ]
