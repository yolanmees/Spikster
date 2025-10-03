# 🧪 Spikster Testing Guide

## Quick Start met Docker

### 1️⃣ **Eerste Keer Opstarten**

```bash
# Start alle services
docker-compose up -d

# Wacht tot MySQL ready is (±30 seconden)
docker-compose logs -f mysql

# Run migrations en seeders
docker-compose exec app php artisan migrate:fresh --seed

# Compile assets
docker-compose exec app npm run dev

# Open browser naar: http://localhost:8080
```

### 2️⃣ **Dagelijks Gebruik**

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Rebuild als je Dockerfile wijzigt
docker-compose up -d --build

# Fresh database
docker-compose exec app php artisan migrate:fresh --seed
```

### 3️⃣ **Testing met PHPUnit**

```bash
# Run alle tests
docker-compose exec app php artisan test

# Run specifieke test
docker-compose exec app php artisan test --filter ServerTest

# Run met coverage
docker-compose exec app php artisan test --coverage

# Run Feature tests
docker-compose exec app php artisan test --testsuite=Feature

# Run Unit tests
docker-compose exec app php artisan test --testsuite=Unit
```

### 4️⃣ **Code Quality Checks**

```bash
# Laravel Pint (formatting)
docker-compose exec app ./vendor/bin/pint

# PHPStan (static analysis)
docker-compose exec app ./vendor/bin/phpstan analyse

# Run both
docker-compose exec app ./vendor/bin/pint && ./vendor/bin/phpstan analyse
```

### 5️⃣ **Development Workflow**

```bash
# Watch assets (in aparte terminal)
docker-compose exec app npm run watch

# Queue worker monitoring
docker-compose exec app php artisan queue:work --verbose

# Logs bekijken
docker-compose logs -f app
docker-compose logs -f queue
docker-compose logs -f mysql

# Container shell
docker-compose exec app bash
```

### 6️⃣ **Database Management**

```bash
# Database shell
docker-compose exec mysql mysql -uspikster -psecret spikster

# Database backup
docker-compose exec mysql mysqldump -uspikster -psecret spikster > backup.sql

# Database restore
docker-compose exec -T mysql mysql -uspikster -psecret spikster < backup.sql

# Tinker REPL
docker-compose exec app php artisan tinker
```

### 7️⃣ **Email Testing**

Open **MailHog** in browser: http://localhost:8025

Alle emails die Laravel verstuurt verschijnen hier, zonder dat ze echt verzonden worden.

```php
// In je .env (automatisch door docker-compose):
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

### 8️⃣ **GitHub Copilot Tips**

#### Test Generatie

```php
// Type in je test file:
// @copilot /tests Generate feature test for Server CRUD operations

// Of:
/** @test */
public function it_can_create_a_new_server()
{
    // Start typing, Copilot suggereert de rest
}
```

#### Service Tests

```php
// @copilot Generate unit test for ServerService with mocked SSH connection
```

#### Livewire Tests

```php
// @copilot Generate Livewire test for ServerTable component with search and pagination
```

### 9️⃣ **Performance Testing**

```bash
# Database query logging
docker-compose exec app php artisan telescope:install
docker-compose exec app php artisan migrate

# Open http://localhost:8080/telescope

# Stress test met Apache Bench
ab -n 1000 -c 10 http://localhost:8080/
```

### 🔟 **Debugging**

```bash
# Enable query logging
docker-compose exec app php artisan tinker
>>> DB::enableQueryLog();
>>> // Run je queries
>>> DB::getQueryLog();

# Ray debugging (install spatie/ray)
docker-compose exec app composer require spatie/laravel-ray --dev

# Xdebug (add to Dockerfile.dev if needed)
```

## 📊 Test Coverage Goals

-   ✅ **Unit Tests**: 80%+ coverage voor Services
-   ✅ **Feature Tests**: Alle CRUD operations
-   ✅ **Livewire Tests**: Alle components
-   ✅ **Integration Tests**: SSH/Queue jobs

## 🐛 Common Issues

### Port al in gebruik

```bash
# Check welke process port 80 gebruikt
lsof -ti:80 | xargs kill -9

# Of wijzig ports in docker-compose.yml:
ports:
  - "8080:80"  # Host:Container
```

### Permission errors

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### MySQL connection refused

```bash
# Wacht tot healthcheck succesvol is
docker-compose ps

# Force recreate
docker-compose down -v
docker-compose up -d
```

### Asset compilation fails

```bash
# Clear node_modules
docker-compose exec app rm -rf node_modules
docker-compose exec app npm install

# Clear npm cache
docker-compose exec app npm cache clean --force
```

## 🚀 CI/CD Integration

### GitHub Actions Workflow

```yaml
# .github/workflows/test.yml
name: Tests

on: [push, pull_request]

jobs:
    test:
        runs-on: ubuntu-latest

        services:
            mysql:
                image: mysql:8.0
                env:
                    MYSQL_DATABASE: spikster_test
                    MYSQL_USER: spikster
                    MYSQL_PASSWORD: secret
                    MYSQL_ROOT_PASSWORD: root
                ports:
                    - 3306:3306
                options: >-
                    --health-cmd="mysqladmin ping"
                    --health-interval=10s
                    --health-timeout=5s
                    --health-retries=3

            redis:
                image: redis:7-alpine
                ports:
                    - 6379:6379

        steps:
            - uses: actions/checkout@v4

            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: 8.3
                  extensions: mbstring, xml, bcmath, mysql, redis
                  coverage: xdebug

            - name: Install Composer Dependencies
              run: composer install --prefer-dist --no-interaction

            - name: Install NPM Dependencies
              run: npm ci

            - name: Build Assets
              run: npm run production

            - name: Run Tests
              run: php artisan test --coverage --min=80
              env:
                  DB_CONNECTION: mysql
                  DB_HOST: 127.0.0.1
                  DB_PORT: 3306
                  DB_DATABASE: spikster_test
                  DB_USERNAME: spikster
                  DB_PASSWORD: secret
```

## 📚 Handige Commands

```bash
# Alles resetten
docker-compose down -v && docker-compose up -d --build

# Logs volgen
docker-compose logs -f

# Resource usage
docker stats

# Cleanup unused resources
docker system prune -a --volumes
```

## 🎯 Next Steps

1. ✅ Setup Docker environment
2. ⬜ Write Feature tests voor Servers
3. ⬜ Write Feature tests voor Sites
4. ⬜ Write Unit tests voor Services
5. ⬜ Write Livewire component tests
6. ⬜ Setup CI/CD pipeline
7. ⬜ Add Mutation testing (Infection PHP)
8. ⬜ Performance benchmarks
