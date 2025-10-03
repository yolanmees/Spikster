# 🐳 Spikster Docker Guide

Complete guide for running Spikster in Docker containers with production-ready configuration.

## 📋 Table of Contents

- [Quick Start](#quick-start)
- [Requirements](#requirements)
- [Configuration](#configuration)
- [Docker Commands](#docker-commands)
- [Development Workflow](#development-workflow)
- [Production Deployment](#production-deployment)
- [Troubleshooting](#troubleshooting)
- [Performance Tuning](#performance-tuning)
- [Security](#security)
- [Monitoring](#monitoring)

---

## 🚀 Quick Start

### Development Environment

```bash
# 1. Copy environment file
cp .env.docker.example .env

# 2. Generate application key
docker compose run --rm app php artisan key:generate

# 3. Start containers
./docker.sh start
# or
make start

# 4. Run migrations
./docker.sh artisan migrate --seed

# 5. Open the application
open http://localhost:8080
```

### Available Services

| Service | URL | Description |
|---------|-----|-------------|
| **Application** | http://localhost:8080 | Spikster web interface |
| **MailHog UI** | http://localhost:8025 | Email testing interface |
| **MySQL** | localhost:3306 | Database server |
| **Redis** | localhost:6379 | Cache & queue backend |

---

## 📦 Requirements

### System Requirements
- **Docker Desktop:** 4.0+ (includes Docker Compose V2)
- **RAM:** Minimum 4GB available for Docker
- **Disk Space:** Minimum 10GB free
- **OS:** macOS, Linux, or Windows with WSL2

### Docker Installation
```bash
# macOS (via Homebrew)
brew install --cask docker

# Ubuntu/Debian
sudo apt-get update
sudo apt-get install docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Verify installation
docker --version
docker compose version
```

---

## ⚙️ Configuration

### Environment Variables

Copy the example environment file and configure:

```bash
cp .env.docker.example .env
```

Key variables to configure:

```env
# Application
APP_NAME=Spikster
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=spikster
DB_USERNAME=spikster
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (MailHog for development)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=noreply@spikster.test
```

### Port Configuration

Default ports (can be changed in `.env`):

```env
APP_PORT=8080          # Application HTTP
MYSQL_PORT=3306        # MySQL database
REDIS_PORT=6379        # Redis
MAILHOG_PORT=8025      # MailHog UI
MAILHOG_SMTP_PORT=1025 # MailHog SMTP
```

---

## 🐋 Docker Commands

### Using docker.sh Helper Script

```bash
# Build Docker images
./docker.sh build

# Start all containers
./docker.sh start

# Stop all containers
./docker.sh stop

# Restart containers
./docker.sh restart

# View logs
./docker.sh logs

# Follow logs
./docker.sh logs -f

# Open shell in app container
./docker.sh shell

# Run artisan command
./docker.sh artisan [command]

# Run tests
./docker.sh test

# Composer commands
./docker.sh composer [command]

# NPM commands
./docker.sh npm [command]

# Database shell
./docker.sh mysql

# Redis CLI
./docker.sh redis
```

### Using Docker Compose Directly

```bash
# Build images
docker compose build

# Start services in background
docker compose up -d

# Stop services
docker compose down

# View logs
docker compose logs -f [service]

# Execute command in container
docker compose exec app [command]

# Restart specific service
docker compose restart app

# View running containers
docker compose ps

# Remove containers and volumes
docker compose down -v
```

### Using Makefile

```bash
# Build images
make build

# Start containers
make start

# Stop containers
make stop

# Run tests
make test

# Open shell
make shell

# View logs
make logs

# Clean everything
make clean
```

---

## 💻 Development Workflow

### Daily Development

```bash
# Morning - Start environment
./docker.sh start

# Check service status
docker compose ps

# View application logs
docker compose logs -f app

# Run migrations after pulling changes
./docker.sh artisan migrate

# Clear cache
./docker.sh artisan cache:clear
./docker.sh artisan config:clear
./docker.sh artisan view:clear

# Run tests
./docker.sh test

# Evening - Stop environment
./docker.sh stop
```

### Code Changes

Docker is configured with volume mounting, so:
- ✅ PHP code changes are **immediately reflected**
- ✅ View changes are **immediately visible**
- ✅ Config changes require `artisan config:clear`
- ✅ Route changes require `artisan route:clear`
- ⚠️ Composer changes require `./docker.sh composer install`
- ⚠️ NPM changes require `./docker.sh npm install`

### Database Management

```bash
# Run migrations
./docker.sh artisan migrate

# Rollback last migration
./docker.sh artisan migrate:rollback

# Fresh migration with seed
./docker.sh artisan migrate:fresh --seed

# Create new migration
./docker.sh artisan make:migration create_table_name

# Open MySQL shell
./docker.sh mysql

# Backup database
docker compose exec mysql mysqldump -u spikster -psecret spikster > backup.sql

# Restore database
docker compose exec -T mysql mysql -u spikster -psecret spikster < backup.sql
```

### Queue Management

```bash
# View queue logs
docker compose logs -f queue

# Restart queue worker
docker compose restart queue

# Run queue manually
./docker.sh artisan queue:work

# Monitor failed jobs
./docker.sh artisan queue:failed

# Retry failed job
./docker.sh artisan queue:retry [id]
```

### Asset Compilation

```bash
# Install NPM dependencies
./docker.sh npm install

# Build assets for development
./docker.sh npm run dev

# Build assets for production
./docker.sh npm run build

# Watch for changes (hot reload)
./docker.sh npm run dev -- --watch
```

---

## 🚀 Production Deployment

### Build Production Image

```bash
# Build optimized production image
docker build -f Dockerfile -t spikster:1.0.0 .

# Tag for registry
docker tag spikster:1.0.0 your-registry.com/spikster:1.0.0

# Push to registry
docker push your-registry.com/spikster:1.0.0
```

### Production Docker Compose

Use `docker-compose.prod.yml`:

```bash
# Start production stack
docker compose -f docker-compose.prod.yml up -d

# With dev tools disabled
docker compose -f docker-compose.prod.yml up -d --scale mailhog=0

# With profiles (recommended)
docker compose -f docker-compose.prod.yml --profile prod up -d
```

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate strong `APP_KEY`
- [ ] Configure real database credentials
- [ ] Set up SSL/TLS certificates
- [ ] Configure proper mail server
- [ ] Enable OPcache (`opcache.validate_timestamps=0`)
- [ ] Set up log rotation
- [ ] Configure backup strategy
- [ ] Set up monitoring and alerts
- [ ] Review security headers
- [ ] Configure firewall rules
- [ ] Set resource limits
- [ ] Enable rate limiting

### Production Optimizations

```bash
# Optimize composer autoloader
./docker.sh composer install --optimize-autoloader --no-dev

# Cache configuration
./docker.sh artisan config:cache

# Cache routes
./docker.sh artisan route:cache

# Cache views
./docker.sh artisan view:cache

# Optimize
./docker.sh artisan optimize
```

---

## 🔧 Troubleshooting

### Common Issues

#### Port Already in Use

**Error:** `Bind for 0.0.0.0:8080 failed: port is already allocated`

**Solution:**
```bash
# Find process using port
lsof -i :8080

# Kill the process
kill -9 [PID]

# Or change port in .env
APP_PORT=8081
```

#### Permission Denied

**Error:** `Permission denied` when accessing files

**Solution:**
```bash
# Fix permissions in container
docker compose exec app chown -R nginx:nginx /var/www/html/storage
docker compose exec app chmod -R 775 /var/www/html/storage
```

#### Database Connection Failed

**Error:** `SQLSTATE[HY000] [2002] Connection refused`

**Solution:**
```bash
# Wait for MySQL to be ready
docker compose up -d mysql
sleep 30

# Check MySQL health
docker compose exec mysql mysqladmin ping -h localhost -u root -psecret

# Restart app
docker compose restart app
```

#### Out of Disk Space

**Error:** `no space left on device`

**Solution:**
```bash
# Clean up Docker resources
docker system prune -a --volumes

# Remove old images
docker image prune -a

# Remove unused volumes
docker volume prune
```

#### Container Keeps Restarting

**Problem:** Container shows "Restarting" status

**Solution:**
```bash
# Check logs
docker compose logs [service]

# Check specific errors
docker compose logs app | tail -50

# Inspect container
docker inspect spikster-app

# Try running container interactively
docker compose run --rm app sh
```

#### Build Fails

**Error:** Build errors during `docker compose build`

**Solution:**
```bash
# Build without cache
docker compose build --no-cache

# Build with verbose output
docker compose build --progress=plain

# Check Dockerfile syntax
docker build -f Dockerfile.dev .
```

### Health Checks

```bash
# Check all container status
docker compose ps

# Check app health endpoint
curl http://localhost:8080/up

# Check MySQL
docker compose exec mysql mysqladmin ping -h localhost -u root -psecret

# Check Redis
docker compose exec redis redis-cli ping

# Check Nginx
docker compose exec nginx nginx -t

# Check PHP-FPM
docker compose exec app php-fpm -t
```

### Logs and Debugging

```bash
# View all logs
docker compose logs

# Follow specific service logs
docker compose logs -f app

# Last 100 lines
docker compose logs --tail=100 app

# Application logs
docker compose exec app tail -f storage/logs/laravel.log

# Nginx access log
docker compose exec app tail -f /var/log/nginx/access.log

# Nginx error log
docker compose exec app tail -f /var/log/nginx/error.log

# PHP-FPM log
docker compose exec app tail -f /var/log/php-fpm.log

# MySQL logs
docker compose logs mysql

# Supervisor logs
docker compose exec app tail -f /var/log/supervisor/supervisord.log
```

---

## ⚡ Performance Tuning

### PHP Configuration

Edit `docker/php/production.ini`:

```ini
memory_limit = 512M                    # Increase for heavy operations
max_execution_time = 300               # Timeout for long scripts
upload_max_filesize = 256M             # Max upload size
post_max_size = 256M                   # Max POST size
realpath_cache_size = 4096K            # Path cache
realpath_cache_ttl = 600               # Cache TTL
```

### OPcache Configuration

Edit `docker/php/opcache.ini`:

```ini
opcache.enable = 1
opcache.memory_consumption = 256       # Increase for larger apps
opcache.max_accelerated_files = 20000  # More files
opcache.validate_timestamps = 0        # Disable in production
opcache.jit = tracing                  # Enable JIT
opcache.jit_buffer_size = 100M         # JIT memory
```

### PHP-FPM Pool

Edit `docker/php/www.conf`:

```ini
pm = dynamic                           # Process manager
pm.max_children = 50                   # Max processes
pm.start_servers = 10                  # Initial processes
pm.min_spare_servers = 5               # Min idle
pm.max_spare_servers = 20              # Max idle
pm.max_requests = 500                  # Recycle after requests
```

### MySQL Optimization

Edit `docker/mysql/my.cnf`:

```ini
innodb_buffer_pool_size = 256M         # Main buffer
innodb_log_file_size = 64M             # Log size
max_connections = 200                  # Connection limit
query_cache_size = 32M                 # Query cache
```

### Redis Configuration

```bash
# Set max memory
docker compose exec redis redis-cli CONFIG SET maxmemory 256mb

# Set eviction policy
docker compose exec redis redis-cli CONFIG SET maxmemory-policy allkeys-lru
```

### Nginx Optimization

Edit `docker/nginx/production.conf`:

```nginx
# Worker processes (= CPU cores)
worker_processes auto;

# Worker connections
events {
    worker_connections 1024;
}

# Gzip compression
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_comp_level 6;
gzip_types text/plain text/css application/json application/javascript;

# Client body buffer
client_body_buffer_size 128k;
client_max_body_size 256M;

# Timeouts
client_body_timeout 60s;
client_header_timeout 60s;
keepalive_timeout 65s;
send_timeout 60s;

# FastCGI cache
fastcgi_cache_path /tmp/nginx_cache levels=1:2 keys_zone=FASTCGI:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";
```

---

## 🔒 Security

### Security Headers

Already configured in `docker/nginx/production.conf`:

```nginx
# Prevent clickjacking
add_header X-Frame-Options "SAMEORIGIN";

# Prevent MIME sniffing
add_header X-Content-Type-Options "nosniff";

# XSS Protection
add_header X-XSS-Protection "1; mode=block";

# HSTS
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";

# Referrer Policy
add_header Referrer-Policy "strict-origin-when-cross-origin";

# Permissions Policy
add_header Permissions-Policy "geolocation=(), microphone=(), camera=()";

# CSP
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';";
```

### SSL/TLS Configuration

For production, add SSL certificates:

```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # ... rest of config
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

### Environment Security

```bash
# Never commit these files
.env
.env.production

# Use strong passwords
openssl rand -base64 32

# Rotate credentials regularly
# Update DB_PASSWORD, REDIS_PASSWORD, etc.

# Limit container privileges
docker compose exec --user nginx app [command]
```

### Database Security

```bash
# Create separate MySQL users per environment
CREATE USER 'spikster_prod'@'%' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON spikster.* TO 'spikster_prod'@'%';
FLUSH PRIVILEGES;

# Never use root in production
# Remove test databases and users
```

---

## 📊 Monitoring

### Resource Usage

```bash
# Real-time container stats
docker stats

# Specific container
docker stats spikster-app

# Check disk usage
docker system df

# Check image sizes
docker images --format "table {{.Repository}}\t{{.Tag}}\t{{.Size}}"
```

### Application Monitoring

```bash
# Health check endpoint
curl http://localhost:8080/up

# PHP-FPM status
curl http://localhost/status

# PHP-FPM ping
curl http://localhost/ping

# Application logs
docker compose exec app tail -f storage/logs/laravel.log

# Error count
docker compose exec app grep "ERROR" storage/logs/laravel.log | wc -l
```

### Performance Monitoring

```bash
# Check OPcache status
docker compose exec app php -r "print_r(opcache_get_status());"

# Check Redis stats
docker compose exec redis redis-cli INFO stats

# Check MySQL performance
docker compose exec mysql mysql -u root -psecret -e "SHOW PROCESSLIST;"
docker compose exec mysql mysql -u root -psecret -e "SHOW STATUS LIKE '%slow%';"

# Check connection counts
docker compose exec mysql mysql -u root -psecret -e "SHOW STATUS LIKE 'Threads_connected';"
```

### Alerting

Consider integrating:
- **Prometheus** - Metrics collection
- **Grafana** - Visualization
- **AlertManager** - Alert routing
- **Sentry** - Error tracking
- **New Relic** - APM
- **Datadog** - Full-stack monitoring

---

## 🤝 Contributing

When contributing Docker-related changes:

1. Test both development and production builds
2. Document new environment variables
3. Update this guide
4. Test on multiple platforms (macOS, Linux, Windows)
5. Verify resource usage
6. Check security implications

---

## 📝 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file/)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [PHP-FPM Configuration](https://www.php.net/manual/en/install.fpm.configuration.php)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [MySQL Docker Hub](https://hub.docker.com/_/mysql)
- [Redis Docker Hub](https://hub.docker.com/_/redis)

---

## 📄 License

This Docker configuration is part of the Spikster project and follows the same license.

---

**Last Updated:** October 3, 2025  
**Docker Version:** 24.0+  
**Docker Compose Version:** 2.0+  
**Maintained by:** Spikster Team
