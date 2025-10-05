# 🐳 Spikster Docker Guide

Complete guide voor het draaien van Spikster in Docker containers.

## 📋 Inhoud

-   [Snelstart](#snelstart)
-   [Vereisten](#vereisten)
-   [Configuratie](#configuratie)
-   [Docker Commands](#docker-commands)
-   [Development Workflow](#development-workflow)
-   [Production Deployment](#production-deployment)
-   [Troubleshooting](#troubleshooting)

---

## 🚀 Snelstart

### Development Environment

```bash
# 1. Kopieer environment bestand
cp .env.docker.example .env

# 2. Genereer applicatie key
docker compose run --rm app php artisan key:generate

# 3. Start containers
./docker.sh start
# of
make start

# 4. Run migraties
./docker.sh artisan migrate

# 5. Open de applicatie
open http://localhost:8000
```

### Beschikbare Services

| Service             | URL                   | Beschrijving            |
| ------------------- | --------------------- | ----------------------- |
| **Application**     | http://localhost:8000 | Spikster web interface  |
| **MailHog UI**      | http://localhost:8025 | Email testing interface |
| **phpMyAdmin**      | http://localhost:8080 | Database management     |
| **Redis Commander** | http://localhost:8081 | Redis management        |

---

## 📦 Vereisten

-   Docker Desktop 20.10+
-   Docker Compose V2
-   4GB vrij RAM minimum
-   10GB vrije disk ruimte

### Installatie Docker

**macOS:**

```bash
brew install --cask docker
```

**Windows:**
Download van [docker.com](https://www.docker.com/products/docker-desktop)

**Linux:**

```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
```

---

## ⚙️ Configuratie

### Environment Variables

Kopieer `.env.docker.example` naar `.env` en pas aan:

```bash
# Application
APP_NAME=Spikster
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=spikster
DB_USERNAME=spikster
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Mail (Development)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

### Docker Compose Profiles

**Development** (met debug tools):

```bash
docker compose --profile dev up -d
```

**Production** (minimal):

```bash
docker compose up -d
```

---

## 🛠️ Docker Commands

### Via Helper Script (`./docker.sh`)

```bash
# Container Management
./docker.sh build          # Build images
./docker.sh start          # Start containers
./docker.sh stop           # Stop containers
./docker.sh restart        # Restart containers

# Development
./docker.sh shell          # Open shell in app container
./docker.sh test           # Run test suite
./docker.sh logs [service] # View logs

# Laravel Artisan
./docker.sh artisan migrate
./docker.sh artisan db:seed
./docker.sh artisan cache:clear

# Composer & NPM
./docker.sh composer install
./docker.sh composer update
./docker.sh npm install
./docker.sh npm run dev
```

### Via Makefile

```bash
make build    # Build images
make start    # Start containers
make stop     # Stop containers
make test     # Run tests
make shell    # Open shell
make clean    # Clean up everything
```

### Direct Docker Compose

```bash
docker compose up -d        # Start background
docker compose down         # Stop and remove
docker compose ps           # List containers
docker compose logs -f app  # Follow app logs
docker compose exec app sh  # Shell in app container
```

---

## 💻 Development Workflow

### 1. Eerste Setup

```bash
# Clone repository
git clone https://github.com/yolanmees/Spikster.git
cd Spikster

# Setup environment
cp .env.docker.example .env

# Build en start
./docker.sh build
./docker.sh start

# Install dependencies
./docker.sh composer install
./docker.sh npm install

# Setup database
./docker.sh artisan key:generate
./docker.sh artisan migrate
./docker.sh artisan db:seed
```

### 2. Daily Development

```bash
# Start work
./docker.sh start

# Run migrations
./docker.sh artisan migrate

# Run tests
./docker.sh test

# Watch assets
./docker.sh npm run watch

# Stop work
./docker.sh stop
```

### 3. Testing

```bash
# All tests
./docker.sh test

# Specific test file
./docker.sh test tests/Feature/Auth/LoginTest.php

# With coverage
./docker.sh test --coverage

# PHPUnit directly
./docker.sh phpunit --filter testLogin
```

### 4. Database Access

```bash
# MySQL CLI
./docker.sh mysql

# phpMyAdmin
open http://localhost:8080

# Migrations
./docker.sh artisan migrate
./docker.sh artisan migrate:fresh --seed

# Backup
docker compose exec mysql mysqldump -u spikster -psecret spikster > backup.sql

# Restore
docker compose exec -T mysql mysql -u spikster -psecret spikster < backup.sql
```

### 5. Redis Access

```bash
# Redis CLI
./docker.sh redis

# Redis Commander UI
open http://localhost:8081

# Flush cache
./docker.sh artisan cache:clear
```

---

## 🚀 Production Deployment

### 1. Production Build

```bash
# Build production image
docker build -f Dockerfile -t spikster:latest .

# Or with docker-compose
docker compose -f docker-compose.prod.yml build
```

### 2. Environment Setup

Maak `.env.production`:

```bash
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...  # Generate via: php artisan key:generate --show

DB_HOST=your-db-host
DB_DATABASE=spikster_prod
DB_USERNAME=spikster_prod
DB_PASSWORD=strong-password-here

REDIS_HOST=your-redis-host
REDIS_PASSWORD=redis-password-here

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=email-password
MAIL_ENCRYPTION=tls
```

### 3. Deploy

```bash
# Start production
docker compose -f docker-compose.prod.yml up -d

# Run migrations
docker compose exec app php artisan migrate --force

# Optimize
docker compose exec app php artisan optimize
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

### 4. SSL/TLS Setup

Voor HTTPS configuratie:

```bash
# Install certbot in container
docker compose exec app apk add certbot

# Generate certificate
docker compose exec app certbot certonly --webroot \
    -w /var/www/html/public \
    -d your-domain.com
```

Of gebruik een reverse proxy zoals Traefik of Nginx Proxy Manager.

---

## 🔧 Troubleshooting

### Container start niet

```bash
# Check logs
docker compose logs app

# Check all container status
docker compose ps

# Rebuild without cache
./docker.sh rebuild
```

### Permission errors

```bash
# Fix permissions
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### Database connection error

```bash
# Check if MySQL is running
docker compose ps mysql

# Check MySQL logs
docker compose logs mysql

# Wait for MySQL to be ready
docker compose up -d mysql
sleep 10
./docker.sh artisan migrate
```

### Port already in use

```bash
# Check what's using the port
lsof -i :8000

# Change port in .env
APP_PORT=8001

# Restart
./docker.sh restart
```

### Slow performance (macOS)

```bash
# Use :cached or :delegated for volumes
# Already configured in docker-compose.yml

# Or increase Docker Desktop resources:
# Docker Desktop → Preferences → Resources
# - CPUs: 4+
# - Memory: 4GB+
# - Swap: 2GB+
```

### Clear everything and start fresh

```bash
# Nuclear option - removes ALL data
./docker.sh clean

# Rebuild from scratch
./docker.sh build
./docker.sh start
./docker.sh artisan migrate:fresh --seed
```

### Tests failing in Docker

```bash
# Ensure test database exists
docker compose exec mysql mysql -u root -prootsecret -e "CREATE DATABASE IF NOT EXISTS spikster_test"

# Run migrations for test database
./docker.sh artisan migrate --env=testing

# Run tests
./docker.sh test
```

---

## 📊 Performance Tuning

### PHP-FPM Optimization

Edit `docker/php/www.conf`:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
```

### MySQL Optimization

Edit `docker/mysql/my.cnf`:

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
```

### Redis Optimization

```bash
# In docker-compose.yml
command: >
  redis-server
  --maxmemory 512mb
  --maxmemory-policy allkeys-lru
```

---

## 🔍 Monitoring

### Container Stats

```bash
# Real-time stats
docker stats

# Via docker.sh
./docker.sh stats
```

### Logs

```bash
# All logs
docker compose logs -f

# Specific service
docker compose logs -f app
docker compose logs -f mysql

# Last 100 lines
docker compose logs --tail=100 app
```

### Health Checks

```bash
# Check application health
curl http://localhost:8000/up

# Check all container health
docker compose ps
```

---

## 🎯 Best Practices

1. **Use .env files** - Never commit sensitive data
2. **Tag images** - Version your Docker images
3. **Health checks** - Always include health checks
4. **Logging** - Centralize logs
5. **Backups** - Regular database backups
6. **Security** - Keep images updated
7. **Resources** - Set resource limits
8. **Networks** - Use custom networks

---

## 📚 Additional Resources

-   [Docker Documentation](https://docs.docker.com/)
-   [Docker Compose Documentation](https://docs.docker.com/compose/)
-   [Laravel Sail](https://laravel.com/docs/sail) (inspiration)
-   [PHP-FPM Configuration](https://www.php.net/manual/en/install.fpm.php)
-   [Nginx Configuration](https://nginx.org/en/docs/)

---

## 🆘 Support

Voor hulp:

1. Check deze documentatie
2. Bekijk `docker compose logs`
3. Open een issue op GitHub
4. Contact het development team

---

**Last Updated:** 3 October 2025  
**Docker Version:** 24.0+  
**Docker Compose Version:** v2.20+
