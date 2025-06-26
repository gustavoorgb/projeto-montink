FROM phpdockerio/php:8.4-fpm

# Instalar dependências do sistema
RUN apt-get update \
    && apt-get -y --no-install-recommends install \
    php8.4-cli \ 
    php8.4-mbstring \ 
    php8.4-xml \ 
    php8.4-pcov \ 
    php8.4-gd \ 
    php8.4-imagick \ 
    php8.4-mysql \ 
    php8.4-xdebug \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Instala o composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Criar diretório de trabalho dentro do container
WORKDIR /application
