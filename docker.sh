#!/usr/bin/env bash

# Spikster Docker Management Script

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

info() { echo -e "${GREEN}[INFO]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

# Docker Compose wrapper
dc() {
    if docker compose version &> /dev/null 2>&1; then
        docker compose "$@"
    else
        docker-compose "$@"
    fi
}

COMMAND=${1:-help}
shift || true

case "$COMMAND" in
    build)
        info "Building Docker images..."
        dc build "$@"
        ;;
    start)
        info "Starting containers..."
        dc up -d
        ;;
    stop)
        dc stop
        ;;
    test)
        info "Running tests..."
        dc exec app php artisan test
        ;;
    shell)
        dc exec app /bin/sh
        ;;
    *)
        echo "Usage: $0 {build|start|stop|test|shell}"
        exit 1
        ;;
esac
