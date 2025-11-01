FROM webdevops/php-nginx-dev:8.2-alpine

ENV WEB_DOCUMENT_ROOT=/app/public
ENV WEB_PHP_TIMEOUT=3000

WORKDIR /app

RUN apk add mysql-client mariadb-connector-c

COPY laravel-nginx.conf /opt/docker/etc/nginx/vhost.common.d/laravel-nginx.conf

# Configure Xdebug 3.x for debugging
# Tente com 'yes' primeiro. Se não funcionar, mude para 'trigger' e use cookie XDEBUG_SESSION
RUN echo "xdebug.mode=debug" | tee -a /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini > /dev/null && \
    echo "xdebug.start_with_request=yes" | tee -a /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini > /dev/null && \
    echo "xdebug.discover_client_host=false" | tee -a /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini > /dev/null && \
    echo "xdebug.client_host=host.docker.internal" | tee -a /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini > /dev/null && \
    echo "xdebug.client_port=9003" | tee -a /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini > /dev/null && \
    echo "xdebug.log=/var/log/xdebug.log" | tee -a /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini > /dev/null && \
    echo "xdebug.log_level=7" | tee -a /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini > /dev/null && \
    echo "xdebug.connect_timeout_ms=200" | tee -a /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini > /dev/null

RUN echo "post_max_size=120M" | tee -a /usr/local/etc/php/conf.d/98-webdevops.ini > /dev/null && \
    echo "upload_max_filesize=120M" | tee -a /usr/local/etc/php/conf.d/98-webdevops.ini > /dev/null

# Fix storage permissions - create permission fix script
RUN echo '#!/bin/sh' > /entrypoint-permissions.sh && \
    echo 'chown -R application:application /app/storage /app/bootstrap/cache 2>/dev/null || true' >> /entrypoint-permissions.sh && \
    echo 'chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true' >> /entrypoint-permissions.sh && \
    chmod +x /entrypoint-permissions.sh

# Create wrapper entrypoint that fixes permissions then calls original entrypoint
RUN echo '#!/bin/sh' > /docker-entrypoint.sh && \
    echo 'set -e' >> /docker-entrypoint.sh && \
    echo '/entrypoint-permissions.sh' >> /docker-entrypoint.sh && \
    echo 'if [ -f /opt/docker/bin/entrypoint.sh ]; then' >> /docker-entrypoint.sh && \
    echo '  exec /opt/docker/bin/entrypoint.sh "$@"' >> /docker-entrypoint.sh && \
    echo 'else' >> /docker-entrypoint.sh && \
    echo '  exec "$@"' >> /docker-entrypoint.sh && \
    echo 'fi' >> /docker-entrypoint.sh && \
    chmod +x /docker-entrypoint.sh

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["supervisord"]