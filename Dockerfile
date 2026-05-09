### Asset build stage
FROM node:22-alpine AS assets

ARG WORKDIR=/var/www/html

WORKDIR $WORKDIR

# Install Node dependencies first to leverage layer caching
COPY package.json package-lock.json ./
RUN npm ci

# Copy the source files Vite needs to build (resources, configs, public assets)
COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.cjs tailwind.config.cjs ./

RUN npm run build

###

### Shared PHP base stage with system deps and PHP extensions installed once
FROM php:8.4-fpm-alpine AS php-base

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

# Install system dependencies
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

###

### PHP build stage
FROM php-base AS build

ARG WORKDIR=/var/www/html

WORKDIR $WORKDIR

# Install Composer
RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

# Install vendor dependencies
COPY composer.json composer.lock ./
COPY nova ./nova
RUN composer install --no-interaction --prefer-dist --no-dev --no-cache --no-scripts --no-autoloader

# Copy the rest of the project files
COPY --chown=www-data . .

# Copy built front-end assets from the asset stage
COPY --from=assets --chown=www-data ${WORKDIR}/public/build ./public/build
COPY --from=assets --chown=www-data ${WORKDIR}/public/offline.html ./public/offline.html
COPY --from=assets --chown=www-data ${WORKDIR}/public/service-worker.js ./public/service-worker.js
COPY --from=assets --chown=www-data ${WORKDIR}/public/service-worker.js.map ./public/service-worker.js.map

# Run post-install scripts and dump optimized autoloader now that the full source is present
RUN composer install --no-interaction --prefer-dist --no-dev --no-cache --optimize-autoloader

###

### Final runtime stage
FROM php-base AS app

ARG WORKDIR=/var/www/html

WORKDIR $WORKDIR

# Install runtime-only dependencies
RUN apk add --no-cache nginx supervisor gettext

# Copy built app from previous stage
COPY --from=build --chown=www-data /var/www/html /var/www/html

# Copy configs
COPY ./docker/php/conf.d/php.ini /usr/local/etc/php/conf.d/php.ini
COPY ./docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY ./docker/supervisor/supervisord.d/laravel.conf /etc/supervisor/supervisord.d/laravel.conf
COPY ./docker/nginx/nginx.conf.template /etc/nginx/nginx.conf.template

# Stream container logs to stdout/stderr for Docker logs and CloudWatch
RUN mkdir -p /var/log/nginx /var/log && \
    ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stderr /var/log/nginx/error.log && \
    ln -sf /dev/stdout /var/log/laravel_scheduler.out.log && \
    ln -sf /dev/stdout /var/log/laravel_worker.out.log && \
    ln -sf /dev/stdout /var/log/php-fpm_consumer.out.log && \
    ln -sf /dev/stderr /var/log/php-fpm_consumer.err.log && \
    ln -sf /dev/stdout /var/log/supervisord.log

# Copy entrypoint script
COPY ./docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    ln -s /usr/local/bin/entrypoint.sh /

ENTRYPOINT [ "entrypoint.sh" ]

# Expose port
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD wget -qO- http://localhost/up || exit 1

# Execute entrypoint
CMD [ "entrypoint" ]
