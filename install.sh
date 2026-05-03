#!/bin/bash
set -e

# =============================================================================
# Spikster — One-Command Install
# =============================================================================
# Usage: curl -sL https://raw.githubusercontent.com/yolanmees/Spikster/v2-update/install.sh | bash
# Or:   bash install.sh
# =============================================================================

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
log()  { echo -e "${GREEN}[✓]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }
err()  { echo -e "${RED}[✗]${NC} $1"; exit 1; }
info() { echo -e "${BLUE}[i]${NC} $1"; }

# Detect IP
IP=$(curl -s -4 ifconfig.me 2>/dev/null || curl -s -4 icanhazip.com 2>/dev/null || hostname -I | awk '{print $1}')
[ -z "$IP" ] && IP="127.0.0.1"

# Random passwords
DB_ROOT_PASS=$(openssl rand -base64 24 | tr -dc a-z0-9 | head -c 30)
DB_SPIKSTER_PASS=$(openssl rand -base64 24 | tr -dc a-z0-9 | head -c 30)
DAEMON_TOKEN=$(openssl rand -hex 32)

APP_DIR="/var/www/spikster"

# ─────────────────────────────────────────────────────────────────────────────
# 0. Swap (needed on 1GB droplets for composer)
# ─────────────────────────────────────────────────────────────────────────────
if [ ! -f /swapfile ]; then
    fallocate -l 2G /swapfile 2>/dev/null || dd if=/dev/zero of=/swapfile bs=1M count=2048 2>/dev/null
    chmod 600 /swapfile
    mkswap /swapfile >/dev/null
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

# ─────────────────────────────────────────────────────────────────────────────
# 1. System packages
# ─────────────────────────────────────────────────────────────────────────────
info "Installing system packages..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq software-properties-common curl gnupg ca-certificates lsb-release >/dev/null 2>&1

# Detect best available PHP version (prefer 8.4, fall back to 8.3, then sury for 8.5)
DISTRO_CODENAME=$(lsb_release -sc)
PHP_PKG_VER=""
# Try native repos first (Ubuntu 25.10+ has 8.4)
for VER in 8.4 8.3; do
    if apt-cache show php${VER}-fpm >/dev/null 2>&1; then
        PHP_PKG_VER=$VER
        info "Using PHP ${PHP_PKG_VER} from native repos"
        break
    fi
done
# If not found natively, try sury.org (22.04 jammy → 8.5)
if [ -z "$PHP_PKG_VER" ]; then
    SURY_CODENAMES="jammy noble resolute"
    if echo "$SURY_CODENAMES" | grep -qw "$DISTRO_CODENAME"; then
        curl -fsSL https://packages.sury.org/php/apt.gpg | gpg --yes --dearmor -o /usr/share/keyrings/sury-php.gpg 2>/dev/null
        echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ ${DISTRO_CODENAME} main" > /etc/apt/sources.list.d/sury-php.list
        apt-get update -qq
        for VER in 8.5 8.4 8.3; do
            if apt-cache show php${VER}-fpm >/dev/null 2>&1; then
                PHP_PKG_VER=$VER
                info "Using PHP ${PHP_PKG_VER} from sury.org"
                break
            fi
        done
    fi
fi
[ -z "$PHP_PKG_VER" ] && err "Could not find PHP 8.3+ in any repo for ${DISTRO_CODENAME}"
PHP_SOCK_VER=$PHP_PKG_VER

apt-get install -y -qq nginx mysql-server redis-server git curl wget unzip openssl expect >/dev/null 2>&1
apt-get install -y -qq php${PHP_PKG_VER}-fpm php${PHP_PKG_VER}-cli php${PHP_PKG_VER}-mysql php${PHP_PKG_VER}-zip php${PHP_PKG_VER}-gd php${PHP_PKG_VER}-mbstring php${PHP_PKG_VER}-curl php${PHP_PKG_VER}-xml php${PHP_PKG_VER}-bcmath php${PHP_PKG_VER}-intl >/dev/null 2>&1
# php-redis: available as package or via PECL
if apt-cache show php${PHP_PKG_VER}-redis >/dev/null 2>&1; then
    apt-get install -y -qq php${PHP_PKG_VER}-redis >/dev/null 2>&1 || true
else
    apt-get install -y -qq php${PHP_PKG_VER}-dev php-pear >/dev/null 2>&1 || true
    pecl install redis >/dev/null 2>&1 || true
    echo "extension=redis.so" > /etc/php/${PHP_PKG_VER}/mods-available/redis.ini
    phpenmod redis 2>/dev/null || true
fi
log "System packages installed (PHP ${PHP_PKG_VER})"

# Start services
systemctl start mysql redis php${PHP_PKG_VER}-fpm 2>/dev/null || true
systemctl enable mysql redis php${PHP_PKG_VER}-fpm 2>/dev/null || true

# ─────────────────────────────────────────────────────────────────────────────
# 2. MySQL setup
# ─────────────────────────────────────────────────────────────────────────────
info "Configuring MySQL..."
# Switch MySQL root to native password
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${DB_ROOT_PASS}'; FLUSH PRIVILEGES;" 2>/dev/null || true
# Create spikster database + user
mysql -u root -p"${DB_ROOT_PASS}" -e "CREATE DATABASE IF NOT EXISTS spikster CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
mysql -u root -p"${DB_ROOT_PASS}" -e "CREATE USER IF NOT EXISTS 'spikster'@'localhost' IDENTIFIED BY '${DB_SPIKSTER_PASS}'; GRANT ALL ON spikster.* TO 'spikster'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null
log "MySQL configured"

# ─────────────────────────────────────────────────────────────────────────────
# 3. Clone / update application
# ─────────────────────────────────────────────────────────────────────────────
info "Setting up Spikster..."
rm -rf ${APP_DIR}
git clone --branch v2-update https://github.com/yolanmees/Spikster.git ${APP_DIR} 2>/dev/null
cd ${APP_DIR}
log "Repository cloned"

# ─────────────────────────────────────────────────────────────────────────────
# 4. Composer install (with module workaround)
# ─────────────────────────────────────────────────────────────────────────────
info "Installing Composer dependencies..."
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" 2>/dev/null
php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer 2>/dev/null
rm -f composer-setup.php

# Disable modules temporarily to avoid package:discover crashes
echo '{"WordPress": false}' > modules_statuses.json
COMPOSER_ALLOW_SUPPRESS_POST_SCRIPTS=1 composer install --no-interaction --no-dev 2>/dev/null
# Re-enable WordPress module after autoloader is built
echo '{"WordPress": true}' > modules_statuses.json
log "Composer dependencies installed"

# ─────────────────────────────────────────────────────────────────────────────
# 5. Environment configuration
# ─────────────────────────────────────────────────────────────────────────────
info "Configuring environment..."
cp .env.example .env
sed -i "s/APP_ENV=.*/APP_ENV=production/" .env
sed -i "s|APP_URL=.*|APP_URL=http://${IP}|" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=spikster/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_SPIKSTER_PASS}/" .env
sed -i "s/DB_ROOT_PASS=.*/DB_ROOT_PASS=${DB_ROOT_PASS}/" .env 2>/dev/null || true
sed -i "s/SPIKSTER_DAEMON_TOKEN=.*/SPIKSTER_DAEMON_TOKEN=${DAEMON_TOKEN}/" .env
sed -i "s/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=database/" .env
sed -i "s/APP_DEBUG=true/APP_DEBUG=false/" .env
sed -i "s/BROADCAST_DRIVER=.*/BROADCAST_DRIVER=reverb/" .env 2>/dev/null || echo "BROADCAST_DRIVER=reverb" >> .env
cat >> .env << ENVEOF

REVERB_APP_ID=spikster
REVERB_APP_KEY=spikster-key
REVERB_APP_SECRET=spikster-secret
REVERB_HOST=${IP}
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY=spikster-key
VITE_REVERB_HOST=${IP}
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
ENVEOF

# MySQL root password for daemon
echo "" >> .env
echo "DB_ROOT_PASS=${DB_ROOT_PASS}" >> .env

php artisan key:generate --force 2>/dev/null
log "Environment configured"

# ─────────────────────────────────────────────────────────────────────────────
# 6. Laravel setup
# ─────────────────────────────────────────────────────────────────────────────
info "Running Laravel setup..."
php artisan vendor:publish --tag=sanctum-config --force 2>/dev/null
php artisan storage:link 2>/dev/null
php artisan migrate --seed --force 2>/dev/null
log "Database migrated and seeded"

# ─────────────────────────────────────────────────────────────────────────────
# 7. Nginx
# ─────────────────────────────────────────────────────────────────────────────
info "Configuring Nginx..."
cat > /etc/nginx/sites-enabled/spikster-panel << 'NGINX'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /var/www/spikster/public;
    index index.php index.html;
    location / { try_files $uri /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php${PHP_SOCK_VER}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.(?!well-known).* { deny all; }
    location ~ \.env { deny all; }
    client_max_body_size 100M;
}
NGINX
rm -f /etc/nginx/sites-enabled/default
nginx -t 2>/dev/null && systemctl reload nginx
log "Nginx configured"

# ─────────────────────────────────────────────────────────────────────────────
# 8. Spikster daemon
# ─────────────────────────────────────────────────────────────────────────────
info "Installing Spikster daemon..."
cp ${APP_DIR}/daemon/spikster-daemon /usr/local/bin/spikster
chmod +x /usr/local/bin/spikster

# Daemon token file
mkdir -p /etc/spikster
echo -n "${DAEMON_TOKEN}" > /etc/spikster/daemon.token
chmod 600 /etc/spikster/daemon.token

echo -n "${DB_ROOT_PASS}" > /etc/spikster/db.pass
chmod 600 /etc/spikster/db.pass
chmod 600 /etc/spikster/daemon.token

# Systemd service
cp ${APP_DIR}/daemon/systemd/spikster-daemon.service /etc/systemd/system/
systemctl daemon-reload
systemctl enable spikster-daemon 2>/dev/null
systemctl start spikster-daemon 2>/dev/null
log "Spikster daemon running"

# ─────────────────────────────────────────────────────────────────────────────
# 9. Spikster monitoring agent
# ─────────────────────────────────────────────────────────────────────────────
info "Installing monitoring agent..."
cp ${APP_DIR}/spikster-agent/spikster-agent /usr/local/bin/spikster-agent
chmod +x /usr/local/bin/spikster-agent
id -u spikster &>/dev/null || useradd -r -s /bin/false spikster
cp ${APP_DIR}/spikster-agent/spikster-agent.service /etc/systemd/system/
systemctl daemon-reload
systemctl enable spikster-agent 2>/dev/null
systemctl start spikster-agent 2>/dev/null
log "Monitoring agent running (port 9273)"

# ─────────────────────────────────────────────────────────────────────────────
# 10. Queue worker
# ─────────────────────────────────────────────────────────────────────────────
info "Setting up queue worker..."
cat > /etc/systemd/system/spikster-queue.service << 'QEOF'
[Unit]
Description=Spikster Queue Worker
After=network.target mysql.service

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/spikster
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --timeout=120
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
QEOF
systemctl daemon-reload
systemctl enable spikster-queue 2>/dev/null
systemctl start spikster-queue 2>/dev/null
log "Queue worker running"

# Allow www-data to run fail2ban-client via sudo (for status checks)
echo "www-data ALL=(root) NOPASSWD: /usr/bin/fail2ban-client status*" > /etc/sudoers.d/spikster-fail2ban
chmod 440 /etc/sudoers.d/spikster-fail2ban

# ─────────────────────────────────────────────────────────────────────────────
# 11. Reverb WebSocket server
# ─────────────────────────────────────────────────────────────────────────────
info "Setting up Reverb WebSocket..."
cat > /etc/systemd/system/spikster-reverb.service << 'REOF'
[Unit]
Description=Spikster Reverb WebSocket
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/spikster
ExecStart=/usr/bin/php artisan reverb:start --host=0.0.0.0 --port=8080
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
REOF
systemctl daemon-reload
systemctl enable spikster-reverb 2>/dev/null
systemctl start spikster-reverb 2>/dev/null
log "Reverb WebSocket running (port 8080)"

# ─────────────────────────────────────────────────────────────────────────────
# 11. Laravel scheduler cron
# ─────────────────────────────────────────────────────────────────────────────
info "Setting up metrics scheduler..."
echo "* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/spikster
chmod 644 /etc/cron.d/spikster
log "Metrics collection scheduled (every minute)"

# ─────────────────────────────────────────────────────────────────────────────
# 11. Cache & permissions
# ─────────────────────────────────────────────────────────────────────────────
php artisan config:cache 2>/dev/null
php artisan route:cache 2>/dev/null
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ─────────────────────────────────────────────────────────────────────────────
# 10. Generate setup URL
# ─────────────────────────────────────────────────────────────────────────────
SETUP_TOKEN=$(php artisan spikster:setup-token 2>/dev/null | grep -o 'http.*' | head -1)
[ -z "$SETUP_TOKEN" ] && SETUP_TOKEN="http://${IP}/setup/$(php artisan tinker --execute='echo App\Http\Controllers\SetupController::generateToken();' 2>/dev/null)"

# ─────────────────────────────────────────────────────────────────────────────
# Done!
# ─────────────────────────────────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║                 SPIKSTER INSTALLATION COMPLETE              ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""
echo "  Panel URL:    http://${IP}"
echo "  Setup URL:    ${SETUP_TOKEN}"
echo ""
echo "  MySQL root:   root / ${DB_ROOT_PASS}"
echo "  MySQL user:   spikster / ${DB_SPIKSTER_PASS}"
echo ""
echo "  1. Open the Setup URL in your browser"
echo "  2. Create your admin account"
echo "  3. Done — server is in the list, daemon is running"
echo ""
echo "  Save these credentials somewhere safe!"
echo ""
