#!/bin/bash

export DEBIAN_FRONTEND=noninteractive

# Function to log messages
log_message() {
    echo "$(date +'%Y-%m-%d %H:%M:%S') - $1" >>/var/log/spikster_uninstall.log
}

# Function to handle errors gracefully
handle_error() {
    echo "Error occurred during $1. Check logs for details. Exiting."
    log_message "Error occurred during $1. Exiting."
    exit 1
}

# Function to remove a package if installed
remove_package() {
    if dpkg -l | grep -qw "$1"; then
        apt-get -y purge "$1" || handle_error "removing $1"
    else
        log_message "$1 is not installed"
    fi
}

# Function to remove a directory if it exists
remove_directory() {
    if [ -d "$1" ]; then
        rm -rf "$1" || handle_error "removing directory $1"
    else
        log_message "Directory $1 does not exist"
    fi
}

clear
echo "Starting Spikster uninstallation..."
log_message "Starting Spikster uninstallation..."

# Stop and disable services
log_message "Stopping and disabling services..."
systemctl stop nginx || log_message "Nginx service not running"
systemctl disable nginx || handle_error "disabling Nginx"
systemctl stop php8.3-fpm || log_message "PHP-FPM service not running"
systemctl disable php8.3-fpm || handle_error "disabling PHP-FPM"
systemctl stop mysql || log_message "MySQL service not running"
systemctl disable mysql || handle_error "disabling MySQL"
systemctl stop redis-server || log_message "Redis service not running"
# systemctl disable redis-server || handle_error "disabling Redis"
systemctl stop fail2ban || log_message "Fail2ban service not running"
systemctl disable fail2ban || handle_error "disabling Fail2ban"

# Remove packages
log_message "Removing packages..."
remove_package nginx
remove_package nginx-core
remove_package php8.3-fpm
remove_package php8.3-common
remove_package php8.3-curl
remove_package php8.3-bcmath
remove_package php8.3-mbstring
remove_package php8.3-mysql
remove_package php8.3-sqlite3
remove_package php8.3-pgsql
remove_package php8.3-redis
remove_package php8.3-memcached
remove_package php8.3-zip
remove_package php8.3-xml
remove_package php8.3-soap
remove_package php8.3-gd
remove_package php8.3-imagick
remove_package php8.3-imap
remove_package php8.3-cli
remove_package nodejs
remove_package npm
remove_package mysql-server
remove_package redis-server
remove_package fail2ban

# Remove Composer
log_message "Removing Composer..."
if command -v composer >/dev/null 2>&1; then
    rm /usr/local/bin/composer || handle_error "removing Composer"
else
    log_message "Composer is not installed"
fi

# Remove configuration files
log_message "Removing configuration files..."
remove_directory /etc/mysql

# Additional cleanup
log_message "Performing additional cleanup..."
apt-get -y autoremove || handle_error "autoremove"
apt-get -y clean || handle_error "clean"

echo "Spikster uninstallation completed."
log_message "Spikster uninstallation completed."

# EOF
