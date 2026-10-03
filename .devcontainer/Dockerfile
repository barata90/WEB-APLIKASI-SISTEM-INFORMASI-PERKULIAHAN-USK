# Lingkungan GitHub Codespaces untuk SIAKAD: PHP 8.3 + Apache (mod_php)
FROM php:8.3-apache

# Alat bantu di terminal Codespaces (git, klien MySQL). Bukan syarat aplikasi berjalan.
ARG INSTALL_TOOLS=true
RUN if [ "$INSTALL_TOOLS" = "true" ]; then \
      apt-get update \
      && apt-get install -y --no-install-recommends git unzip mariadb-client \
      && rm -rf /var/lib/apt/lists/*; \
    fi

# Ekstensi PDO MySQL dan mod_rewrite untuk .htaccess
RUN docker-php-ext-install pdo_mysql && a2enmod rewrite

# Document root diarahkan ke folder repositori, .htaccess diizinkan
ENV APACHE_DOCUMENT_ROOT=/workspaces/siakad
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
 && printf '<Directory ${APACHE_DOCUMENT_ROOT}>\n    AllowOverride All\n    Require all granted\n</Directory>\nServerName localhost\n' > /etc/apache2/conf-available/siakad.conf \
 && a2enconf siakad
