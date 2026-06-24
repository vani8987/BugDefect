FROM php:8.3-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql

COPY . .

RUN php composer.phar install || true

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]