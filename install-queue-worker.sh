#!/bin/bash

# Laravel Queue Worker Systemd Service Installer
# This script installs and configures the Laravel queue worker as a systemd service

set -e

echo "🚀 Installing Laravel Queue Worker Service..."

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Please run as root (sudo)"
    exit 1
fi

# Configuration
SERVICE_NAME="laravel-queue-worker"
SERVICE_FILE="${SERVICE_NAME}.service"
LARAVEL_DIR="/var/www/html"
LARAVEL_USER="www-data"
LARAVEL_GROUP="www-data"

# Check if Laravel directory exists
if [ ! -d "$LARAVEL_DIR" ]; then
    echo "❌ Laravel directory not found: $LARAVEL_DIR"
    exit 1
fi

# Check if artisan exists
if [ ! -f "$LARAVEL_DIR/artisan" ]; then
    echo "❌ artisan file not found in: $LARAVEL_DIR"
    exit 1
fi

# Stop existing service if running
if systemctl is-active --quiet "$SERVICE_NAME"; then
    echo "⏸️  Stopping existing service..."
    systemctl stop "$SERVICE_NAME"
fi

# Copy service file
echo "📝 Installing service file..."
cp "$SERVICE_FILE" /etc/systemd/system/

# Set permissions
chmod 644 /etc/systemd/system/"$SERVICE_FILE"

# Reload systemd
echo "🔄 Reloading systemd daemon..."
systemctl daemon-reload

# Enable service
echo "✅ Enabling service to start on boot..."
systemctl enable "$SERVICE_NAME"

# Start service
echo "▶️  Starting service..."
systemctl start "$SERVICE_NAME"

# Check status
sleep 2
if systemctl is-active --quiet "$SERVICE_NAME"; then
    echo ""
    echo "✅ Laravel Queue Worker service installed successfully!"
    echo ""
    echo "📊 Service Status:"
    systemctl status "$SERVICE_NAME" --no-pager -l
    echo ""
    echo "💡 Useful commands:"
    echo "   sudo systemctl status $SERVICE_NAME    # Check status"
    echo "   sudo systemctl restart $SERVICE_NAME   # Restart worker"
    echo "   sudo systemctl stop $SERVICE_NAME      # Stop worker"
    echo "   sudo systemctl start $SERVICE_NAME     # Start worker"
    echo "   sudo journalctl -u $SERVICE_NAME -f    # View logs"
    echo ""
else
    echo ""
    echo "❌ Service failed to start. Check logs with:"
    echo "   sudo journalctl -u $SERVICE_NAME -n 50"
    exit 1
fi
