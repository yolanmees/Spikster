#!/bin/bash

# Deployment script for monitoring system fixes
# This fixes the server_id data type mismatch

set -e

echo "🚀 Deploying Monitoring System Fixes..."
echo ""

# Check if running as correct user
if [ "$EUID" -eq 0 ] && [ "$(whoami)" != "root" ]; then
    echo "⚠️  Running as root. Consider running as www-data user."
fi

LARAVEL_DIR="/var/www/html"

# Navigate to Laravel directory
cd "$LARAVEL_DIR"

echo "📦 Pulling latest code..."
git pull origin laravel-12

echo ""
echo "🗄️  Running database migration..."
echo "⚠️  This will truncate existing server_metrics data (safe - it was collecting wrong data anyway)"
read -p "Continue? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Deployment cancelled"
    exit 1
fi

php artisan migrate --force

echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo ""
echo "🔄 Restarting queue worker..."
php artisan queue:restart

# Check if queue worker service exists
if systemctl list-unit-files | grep -q "laravel-queue-worker.service"; then
    echo "♻️  Restarting queue worker service..."
    systemctl restart laravel-queue-worker
fi

echo ""
echo "✅ Deployment Complete!"
echo ""
echo "📊 Next steps:"
echo "1. Wait 1-2 minutes for new metrics to be collected"
echo "2. Check database: php artisan tinker --execute=\"echo App\\\\Models\\\\ServerMetric::count();\""
echo "3. Visit server stats page in browser"
echo ""
echo "🔍 Monitoring commands:"
echo "   sudo journalctl -u laravel-queue-worker -f    # Watch queue worker logs"
echo "   tail -f storage/logs/laravel.log              # Watch Laravel logs"
