FROM php:8.2

RUN mkdir -p /var/www/api

WORKDIR /var/www/api

COPY composer*.json /var/www/api

ARG user
ARG uid

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libzip-dev \
    libgd-dev
# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
#Mine

# Install extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-external-gd
RUN docker-php-ext-install gd
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install sockets

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for laravel application
RUN groupadd -g ${uid} ${user}
RUN useradd -u ${uid} -ms /bin/bash -g ${user} ${user}
RUN usermod -aG sudo ${user}

# Copy existing application directory contents
COPY . /var/www/api

# Copy existing application directory permissions
COPY --chown=${user}:${user} . /var/www/api

RUN composer install

# Change current user to www
USER ${user}



EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

