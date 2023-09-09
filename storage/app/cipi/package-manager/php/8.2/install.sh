sudo apt-get -y install php8.2-fpm
sudo apt-get -y install php8.2-common
sudo apt-get -y install php8.2-curl
sudo apt-get -y install php8.2-openssl
sudo apt-get -y install php8.2-bcmath
sudo apt-get -y install php8.2-mbstring
sudo apt-get -y install php8.2-tokenizer
sudo apt-get -y install php8.2-mysql
sudo apt-get -y install php8.2-sqlite3
sudo apt-get -y install php8.2-pgsql
sudo apt-get -y install php8.2-redis
sudo apt-get -y install php8.2-memcached
sudo apt-get -y install php8.2-json
sudo apt-get -y install php8.2-zip
sudo apt-get -y install php8.2-xml
sudo apt-get -y install php8.2-soap
sudo apt-get -y install php8.2-gd
sudo apt-get -y install php8.2-imagick
sudo apt-get -y install php8.2-fileinfo
sudo apt-get -y install php8.2-imap
sudo apt-get -y install php8.2-cli
PHPINI=/etc/php/8.2/fpm/conf.d/cipi.ini
sudo touch $PHPINI
sudo cat > "$PHPINI" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 180
max_input_time = 180
EOF
sudo service php8.2-fpm restart
sudo apt-get -y install php-dev php-pear
sudo apt-get -y install php-dev php-pear

