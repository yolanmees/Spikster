#!/bin/bash
# Roundcube Webmail Installation Script
# This script installs and configures Roundcube webmail
# Usage: Run via SSH job for each site

set -e

DOMAIN="$1"
SITE_ROOT="$2"
DB_NAME="$3"
DB_USER="$4"
DB_PASS="$5"

WEBMAIL_DIR="${SITE_ROOT}/webmail"
ROUNDCUBE_VERSION="1.6.5"
DOWNLOAD_URL="https://github.com/roundcube/roundcubemail/releases/download/${ROUNDCUBE_VERSION}/roundcubemail-${ROUNDCUBE_VERSION}-complete.tar.gz"

echo "Installing Roundcube ${ROUNDCUBE_VERSION} for ${DOMAIN}..."

# Create webmail directory
mkdir -p "$WEBMAIL_DIR"
cd "$WEBMAIL_DIR"

# Download Roundcube
echo "Downloading Roundcube..."
wget -q "$DOWNLOAD_URL" -O roundcube.tar.gz

# Extract
echo "Extracting Roundcube..."
tar -xzf roundcube.tar.gz --strip-components=1
rm roundcube.tar.gz

# Create Roundcube database
echo "Creating Roundcube database..."
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME}_roundcube CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON ${DB_NAME}_roundcube.* TO '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
FLUSH PRIVILEGES;
EOF

# Import Roundcube database schema
echo "Importing database schema..."
mysql -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}_roundcube" < SQL/mysql.initial.sql

# Create config.inc.php
echo "Creating Roundcube configuration..."
cat > config/config.inc.php <<'EOFCONFIG'
<?php

$config = [];

// Database connection
$config['db_dsnw'] = 'mysql://DB_USER:DB_PASS@localhost/DB_NAME_roundcube';

// IMAP connection
$config['default_host'] = 'ssl://localhost';
$config['default_port'] = 993;
$config['imap_auth_type'] = 'LOGIN';
$config['imap_delimiter'] = '/';

// SMTP connection
$config['smtp_server'] = 'tls://localhost';
$config['smtp_port'] = 587;
$config['smtp_user'] = '%u';
$config['smtp_pass'] = '%p';
$config['smtp_auth_type'] = 'LOGIN';

// Security settings
$config['des_key'] = 'DES_KEY_PLACEHOLDER';
$config['cipher_method'] = 'AES-256-CBC';
$config['useragent'] = 'Roundcube Webmail';

// Product name
$config['product_name'] = 'DOMAIN Webmail';

// Disable installer
$config['enable_installer'] = false;

// Plugins
$config['plugins'] = [
    'archive',
    'zipdownload',
    'markasjunk',
    'managesieve',
    'password',
];

// Password plugin config
$config['password_driver'] = 'sql';
$config['password_db_dsn'] = 'mysql://DB_USER:DB_PASS@localhost/DB_NAME';
$config['password_query'] = 'UPDATE email_accounts SET password = %c WHERE email = %u';
$config['password_crypt_hash'] = 'bcrypt';
$config['password_algorithm'] = 'bcrypt';
$config['password_blowfish_cost'] = 12;

// ManageSieve plugin config (for vacation messages)
$config['managesieve_host'] = 'localhost';
$config['managesieve_port'] = 4190;
$config['managesieve_auth_type'] = 'LOGIN';
$config['managesieve_usetls'] = false;

// Skin
$config['skin'] = 'elastic';

// Language
$config['language'] = 'en_US';
$config['auto_create_user'] = true;

// Session
$config['session_lifetime'] = 30;
$config['session_domain'] = 'DOMAIN';

// Misc
$config['support_url'] = '';
$config['enable_spellcheck'] = true;
$config['spellcheck_engine'] = 'pspell';
$config['identities_level'] = 0;
$config['draft_autosave'] = 300;

EOFCONFIG

# Replace placeholders
sed -i "s/DB_USER/${DB_USER}/g" config/config.inc.php
sed -i "s/DB_PASS/${DB_PASS}/g" config/config.inc.php
sed -i "s/DB_NAME/${DB_NAME}/g" config/config.inc.php
sed -i "s/DOMAIN/${DOMAIN}/g" config/config.inc.php

# Generate DES key
DES_KEY=$(openssl rand -base64 24)
sed -i "s/DES_KEY_PLACEHOLDER/${DES_KEY}/g" config/config.inc.php

# Set permissions
echo "Setting permissions..."
chown -R www-data:www-data "$WEBMAIL_DIR"
chmod -R 755 "$WEBMAIL_DIR"
chmod -R 777 "${WEBMAIL_DIR}/temp" "${WEBMAIL_DIR}/logs"

# Install Composer dependencies
echo "Installing Composer dependencies..."
cd "$WEBMAIL_DIR"
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader
fi

# Update plugins
echo "Updating plugins..."
cd "${WEBMAIL_DIR}/plugins/managesieve"
if [ -f "composer.json" ]; then
    composer install --no-dev
fi

echo "Roundcube installation completed successfully!"
echo "Access webmail at: https://${DOMAIN}/webmail"
