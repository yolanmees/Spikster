# Spikster Development Makefile

.PHONY: help install start stop restart build test lint format clean logs shell mysql tinker fresh

# Default target
.DEFAULT_GOAL := help

help: ## Show this help message
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Install and setup the project
	@echo "🚀 Installing Spikster..."
	docker-compose up -d
	@echo "⏳ Waiting for MySQL..."
	@sleep 10
	docker-compose exec -T app composer install
	docker-compose exec -T app npm install
	docker-compose exec -T app cp .env.example .env || true
	docker-compose exec -T app php artisan key:generate
	docker-compose exec -T app php artisan migrate:fresh --seed
	docker-compose exec -T app npm run dev
	@echo "✅ Installation complete! Visit http://localhost:8080"

start: ## Start all containers
	@echo "🚀 Starting containers..."
	docker-compose up -d
	@echo "✅ Containers started"

stop: ## Stop all containers
	@echo "⏸️  Stopping containers..."
	docker-compose down
	@echo "✅ Containers stopped"

restart: stop start ## Restart all containers

build: ## Rebuild containers
	@echo "🔨 Building containers..."
	docker-compose up -d --build
	@echo "✅ Build complete"

test: ## Run all tests
	@echo "🧪 Running tests..."
	docker-compose exec app php artisan test

test-coverage: ## Run tests with coverage
	@echo "🧪 Running tests with coverage..."
	docker-compose exec app php artisan test --coverage --min=80

test-feature: ## Run feature tests only
	@echo "🧪 Running feature tests..."
	docker-compose exec app php artisan test --testsuite=Feature

test-unit: ## Run unit tests only
	@echo "🧪 Running unit tests..."
	docker-compose exec app php artisan test --testsuite=Unit

lint: ## Run static analysis (PHPStan)
	@echo "🔍 Running PHPStan..."
	docker-compose exec app ./vendor/bin/phpstan analyse

format: ## Format code with Pint
	@echo "✨ Formatting code..."
	docker-compose exec app ./vendor/bin/pint

check: format lint test ## Run format, lint and test

clean: ## Clean containers and volumes
	@echo "🧹 Cleaning up..."
	docker-compose down -v
	@echo "✅ Cleanup complete"

logs: ## Show container logs
	docker-compose logs -f app

logs-queue: ## Show queue worker logs
	docker-compose logs -f queue

logs-mysql: ## Show MySQL logs
	docker-compose logs -f mysql

shell: ## Open bash shell in app container
	docker-compose exec app bash

mysql: ## Open MySQL shell
	docker-compose exec mysql mysql -uspikster -psecret spikster

tinker: ## Open Laravel Tinker
	docker-compose exec app php artisan tinker

fresh: ## Fresh database with seeders
	@echo "🔄 Refreshing database..."
	docker-compose exec app php artisan migrate:fresh --seed
	@echo "✅ Database refreshed"

npm-watch: ## Watch and compile assets
	docker-compose exec app npm run watch

npm-dev: ## Build development assets
	docker-compose exec app npm run dev

npm-prod: ## Build production assets
	docker-compose exec app npm run production

queue-work: ## Run queue worker
	docker-compose exec app php artisan queue:work --verbose

queue-restart: ## Restart queue workers
	docker-compose exec app php artisan queue:restart

cache-clear: ## Clear all caches
	@echo "🧹 Clearing caches..."
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear
	@echo "✅ Caches cleared"

optimize: ## Optimize application
	@echo "⚡ Optimizing..."
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache
	@echo "✅ Optimization complete"

permissions: ## Fix storage permissions
	@echo "🔐 Fixing permissions..."
	docker-compose exec app chmod -R 775 storage bootstrap/cache
	docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
	@echo "✅ Permissions fixed"

status: ## Show container status
	docker-compose ps

stats: ## Show container resource usage
	docker stats

mailhog: ## Open MailHog in browser
	@echo "📧 Opening MailHog..."
	@open http://localhost:8025 || xdg-open http://localhost:8025 || echo "Open http://localhost:8025 in your browser"

app: ## Open application in browser
	@echo "🌐 Opening application..."
	@open http://localhost:8080 || xdg-open http://localhost:8080 || echo "Open http://localhost:8080 in your browser"

db-backup: ## Backup database
	@echo "💾 Creating database backup..."
	@mkdir -p backups
	docker-compose exec -T mysql mysqldump -uspikster -psecret spikster > backups/backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "✅ Backup created in backups/"

db-restore: ## Restore database (usage: make db-restore FILE=backups/backup.sql)
	@echo "📥 Restoring database from $(FILE)..."
	docker-compose exec -T mysql mysql -uspikster -psecret spikster < $(FILE)
	@echo "✅ Database restored"
