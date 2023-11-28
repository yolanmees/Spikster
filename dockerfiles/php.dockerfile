FROM php:8.2-fpm

ARG UID
ARG GID
ARG APP_DEBUG

ENV UID=${UID}
ENV GID=${GID}
ENV APP_DEBUG=${APP_DEBUG}

RUN apt-get update && \
    apt-get install -y \
    git\
    zip


RUN groupadd --gid ${GID} --system laravel
RUN useradd --system -s /bin/sh -u ${UID} -g ${GID} laravel

WORKDIR /var/www/html
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY --chown=laravel:laravel . .

RUN docker-php-ext-install pdo pdo_mysql

RUN mkdir -p /usr/src/php/ext/redis
RUN curl -L https://github.com/phpredis/phpredis/archive/refs/tags/5.3.7.tar.gz | tar -xvz -C /usr/src/php/ext/redis --strip 1
RUN echo 'redis' >> /usr/src/php-available-exts \
    && docker-php-ext-install redis

### Configurations

# Replace the www-data user with the laravel user we created
RUN sed -i "s/user = www-data/user = laravel/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/group = www-data/group = laravel/g" /usr/local/etc/php-fpm.d/www.conf
RUN echo "php_admin_flag[log_errors] = on" >> /usr/local/etc/php-fpm.d/www.conf

# Running this section as root as opposed to Laravel to avoid permissions error
RUN if [ "${APP_DEBUG}" = "true" ]; then \
    sed -i "s|;php_admin_value\[error_log\] = \/var\/log\/fpm-php.www.log|php_admin_value[error_log] = \/dev\/stderr/" /usr/local/etc/php-fpm.d/www.conf; \
    sed -i "s|;slowlog = log/\$pool.log.slow|slowlog = /dev/stdout|" /usr/local/etc/php-fpm.d/www.conf;\
    sed -i "s|;request_slowlog_timeout = 0|request_slowlog_timeout = 5s|" /usr/local/etc/php-fpm.d/www.conf; \
    export slowlog=/dev/stdout; \
    fi

# Copy the initialization script into the image
COPY init.sh /usr/local/bin/init.sh

# Grant execution permissions to the script
RUN chmod +x /usr/local/bin/init.sh

EXPOSE 9000

USER laravel

RUN composer update

ENTRYPOINT [ "/usr/local/bin/init.sh" ]

CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]