FROM php:7.3-fpm-alpine

# Add packages
RUN apk update && apk upgrade && \
    apk add --no-cache bash git openssh

# Add pdo_mysql
RUN docker-php-ext-install pdo_mysql mysqli

# Add xDebug
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-2.7.0 \
    && docker-php-ext-enable xdebug
# install composer
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
&& curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
# Make sure we're installing what we think we're installing!
&& php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
&& php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --snapshot \
&& rm -f /tmp/composer-setup.*
# Use the default development configuration
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY mailhog.ini $PHP_INI_DIR/conf.d/
COPY show_errors.ini $PHP_INI_DIR/conf.d/
