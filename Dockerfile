FROM php:8.3-apache

RUN a2enmod rewrite headers expires

# Use public/ as DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf /etc/apache2/conf-available/*.conf

# PHP ini sécurisée (baseline)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    { \
      echo "expose_php = Off"; \
      echo "file_uploads = On"; \
      echo "upload_max_filesize = 100M"; \
      echo "post_max_size = 110M"; \
      echo "max_file_uploads = 10"; \
      echo "session.cookie_httponly = 1"; \
      echo "session.cookie_samesite = Strict"; \
      echo "session.use_strict_mode = 1"; \
      echo "session.use_only_cookies = 1"; \
      echo "display_errors = Off"; \
      echo "log_errors = On"; \
      echo "error_log = /var/log/php_errors.log"; \
      echo "allow_url_fopen = Off"; \
      echo "memory_limit = 1024M"; \
    } >> "$PHP_INI_DIR/php.ini"

# Extensions: PDO SQLite + GD (JPEG/PNG/WebP)
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
      libsqlite3-dev \
      libjpeg62-turbo-dev libpng-dev libwebp-dev; \
    docker-php-ext-configure pdo_sqlite --with-pdo-sqlite=/usr; \
    docker-php-ext-install -j"$(nproc)" pdo_sqlite; \
    docker-php-ext-configure gd --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" gd; \
    rm -rf /var/lib/apt/lists/*

# Apache: exposer /cdn/ (statique, PHP désactivé) + docroot public/
COPY apache-cdn.conf /etc/apache2/conf-enabled/cdn.conf

WORKDIR /var/www/html
COPY /app/ /var/www/html/app/
COPY /public/ /var/www/html/public/
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
