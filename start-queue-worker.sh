#!/bin/bash

# Start Laravel Queue Worker for development
# This processes jobs from the database queue

echo "🚀 Starting Laravel Queue Worker..."
echo "📋 Queue Connection: database"
echo "⏱️  Press Ctrl+C to stop"
echo ""

php artisan queue:work database --sleep=3 --tries=3 --timeout=360 --verbose

