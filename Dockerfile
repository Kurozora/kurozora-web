FROM php:8.2-fpm-alpine as app

# Set labels
LABEL app.kurozora.authors="Kiritokatklian"
LABEL app.kurozora.maintainer="Kiritokatklian"
LABEL app.kurozora.version="1.0"

# Set arguments
ARG WORKDIR=/var/www/html
ARG HOST_UID=1337

# Set environment variables
ENV DOCUMENT_ROOT=${WORKDIR}
ENV TZ=UTC

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Update packages
RUN apk update && apk upgrade --no-cache

# Install Git
RUN apk add git supervisor --no-cache

# Install PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions exif \
    gd imagick \
    intl \
    opcache \
    pdo_mysql \
    zip

# Install Composer
ENV COMPOSER_HOME /composer
ENV PATH ./vendor/bin:/composer/vendor/bin:$PATH
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -s https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

# Cleanup cache
RUN apk cache clean

# Set working directory
WORKDIR $WORKDIR

# Copy project files
COPY --chmod=www-data . /var/www/html

# Copy configs
COPY ./docker/php/conf.d/php.ini /usr/local/etc/php/conf.d/php.ini
COPY ./docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

# Install Composer dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Laravel Down
RUN php artisan down

## Migrate tables
#RUN php artisan migrate --force

# Optimize Laravel
# Clear expired password reset tokens
RUN #php artisan auth:clear-resets && \
    # Clear and cache config
    #php artisan config:cache && \
    # Clear and cache views
    php artisan view:cache && \
    # Clear and cache routes
    php artisan route:cache && \
    # Clear and cache events
    php artisan event:cache

# Laravel up
RUN php artisan up

# Copy entrypoint script
COPY ./docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh
RUN ln -s /usr/local/bin/entrypoint.sh /

ENTRYPOINT ["entrypoint.sh"]

# Apply correct permissions on workdir
RUN chmod -R 755 $WORKDIR
RUN chown -R www-data $WORKDIR

# Expose port
EXPOSE 9000

# Execute entrypoint
CMD [ "entrypoint" ]
