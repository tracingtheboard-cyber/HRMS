FROM php:8.2-fpm

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /var/www/html

# 复制 composer 文件
COPY composer.json composer.lock ./

# 安装依赖
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 复制应用代码
COPY . .

# 设置权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# 暴露端口
EXPOSE 8000

# 启动命令
CMD php artisan serve --host=0.0.0.0 --port=8000

