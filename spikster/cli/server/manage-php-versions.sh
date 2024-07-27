#!/bin/bash

# Function to display usage
usage() {
    echo "Usage: $0 --format {json|csv|table|list} [options]"
    echo "Options:"
    echo "  --set-default {php_version}         Set the default PHP version"
    echo "  --install {php_version}             Install the specified PHP version"
    echo "  --remove {php_version}              Remove the specified PHP version"
    echo "  --set-apache {php_version}          Set PHP version for Apache"
    echo "  --set-nginx {php_version}           Set PHP version for Nginx"
    echo "Examples:"
    echo "  $0 --format table              # List all PHP versions in table format"
    echo "  $0 --format json --set-default php8.3  # Set PHP 8.3 as default and output in JSON format"
    echo "  $0 --format csv --install php8.3  # Install PHP 8.3 and output in CSV format"
    exit 1
}

# Log Function
log_message() {
    echo "$(date +'%Y-%m-%d %H:%M:%S') - $1" >>/var/log/spikster_manage_php.log
}

manage_php() {
    # Function to list PHP versions
    list_php_versions() {
        PHP_VERSIONS=$(update-alternatives --list php)
        CURRENT_VERSION=$(php -v | head -n 1 | awk '{print $2}')
        DEFAULT_VERSION=$(readlink -f /etc/alternatives/php | sed 's@.*/php@@')

        declare -A PHP_VERSION_STATUSES
        for version in $PHP_VERSIONS; do
            PHP_VERSION=$(basename $version)
            if [[ $PHP_VERSION == *"$CURRENT_VERSION"* ]]; then
                PHP_VERSION_STATUSES[$PHP_VERSION]="ACTIVE"
            else
                PHP_VERSION_STATUSES[$PHP_VERSION]="INACTIVE"
            fi

            if [[ $PHP_VERSION == *"$DEFAULT_VERSION"* ]]; then
                PHP_VERSION_STATUSES[$PHP_VERSION]="DEFAULT"
            fi
        done

        case $OUTPUT_FORMAT in
        json)
            echo "{"
            echo "  \"php_versions\": ["
            FIRST_ITEM=true
            for version in "${!PHP_VERSION_STATUSES[@]}"; do
                status="${PHP_VERSION_STATUSES[$version]}"
                if $FIRST_ITEM; then
                    FIRST_ITEM=false
                else
                    echo "    ,"
                fi
                echo "    { \"version\": \"$version\", \"status\": \"$status\" }"
            done
            echo "  ]"
            echo "}"
            ;;
        csv)
            echo "Version,Status"
            for version in "${!PHP_VERSION_STATUSES[@]}"; do
                echo "$version,${PHP_VERSION_STATUSES[$version]}"
            done
            ;;
        table)
            printf "%-10s %-10s\n" "Version" "Status"
            printf "%-10s %-10s\n" "-------" "------"
            for version in "${!PHP_VERSION_STATUSES[@]}"; do
                printf "%-10s %-10s\n" "$version" "${PHP_VERSION_STATUSES[$version]}"
            done
            ;;
        list)
            for version in "${!PHP_VERSION_STATUSES[@]}"; do
                echo "Version: $version, Status: ${PHP_VERSION_STATUSES[$version]}"
            done
            ;;
        *)
            usage
            ;;
        esac
    }

    # Function to set default PHP version
    set_default_php_version() {
        local php_version="$1"
        if [[ -z $(update-alternatives --list php | grep $php_version) ]]; then
            echo "Error: PHP version '$php_version' not found."
            log_message "Error: PHP version '$php_version' not found."
            exit 1
        fi

        sudo update-alternatives --set php /usr/bin/$php_version
        if [ $? -eq 0 ]; then
            echo "Default PHP version set to '$php_version'."
            log_message "Default PHP version set to '$php_version'."
        else
            echo "Error: Failed to set default PHP version to '$php_version'."
            log_message "Error: Failed to set default PHP version to '$php_version'."
            exit 1
        fi
    }

    # Function to install PHP version
    install_php_version() {
        local php_version="$1"
        sudo apt update
        sudo apt install -y $php_version $php_version-cli $php_version-fpm $php_version-mysql $php_version-curl $php_version-xml $php_version-mbstring $php_version-zip $php_version-bcmath
        if [ $? -eq 0 ]; then
            echo "PHP version '$php_version' installed successfully."
            log_message "PHP version '$php_version' installed successfully."
        else
            echo "Error: Failed to install PHP version '$php_version'."
            log_message "Error: Failed to install PHP version '$php_version'."
            exit 1
        fi
    }

    # Function to remove PHP version
    remove_php_version() {
        local php_version="$1"
        sudo apt remove -y $php_version $php_version-cli $php_version-fpm $php_version-mysql $php_version-curl $php_version-xml $php_version-mbstring $php_version-zip $php_version-bcmath
        if [ $? -eq 0 ]; then
            echo "PHP version '$php_version' removed successfully."
            log_message "PHP version '$php_version' removed successfully."
        else
            echo "Error: Failed to remove PHP version '$php_version'."
            log_message "Error: Failed to remove PHP version '$php_version'."
            exit 1
        fi
    }

    # Function to set PHP version for Apache
    set_php_version_apache() {
        local php_version="$1"
        if [[ -z $(update-alternatives --list php | grep $php_version) ]]; then
            echo "Error: PHP version '$php_version' not found."
            log_message "Error: PHP version '$php_version' not found."
            exit 1
        fi
        sudo a2dismod php*
        sudo a2enmod $php_version
        sudo systemctl restart apache2
        if [ $? -eq 0 ]; then
            echo "PHP version '$php_version' set for Apache successfully."
            log_message "PHP version '$php_version' set for Apache successfully."
        else
            echo "Error: Failed to set PHP version '$php_version' for Apache."
            log_message "Error: Failed to set PHP version '$php_version' for Apache."
            exit 1
        fi
    }

    # Function to set PHP version for Nginx
    set_php_version_nginx() {
        local php_version="$1"
        if [[ -z $(update-alternatives --list php | grep $php_version) ]]; then
            echo "Error: PHP version '$php_version' not found."
            log_message "Error: PHP version '$php_version' not found."
            exit 1
        fi
        sudo update-alternatives --set php /usr/bin/$php_version
        sudo systemctl restart php$php_version-fpm
        sudo systemctl restart nginx
        if [ $? -eq 0 ]; then
            echo "PHP version '$php_version' set for Nginx successfully."
            log_message "PHP version '$php_version' set for Nginx successfully."
        else
            echo "Error: Failed to set PHP version '$php_version' for Nginx."
            log_message "Error: Failed to set PHP version '$php_version' for Nginx."
            exit 1
        fi
    }

    # Parse arguments
    if [ $# -lt 2 ]; then
        usage
    fi

    if [ "$1" != "--format" ]; then
        usage
    fi

    OUTPUT_FORMAT=$2
    shift 2

    while [[ $# -gt 0 ]]; do
        key="$1"
        case $key in
        --set-default)
            NEW_DEFAULT_PHP_VERSION="$2"
            shift 2
            ;;
        --install)
            PHP_VERSION_TO_INSTALL="$2"
            shift 2
            ;;
        --remove)
            PHP_VERSION_TO_REMOVE="$2"
            shift 2
            ;;
        --set-apache)
            PHP_VERSION_FOR_APACHE="$2"
            shift 2
            ;;
        --set-nginx)
            PHP_VERSION_FOR_NGINX="$2"
            shift 2
            ;;
        *)
            usage
            ;;
        esac
    done

    # Install PHP version if requested
    if [ ! -z "$PHP_VERSION_TO_INSTALL" ]; then
        install_php_version "$PHP_VERSION_TO_INSTALL"
    fi

    # Remove PHP version if requested
    if [ ! -z "$PHP_VERSION_TO_REMOVE" ]; then
        remove_php_version "$PHP_VERSION_TO_REMOVE"
    fi

    # Set default PHP version if requested
    if [ ! -z "$NEW_DEFAULT_PHP_VERSION" ]; then
        set_default_php_version "$NEW_DEFAULT_PHP_VERSION"
    fi

    # Set PHP version for Apache if requested
    if [ ! -z "$PHP_VERSION_FOR_APACHE" ]; then
        set_php_version_apache "$PHP_VERSION_FOR_APACHE"
    fi

    # Set PHP version for Nginx if requested
    if [ ! -z "$PHP_VERSION_FOR_NGINX" ]; then
        set_php_version_nginx "$PHP_VERSION_FOR_NGINX"
    fi

    # List PHP versions
    list_php_versions
}
