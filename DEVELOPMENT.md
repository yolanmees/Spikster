# Spikster Development Guide

## Prerequisites

-   PHP 8.2 or higher (tested on PHP 8.4.6)
-   Composer 2.x
-   Node.js 16+ and NPM 8+
-   MySQL 8.0+
-   Git

## Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/yolanmees/Spikster.git
cd Spikster
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install NPM dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env
# DB_DATABASE=your_database_name
# DB_USERNAME=your_database_user
# DB_PASSWORD=your_database_password
```

### 4. Database Setup

```bash
# Run migrations
php artisan migrate

# (Optional) Seed database
php artisan db:seed
```

### 5. Build Assets

```bash
# Development
npm run dev

# Production
npm run prod

# Watch for changes
npm run watch
```

### 6. Serve the Application

```bash
php artisan serve
```

Visit: `http://localhost:8000`

Default login: `administrator@localhost` / `password`

## Development Tools

### Code Quality

```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Check code style without fixing
./vendor/bin/pint --test

# Run PHPStan static analysis
./vendor/bin/phpstan analyse
```

### Security Audits

```bash
# Check PHP dependencies for vulnerabilities
composer audit

# Check NPM dependencies for vulnerabilities
npm audit

# Fix NPM vulnerabilities automatically
npm audit fix
```

### Caching

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=TestName

# Run with coverage
php artisan test --coverage
```

## Project Structure

```
├── app/
│   ├── Console/        # Artisan commands
│   ├── Http/           # Controllers, Middleware
│   ├── Jobs/           # Queue jobs (SSH operations)
│   ├── Livewire/       # Livewire components
│   ├── Models/         # Eloquent models
│   └── Services/       # Business logic services
├── config/             # Configuration files
├── database/           # Migrations, seeders
├── public/             # Web root, assets
├── resources/          # Views, CSS, JS
├── routes/             # Route definitions
├── storage/            # Logs, cache, uploads
└── tests/              # Test files
```

## Key Technologies

-   **Framework**: Laravel 12.x
-   **Frontend**: Livewire 3.x, TailwindCSS 3.x
-   **Authentication**: Laravel Jetstream, Sanctum
-   **Database**: MySQL 8.0
-   **Queue**: Database driver
-   **Testing**: PHPUnit 11.x
-   **Code Quality**: Laravel Pint, PHPStan/Larastan

## Common Tasks

### Adding a New Feature

1. Create feature branch: `git checkout -b feature/your-feature`
2. Write code following Laravel conventions
3. Run Pint: `./vendor/bin/pint`
4. Run PHPStan: `./vendor/bin/phpstan analyse`
5. Write tests
6. Commit and push
7. Create pull request

### Database Changes

```bash
# Create migration
php artisan make:migration create_table_name

# Create model with migration
php artisan make:model ModelName -m

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset database
php artisan migrate:fresh
```

### Queue Jobs

```bash
# Run queue worker
php artisan queue:work

# Process jobs once
php artisan queue:work --once

# List failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry {id}
```

## API Documentation

Swagger API documentation is available at: `/api/docs`

To regenerate Swagger docs:

```bash
php artisan l5-swagger:generate
```

## Troubleshooting

### Permission Issues

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Composer Issues

```bash
# Clear composer cache
composer clear-cache

# Update dependencies
composer update

# Dump autoload
composer dump-autoload
```

### NPM Issues

```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Code Style Guidelines

-   Follow PSR-12 coding standard
-   Use Laravel Pint for automatic formatting
-   Maintain PHPStan level 5 compliance
-   Write descriptive commit messages
-   Add PHPDoc blocks for classes and methods
-   Write tests for new features

## Security

If you discover any security issues, please email security@spikster.com instead of using the issue tracker.

## License

Spikster is licensed under the Creative Commons Attribution-NonCommercial 4.0 International License.

## Support

-   Documentation: https://spikster.com/
-   Issues: https://github.com/yolanmees/Spikster/issues
-   Discord: https://discord.gg/ne99uNEetG
