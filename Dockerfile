# Estágio 1: Dependências do PHP
FROM php:8.3-fpm-alpine as vendor
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN apk add --no-cache git unzip libpng-dev libzip-dev zip \
    && docker-php-ext-install pdo_pgsql zip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Estágio 2: Frontend (Assets)
FROM node:20-alpine as frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

# Estágio 3: Imagem Final de Produção
FROM php:8.3-fpm-alpine
WORKDIR /var/www/html

# Instalar extensões necessárias para o motor do Coddense
RUN apk add --no-cache libpng libzip libpq \
    && docker-php-ext-install pdo_pgsql zip

# Copiar app e dependências
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY . .

# Ajustar permissões (Fator VI: Stateless)
RUN chown -R www-data:www-data storage bootstrap/cache

# Configuração de Logs (Fator XI)
RUN ln -sf /dev/stderr /var/www/html/storage/logs/laravel.log

EXPOSE 9000
CMD ["php-fpm"]
