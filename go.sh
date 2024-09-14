#!/bin/bash
export DEBIAN_FRONTEND=noninteractive

### CONFIGURATION ###
BUILD=20240727
PASS=$(openssl rand -base64 32 | sha256sum | base64 | head -c 32 | tr '[:upper:]' '[:lower:]')
DBPASS=$(openssl rand -base64 24 | sha256sum | base64 | head -c 32 | tr '[:upper:]' '[:lower:]')
SERVERID=$(openssl rand -base64 12 | sha256sum | base64 | head -c 32 | tr '[:upper:]' '[:lower:]')
REPO=yolanmees/Spikster
BRANCH=master
ADMIN_EMAIL="your_admin_email@example.com"
USE_LOCAL_IP=false

# CLI tool coloring
reset=$(tput sgr0)
bold=$(tput bold)
underline=$(tput smul)
black=$(tput setaf 0)
white=$(tput setaf 7)
red=$(tput setaf 1)
green=$(tput setaf 2)
yellow=$(tput setaf 3)
blue=$(tput setaf 4)
purple=$(tput setaf 5)
bgblack=$(tput setab 0)
bgwhite=$(tput setab 7)
bgred=$(tput setab 1)
bggreen=$(tput setab 2)
bgyellow=$(tput setab 4)
bgblue=$(tput setab 4)
bgpurple=$(tput setab 5)

# Function to check disk space
check_disk_space() {
    required_space=2048 #
    available_space=$(df / | tail -1 | awk '{print $4}')
    if ((available_space < required_space)); then
        echo "${bgred}${white}${bold}Error: Not enough disk space.
        Required: ${required_space}MB, Available: $(($available_space / 1024))MB.${reset}"
        exit 1
    fi
}

# Call to the disk space check function
clear
check_disk_space

# LOGO
clear
echo "${green}${bold}"
echo ""
echo "███████╗██████╗ ██╗██╗  ██╗███████╗████████╗███████╗██████╗ "
echo "██╔════╝██╔══██╗██║██║ ██╔╝██╔════╝╚══██╔══╝██╔════╝██╔══██╗"
echo "███████╗██████╔╝██║█████╔╝ ███████╗   ██║   █████╗  ██████╔╝"
echo "╚════██║██╔═══╝ ██║██╔═██╗ ╚════██║   ██║   ██╔══╝  ██╔══██╗"
echo "███████║██║     ██║██║  ██╗███████║   ██║   ███████╗██║  ██║"
echo "╚══════╝╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝   ╚═╝   ╚══════╝╚═╝  ╚═╝"
echo ""
echo "Installation has been started... Hold on!"
echo "${reset}"
sleep 3s

help() {
    echo "Usage: $0 [OPTIONS]
    -n, --nginx         Install Nginx         [yes|no]  default: yes
    -p, --phpfpm        Install PHP-FPM       [yes|no]  default: yes
    -m, --mysql         Install MySQL         [yes|no]  default: yes
    -b, --branch        Set branch for admin panel        default: master
    -f, --force         Force installation
    -l, --local         Use local IP instead of external IP
    -h, --help          Print this help

    Example: bash $0 -n no -p yes -m yes"
    exit 1
}

# Parse short and long arguments
nginx='yes'
phpfpm='yes'
mysql='yes'

while [ $# -gt 0 ]; do
    case $1 in
    -n | --nginx)
        nginx="$2"
        shift 2
        ;;
    -p | --phpfpm)
        phpfpm="$2"
        shift 2
        ;;
    -m | --mysql)
        mysql="$2"
        shift 2
        ;;
    -b | --branch)
        BRANCH="$2"
        shift 2
        ;;
    -f | --force)
        force='yes'
        shift
        ;;
    -l | --local)
        USE_LOCAL_IP=true
        shift
        ;;
    -h | --help)
        help
        ;;
    *)
        echo "Unknown option: $1"
        help
        ;;
    esac
done

# Ensure valid input values
validate_choice() {
    if [ "$1" != "yes" ] && [ "$1" != "no" ]; then
        echo "${bgred}${white}${bold}Invalid option for $2. Expected [yes|no].${reset}"
        exit 1
    fi
}

validate_choice "$nginx" "Nginx"
validate_choice "$phpfpm" "PHP-FPM"
validate_choice "$mysql" "MySQL"

# Function to log messages
log_message() {
    echo "$(date +'%Y-%m-%d %H:%M:%S') - $1" >>/var/log/spikster_install.log
}

# Function to handle errors gracefully
handle_error() {
    echo "${bgred}${white}${bold}Error occurred during $1. Check logs for details. Exiting.${reset}"
    log_message "Error occurred during $1. Exiting."
    exit 1
}

# Check for existing software versions
log_message "Checking current versions of installed software"
nginx_version=$(nginx -v 2>&1)
php_version=$(php -v 2>&1)
mysql_version=$(mysql --version 2>&1)
versions=""
if dpkg -l | grep -qw nginx; then
    versions+="Nginx: $nginx_version\n"
else
    versions+="Nginx: Not installed\n"
fi
if dpkg -l | grep -qw php; then
    versions+="PHP: $php_version\n"
else
    versions+="PHP: Not installed\n"
fi
if dpkg -l | grep -qw mysql-server; then
    versions+="MySQL: $mysql_version\n"
else
    versions+="MySQL: Not installed\n"
fi
echo -e "$versions" >>/var/log/spikster_install.log

# OS Check
clear
log_message "OS check..."
echo "${bggreen}${black}${bold}"
echo "OS check..."
echo "${reset}"
sleep 1s

ID=$(grep -oP '(?<=^ID=).+' /etc/os-release | tr -d '"')
VERSION=$(grep -oP '(?<=^VERSION_ID=).+' /etc/os-release | tr -d '"')
if [ "$ID" = "ubuntu" ]; then
    case $VERSION in
    20.04 | 22.04 | 23.04 | 24.04) ;;
    *)
        echo "${bgred}${white}${bold}"
        echo "Spikster requires a minimum of Linux Ubuntu 20.04 LTS"
        echo "${reset}"
        log_message "Unsupported Ubuntu version: $VERSION"
        exit 1
        ;;
    esac
else
    echo "${bgred}${white}${bold}"
    echo "Spikster requires a minimum of Linux Ubuntu 20.04 LTS"
    echo "${reset}"
    log_message "Unsupported OS: $ID"
    exit 1
fi

# Root check
clear
log_message "Permission check..."
echo "${bggreen}${black}${bold}"
echo "Permission check..."
echo "${reset}"
sleep 1s

if [ "$(id -u)" != "0" ]; then
    echo "${bgred}${white}${bold}"
    echo "You have to run Spikster as root. (In AWS use 'sudo -s')"
    echo "${reset}"
    log_message "Script must be run as root"
    exit 1
fi

# Backup existing configurations
backup_configuration() {
    backup_dir="/root/spikster_backup_$(date +%s)"
    mkdir -p $backup_dir

    config_files=(
        "/etc/nginx"
        "/etc/mysql"
        "/etc/php"
        "/etc/supervisor/conf.d"
    )

    for file in "${config_files[@]}"; do
        if [ -d $file ]; then
            cp -r $file $backup_dir
        fi
    done

    log_message "Configuration files backed up to $backup_dir"
    echo "Backup completed and stored to $backup_dir"
}

get_local_ip() {
    local_ip=$(hostname -I | awk '{print $1}')
    echo $local_ip
}

# Perform backup
backup_configuration

# Basic setup
clear
log_message "Basic setup..."
echo "${bggreen}${black}${bold}"
echo "Base setup..."
echo "${reset}"
sleep 1s

apt-get update || handle_error "apt-get update"
apt-get -y install software-properties-common curl wget nano vim sed zip unzip openssl expect \
    dirmngr apt-transport-https lsb-release ca-certificates dnsutils dos2unix zsh htop ffmpeg || handle_error "installing basic packages"

# GET IP
clear
echo "${bggreen}${black}${bold}"
echo "Getting IP..."
echo "${reset}"
sleep 1s
if $USE_LOCAL_IP; then
    IP=$(get_local_ip)
else
    IP=$(curl -s https://checkip.amazonaws.com)
fi
log_message "Server IP obtained: $IP"

# MOTD WELCOME MESSAGE
clear
echo "${bggreen}${black}${bold}"
echo "Motd settings..."
echo "${reset}"
sleep 1s
WELCOME=/etc/motd
touch $WELCOME
cat >"$WELCOME" <<EOF
███████╗██████╗ ██╗██╗  ██╗███████╗████████╗███████╗██████╗
██╔════╝██╔══██╗██║██║ ██╔╝██╔════╝╚══██╔══╝██╔════╝██╔══██╗
███████╗██████╔╝██║█████╔╝ ███████╗   ██║   █████╗  ██████╔╝
╚════██║██╔═══╝ ██║██╔═██╗ ╚════██║   ██║   ██╔══╝  ██╔══██╗
███████║██║     ██║██║  ██╗███████║   ██║   ███████╗██║  ██║
╚══════╝╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝   ╚═╝   ╚══════╝╚═╝  ╚═╝

With great power comes great responsibility...
EOF
log_message "MOTD message set"

# SWAP FILE CREATION
clear
echo "${bggreen}${black}${bold}"
echo "Memory SWAP..."
echo "${reset}"
sleep 1s

# Check if swap is already enabled
if swapon --show | grep -q "swapfile"; then
    echo "${bgyellow}${black}${bold}Swapfile already exists.${reset}"
    log_message "Swapfile already exists. Skipping creation."
else
    fallocate -l 1G /swapfile || handle_error "creating swap file"
    chmod 600 /swapfile || handle_error "setting swap file permissions"
    mkswap /swapfile || handle_error "setting up swap space"
    swapon /swapfile || handle_error "enabling swap"
    echo "/swapfile   none    swap    sw    0   0" >>/etc/fstab
    log_message "1GB swapfile created and enabled"
fi

# ALIAS
clear
echo "${bggreen}${black}${bold}"
echo "Custom CLI configuration..."
echo "${reset}"
sleep 1s
shopt -s expand_aliases
alias ll='ls -alF'
log_message "CLI aliases set"

# Spikster DIRS
clear
echo "${bggreen}${black}${bold}"
echo "Spikster directories..."
echo "${reset}"
sleep 1s
mkdir /etc/spikster/ && chmod o-r /etc/spikster
mkdir /var/spikster/ && chmod o-r /var/spikster
log_message "Spikster directories created"

# USER
clear
echo "${bggreen}${black}${bold}"
echo "Spikster root user..."
echo "${reset}"
sleep 1s

# Check if user already exists
if id "spikster" &>/dev/null; then
    echo "${bgyellow}${black}${bold}User 'spikster' already exists. Deleting user...${reset}"
    userdel -r spikster || handle_error "deleting existing spikster user"
    log_message "'spikster' user already existed and was deleted"
fi

pam-auth-update --package || handle_error "updating PAM authentication"
useradd -m -s /bin/bash spikster || handle_error "creating spikster user"
echo "spikster:$PASS" | chpasswd || handle_error "setting spikster user password"
usermod -aG sudo spikster || handle_error "modifying spikster user groups"
log_message "Spikster user account created"

if [ "$phpfpm" = "yes" ]; then
    clear
    log_message "PHP-FPM setup..."
    echo "${bggreen}${black}${bold}"
    echo "PHP setup..."
    echo "${reset}"
    sleep 1s

    add-apt-repository -y ppa:ondrej/php || handle_error "adding PPA for PHP"
    apt-get update || handle_error "apt-get update after adding PPA"
    apt-get -y install php8.3-fpm php8.3-common php8.3-curl php8.3-bcmath \
        php8.3-mbstring php8.3-mysql php8.3-sqlite3 php8.3-pgsql php8.3-redis \
        php8.3-memcached php8.3-zip php8.3-xml php8.3-soap php8.3-gd \
        php8.3-imagick php8.3-imap php8.3-cli || handle_error "installing PHP packages"

    if ! command -v php >/dev/null 2>&1; then
        echo "${bgred}${white}${bold}PHP installation failed${reset}"
        log_message "PHP installation failed"
        exit 1
    else
        log_message "PHP installed successfully"
    fi

    PHPINI=/etc/php/8.3/fpm/conf.d/spikster.ini
    touch $PHPINI
    cat >"$PHPINI" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 180
max_input_time = 180
EOF
    service php8.3-fpm restart || handle_error "restarting PHP-FPM"
    log_message "PHP-FPM installed"

    update-alternatives --set php /usr/bin/php8.3 || handle_error "setting PHP CLI version"
    echo "${bggreen}${black}${bold}"
    echo "PHP CLI configuration..."
    echo "${reset}"
    sleep 1s
    log_message "PHP CLI configured"
fi

# Install Composer
if [ "$phpfpm" = "yes" ]; then
    clear
    log_message "Composer setup..."
    echo "${bggreen}${black}${bold}"
    echo "Composer setup..."
    echo "${reset}"
    sleep 1s

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    EXPECTED_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig)
    ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")
    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
        echo >&2 'ERROR: Invalid installer signature'
        rm -f composer-setup.php
        exit 1
    fi
    php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    RESULT=$?
    rm -f composer-setup.php
    if [ $RESULT -ne 0 ]; then
        log_message "Composer installation failed"
        exit $RESULT
    fi
    composer --version
    log_message "Composer installed"
fi

# GIT
clear
echo "${bggreen}${black}${bold}"
echo "GIT setup..."
echo "${reset}"
sleep 1s

apt-get -y install git
ssh-keygen -t rsa -C "git@github.com" -f /etc/spikster/github -q -P ""
log_message "GIT installed"

# SUPERVISOR
clear
echo "${bggreen}${black}${bold}"
echo "Supervisor setup..."
echo "${reset}"
sleep 1s

apt-get -y install supervisor
service supervisor restart
log_message "Supervisor installed"

# Install Nginx
if [ "$nginx" = "yes" ]; then
    start_nginx() {
        log_message "Checking Nginx configuration..."
        nginx -t || handle_error "Nginx configuration check"
        log_message "Nginx configuration is valid."

        log_message "Starting Nginx service..."
        systemctl start nginx.service || handle_error "starting Nginx"
        systemctl enable nginx.service || handle_error "enabling Nginx"
        log_message "Nginx started and enabled to start on boot."
    }

    clear
    log_message "Nginx setup..."
    echo "${bggreen}${black}${bold}"
    echo "Nginx setup..."
    echo "${reset}"
    sleep 1s

    apt-get -y install nginx-core || handle_error "installing Nginx"
    start_nginx
    sed -i -e 's|http {|&\n    limit_req_zone $binary_remote_addr zone=one:10m rate=1r/s;\n    fastcgi_read_timeout 300;|g' /etc/nginx/nginx.conf || handle_error "configuring Nginx" systemctl enable nginx.service || handle_error "enabling Nginx"
    log_message "Nginx installed"

    # DEFAULT VHOST
    clear
    echo "${bggreen}${black}${bold}"
    echo "Default vhost..."
    echo "${reset}"
    sleep 1s

    NGINX=/etc/nginx/sites-available/default
    if test -f "$NGINX"; then
        unlink NGINX
    fi
    touch $NGINX
    cat >"$NGINX" <<EOF
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    root /var/www/html/public;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    client_body_timeout 10s;
    client_header_timeout 10s;
    client_max_body_size 256M;
    index index.html index.php;
    charset utf-8;
    server_tokens off;
    location / {
        try_files   \$uri     \$uri/  /index.php?\$query_string;
    }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    error_page 404 /index.php;
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
    mkdir /etc/nginx/spikster/
    systemctl restart nginx.service

fi

# Install MySQL
if [ "$mysql" = "yes" ]; then
    clear
    log_message "MySQL setup..."
    echo "${bggreen}${black}${bold}"
    echo "MySQL setup..."
    echo "${reset}"
    sleep 1s

    apt-get -y install mysql-server || handle_error "installing MySQL"
    SECURE_MYSQL=$(expect -c "
    set timeout 10
    spawn mysql_secure_installation
    expect \"Press y|Y for Yes, any other key for No:\"
    send \"n\r\"
    expect \"New password:\"
    send \"$DBPASS\r\"
    expect \"Re-enter new password:\"
    send \"$DBPASS\r\"
    expect \"Remove anonymous users? (Press y|Y for Yes, any other key for No)\"
    send \"y\r\"
    expect \"Disallow root login remotely? (Press y|Y for Yes, any other key for No)\"
    send \"n\r\"
    expect \"Remove test database and access to it? (Press y|Y for Yes, any other key for No)\"
    send \"y\r\"
    expect \"Reload privilege tables now? (Press y|Y for Yes, any other key for No) \"
    send \"y\r\"
    expect eof
    ")
    echo "$SECURE_MYSQL"
    /usr/bin/mysql -u root -p$DBPASS <<EOF
    use mysql;
    CREATE USER 'spikster'@'%' IDENTIFIED WITH mysql_native_password BY '$DBPASS';
    GRANT ALL PRIVILEGES ON *.* TO 'spikster'@'%' WITH GRANT OPTION;
    FLUSH PRIVILEGES;
EOF
    log_message "MySQL installed"
fi

# REDIS
clear
echo "${bggreen}${black}${bold}"
echo "Redis setup..."
echo "${reset}"
sleep 1s

apt install -y redis-server
rpl -i -w "supervised no" "supervised systemd" /etc/redis/redis.conf
systemctl restart redis.service

# LET'S ENCRYPT
clear
echo "${bggreen}${black}${bold}"
echo "Let's Encrypt setup..."
echo "${reset}"
sleep 1s

apt-get install -y certbot
apt-get install -y python3-certbot-nginx
apt-get install -y pyenv

# NODE
clear
echo "${bggreen}${black}${bold}"
echo "Node/npm setup..."
echo "${reset}"
sleep 1s

curl -s https://deb.nodesource.com/gpgkey/nodesource.gpg.key | apt-key add -
curl -sL https://deb.nodesource.com/setup_16.x | -E bash -
NODE=/etc/apt/sources.list.d/nodesource.list
unlink NODE
touch $NODE
cat >"$NODE" <<EOF
deb https://deb.nodesource.com/node_16.x focal main
deb-src https://deb.nodesource.com/node_16.x focal main
EOF
apt-get update
apt -y install nodejs
apt -y install npm

# Exim installation and configuration
clear
echo "${bggreen}${black}${bold}"
echo "Exim setup..."
echo "${reset}"
sleep 1s

apt-get -y install exim4 || handle_error "installing Exim"
dpkg-reconfigure exim4-config
update-exim4.conf

# Dovecot installation and configuration
clear
echo "${bggreen}${black}${bold}"
echo "Dovecot setup..."
echo "${reset}"
sleep 1s

apt-get -y install dovecot-core dovecot-imapd dovecot-pop3d || handle_error "installing Dovecot"

# Configure Dovecot
cat >/etc/dovecot/dovecot.conf <<EOF
disable_plaintext_auth = yes
mail_privileged_group = mail
mail_location = maildir:~/Maildir

protocols = imap pop3

service imap-login {
    inet_listener imap {
        port = 0
    }
    inet_listener imaps {
        port = 993
        ssl = yes
    }
}

service pop3-login {
    inet_listener pop3 {
        port = 0
    }
    inet_listener pop3s {
        port = 995
        ssl = yes
    }
}

ssl = required
ssl_cert = </etc/ssl/certs/ssl-cert-snakeoil.pem
ssl_key = </etc/ssl/private/ssl-cert-snakeoil.key
EOF

systemctl restart dovecot || handle_error "restarting Dovecot"

# SpamAssassin installation and configuration
clear
echo "${bggreen}${black}${bold}"
echo "SpamAssassin setup..."
echo "${reset}"
sleep 1s

apt-get -y install spamassassin spamc || handle_error "installing SpamAssassin"
systemctl enable spamassassin
systemctl start spamassassin

# ClamAV installation and configuration
clear
echo "${bggreen}${black}${bold}"
echo "ClamAV setup..."
echo "${reset}"
sleep 1s

apt-get -y install clamav clamav-daemon || handle_error "installing ClamAV"
systemctl stop clamav-freshclam
freshclam
systemctl start clamav-freshclam

# Roundcube installation and configuration
clear
echo "${bggreen}${black}${bold}"
echo "Roundcube setup..."
echo "${reset}"
sleep 1s

apt-get -y install roundcube roundcube-plugins roundcube-core || handle_error "installing Roundcube"

# Link Roundcube to web server
ln -s /usr/share/roundcube /var/www/html/roundcube

# Update Roundcube configuration
sed -i "s|^\(\$config\['default_host'\] = \).*|\1'localhost';|" /etc/roundcube/config.inc.php
sed -i "s|^\(\$config\['smtp_server'\] = \).*|\1'localhost';|" /etc/roundcube/config.inc.php
sed -i "s|^\(\$config\['imap_auth_type'\] = \).*|\1'PLAIN';|" /etc/roundcube/config.inc.php
sed -i "s|^\(\$config\['smtp_auth_type'\] = \).*|\1'PLAIN';|" /etc/roundcube/config.inc.php

systemctl restart nginx.service || handle_error "restarting Nginx"

# Fail2ban setup
clear
log_message "Fail2ban setup..."
echo "${bggreen}${black}${bold}"
echo "Fail2ban setup..."
echo "${reset}"
sleep 1s

apt-get -y install fail2ban || handle_error "installing Fail2Ban"
JAIL=/etc/fail2ban/jail.local
touch $JAIL
cat >"$JAIL" <<EOF
[DEFAULT]
bantime = 3600
banaction = iptables-multiport
[sshd]
enabled = true
logpath  = /var/log/auth.log
EOF
systemctl restart fail2ban || handle_error "restarting Fail2Ban"
log_message "Fail2ban configured"

# Firewall setup
ufw --force enable || handle_error "enabling UFW"
ufw allow ssh || handle_error "allowing SSH through UFW"
ufw allow http || handle_error "allowing HTTP through UFW"
ufw allow https || handle_error "allowing HTTPS through UFW"
if [ "$nginx" = "yes" ]; then
    ufw allow "Nginx Full" || handle_error "allowing Nginx through UFW"
fi
log_message "Firewall configured"

# Panel Installation
if [ "$nginx" = "yes" ]; then
    clear
    log_message "Panel installation..."
    echo "${bggreen}${black}${bold}"
    echo "Panel installation..."
    echo "${reset}"
    sleep 1s

    # Create MySQL Database if MySQL is set to install
    if [ "$mysql" = "yes" ]; then
        /usr/bin/mysql -u root -p$DBPASS <<EOF
CREATE DATABASE IF NOT EXISTS spikster;
EOF
        log_message "MySQL database 'spikster' created"
    fi

    clear
    rm -rf /var/www/html

    # Clone the repository and log errors if any
    cd /var/www && git clone https://github.com/yolanmees/Spikster.git html || {
        log_message "Failed to clone the repository"
        exit 1
    }

    cd /var/www/html && git checkout $BRANCH || {
        log_message "Failed to checkout branch $BRANCH"
        exit 1
    }

    cd /var/www/html && composer update --no-interaction || {
        log_message "Composer update failed"
        exit 1
    }

    if [ -f .env ]; then
        rm .env
    fi
    cp .env.example .env
    php artisan key:generate || handle_error "artisan key:generate"

    # Replace database and application configurations using sed
    sed -i "s/DB_USERNAME=dbuser/DB_USERNAME=spikster/g" /var/www/html/.env
    sed -i "s/DB_PASSWORD=dbpass/DB_PASSWORD=$DBPASS/g" /var/www/html/.env
    sed -i "s/DB_DATABASE=dbname/DB_DATABASE=spikster/g" /var/www/html/.env
    sed -i "s|APP_URL=http://localhost|APP_URL=http://$IP|g" /var/www/html/.env
    sed -i "s/APP_ENV=local/APP_ENV=production/g" /var/www/html/.env

    # Replace additional placeholders in the seeder file
    sed -i "s|CIPISERVERID|$SERVERID|g" /var/www/html/database/seeders/DatabaseSeeder.php
    sed -i "s|CIPIIP|$IP|g" /var/www/html/database/seeders/DatabaseSeeder.php
    sed -i "s|CIPIPASS|$PASS|g" /var/www/html/database/seeders/DatabaseSeeder.php
    sed -i "s|CIPIDB|$DBPASS|g" /var/www/html/database/seeders/DatabaseSeeder.php

    chmod -R o+w /var/www/html/storage
    chmod -R 777 /var/www/html/storage
    chmod -R o+w /var/www/html/bootstrap/cache
    chmod -R 777 /var/www/html/bootstrap/cache

    # Run composer update and handle errors
    cd /var/www/html && composer update --no-interaction || {
        log_message "Composer update failed"
        exit 1
    }

    php artisan key:generate || handle_error "artisan key:generate"
    php artisan cache:clear || handle_error "artisan cache:clear"
    php artisan storage:link || handle_error "artisan storage:link"
    php artisan view:cache || handle_error "artisan view:cache"
    php artisan migrate --seed --force || handle_error "artisan migrate --seed --force"
    php artisan config:cache || handle_error "artisan config:cache"
    chmod -R o+w /var/www/html/storage
    chmod -R 775 /var/www/html/storage
    chmod -R o+w /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/bootstrap/cache
    chown -R www-data:spikster /var/www/html

    log_message "Panel installed"
fi

# Last Steps
clear
log_message "Last steps..."
echo "${bggreen}${black}${bold}"
echo "Last steps..."
echo "${reset}"
sleep 1s

chown www-data:spikster -R /var/www/html
chmod -R 750 /var/www/html
echo 'DefaultStartLimitIntervalSec=1s' >>/usr/lib/systemd/system/user@.service
echo 'DefaultStartLimitBurst=50' >>/usr/lib/systemd/system/user@.service
echo 'StartLimitBurst=0' >>/usr/lib/systemd/system/user@.service
systemctl daemon-reload || handle_error "daemon-reload"

# Set up the cron jobs
TASK=/etc/cron.d/spikster.crontab
touch $TASK
cat >"$TASK" <<EOF
10 4 * * 7 certbot renew --nginx --non-interactive --post-hook "systemctl restart nginx.service"
20 4 * * 7 apt-get -y update
40 4 * * 7 DEBIAN_FRONTEND=noninteractive DEBIAN_PRIORITY=critical apt-get -q -y -o "Dpkg::Options::=--force-confdef" -o "Dpkg::Options::=--force-confold" dist-upgrade
20 5 * * 7 apt-get clean && apt-get autoclean
50 5 * * * echo 3 > /proc/sys/vm/drop_caches && swapoff -a && swapon -a
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
5 2 * * * cd /var/www/html/utility/spikster-update && sh run.sh >> /dev/null 2>&1
EOF
crontab $TASK || handle_error "setting up crontab"
systemctl restart nginx.service || handle_error "restarting nginx"
log_message "Cron jobs and Nginx restart configured"

# Strengthen SSH configuration
rpl -i -w "#PasswordAuthentication" "PasswordAuthentication" /etc/ssh/sshd_config
rpl -i -w "# PasswordAuthentication" "PasswordAuthentication" /etc/ssh/sshd_config
rpl -i -w "PasswordAuthentication no" "PasswordAuthentication yes" /etc/ssh/sshd_config
rpl -i -w "PermitRootLogin yes" "PermitRootLogin no" /etc/ssh/sshd_config
service sshd restart || handle_error "restarting SSH service"
log_message "SSH configuration strengthened"

# Supervisor setup for worker processes
clear
echo "${bggreen}${black}${bold}"
echo "Supervisor setup..."
echo "${reset}"
sleep 1s

# Install Supervisor
apt-get install -y supervisor || handle_error "installing Supervisor"
service supervisor start || handle_error "starting Supervisor"

# Create the directory if it doesn't exist
mkdir -p /etc/supervisor/conf.d

TASK=/etc/supervisor/conf.d/spikster-worker.conf
touch $TASK
cat >"$TASK" <<EOF
[program:spikster-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=spikster
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/worker.log
stopwaitsecs=3600
EOF

# Ensure supervisorctl exists in the path
if ! command -v supervisorctl &>/dev/null; then
    echo "${bgred}${white}${bold}supervisorctl is missing. Exiting.${reset}"
    log_message "supervisorctl is missing. Exiting."
    exit 1
fi

supervisorctl reread || handle_error "supervisor reread"
supervisorctl update || handle_error "supervisor update"
supervisorctl start all || handle_error "supervisor start all"
service supervisor restart || handle_error "restarting supervisor"
log_message "Supervisor configured"

# Additional installations
apt install -y bind9 bind9utils bind9-doc || handle_error "installing bind9"
apt install -y python3-pip || handle_error "installing Python3 pip"

pip install glances bottle fastapi uvicorn || handle_error "installing Python packages"
log_message "Additional packages installed: bind9, glances, bottle, fastapi"

# Complete
clear
echo "${bggreen}${black}${bold}"
echo "Spikster installation has been completed..."
echo "${reset}"
sleep 1s

# Send notification email upon successful completion
email_notification() {
    local subject="$1"
    local body="$2"
    mail -s "$subject" $ADMIN_EMAIL <<EOF
$body

-- Spikster Team
EOF
}
email_notification "Spikster Installation Completed" "The installation of Spikster on host $(hostname) was successful. Server Details:\n- SSH user: spikster\n- SSH pass: $PASS\n- MySQL user: spikster\n- MySQL pass: $DBPASS\n\nYou can manage your server by visiting: http://$IP and clicking on the 'dashboard' button.\nDefault credentials are: administrator@localhost / password"
log_message "Installation completion notification sent"

log_message "Spikster installation completed"
echo "${bggreen}${black}${bold}"
echo "Spikster installation has been completed..."
echo "${reset}"
sleep 1s
# Start glances in the background and ensure it keeps running after the script ends
nohup glances -w --disable-webui >/var/log/glances.log 2>&1 &

echo "Glances has been started in the background."

# Confirm script completion
log_message "Script completed successfully."
echo "${bggreen}${black}${bold}"
echo "Script completed successfully."
echo "${reset}"
sleep 1s

echo "***********************************************************"
echo "                    SETUP COMPLETE"
echo "***********************************************************"
echo ""
echo " SSH root user: spikster"
echo " SSH root pass: $PASS"
echo " MySQL root user: spikster"
echo " MySQL root pass: $DBPASS"
echo ""
echo " To manage your server visit: http://$IP"
echo " and click on 'dashboard' button."
echo " Default credentials are: administrator@localhost / password"
echo ""
echo "***********************************************************"
echo "          DO NOT LOSE AND KEEP SAFE THIS DATA"
echo "***********************************************************"

# Exit script
exit 0
