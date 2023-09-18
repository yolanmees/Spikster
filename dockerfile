FROM ubuntu:22.04
ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y software-properties-common 

# Set the working directory in the container
WORKDIR /var/www/html
RUN apt-get update &&  apt-get install -y git
RUN git clone https://github.com/yolanmees/Spikster.git /var/www/html


RUN LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php

RUN apt-get update -y && apt-get install -y php8.2 \
    libapache2-mod-php8.2 \
    php8.2-common \
    php8.2-mysql \
    php8.2-gmp \
    php8.2-ldap \
    php8.2-curl \
    php8.2-intl \
    php8.2-mbstring \
    php8.2-xmlrpc \
    php8.2-gd \
    php8.2-bcmath \
    php8.2-xml \
    php8.2-cli \
    php8.2-zip \
    php8.2-fpm 

RUN apt-get update && apt-get install -y \
    libfreetype-dev \
    libpng-dev 



# Update the system and install necessary packages
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y \
    software-properties-common curl wget nano vim rpl sed zip unzip openssl expect dirmngr apt-transport-https lsb-release ca-certificates dnsutils dos2unix zsh htop ffmpeg \
    nginx-core \
    fail2ban \
    redis-server \
    certbot python3-certbot-nginx \
    supervisor 

COPY ./nginx/default /etc/nginx/sites-available/default

RUN apt-get install -y nodejs npm

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer 

RUN chmod -R 755 /var/www/html
RUN chown -R www-data:www-data /var/www/html
RUN export COMPOSER_ALLOW_SUPERUSER=1
RUN cp .env.example .env
RUN composer install --ignore-platform-reqs
RUN php artisan key:generate
RUN npm install 
RUN npm run dev
RUN service nginx restart
RUN service php8.2-fpm restart

# Tell Docker about the port we'll run on.
EXPOSE 80 22 443 3306 6379 9000

# Run the specified command within the container.
CMD [ "supervisord", "-n" ]RUN wget -O - https://raw.githubusercontent.com/yolanmees/Spikster/master/go.sh 