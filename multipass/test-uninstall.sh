#!/bin/bash
# Test Spikster uninstallation in Multipass VM
# Usage: ./multipass/test-uninstall.sh <vm-name>

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

VM_NAME="$1"
LOG_DIR="./multipass-logs"
LOG_FILE="${LOG_DIR}/test-uninstall-$(date +%Y%m%d-%H%M%S).log"

mkdir -p "$LOG_DIR"

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
}

if [ -z "$VM_NAME" ]; then
    error "Usage: $0 <vm-name>"
    error "Example: $0 spikster-test-24.04-1234567890"
    exit 1
fi

# Check if VM exists
if ! multipass list | grep -q "$VM_NAME"; then
    error "VM '$VM_NAME' not found"
    echo "Available VMs:"
    multipass list
    exit 1
fi

log "========================================="
log "Spikster Uninstall Test"
log "========================================="
log "VM: $VM_NAME"
log "========================================="

log "Step 1/4: Transferring uninstall script..."
multipass transfer uninstall-spikster.sh "$VM_NAME:/tmp/" 2>&1 | tee -a "$LOG_FILE"

log "Step 2/4: Running uninstall script..."
multipass exec "$VM_NAME" -- sudo bash /tmp/uninstall-spikster.sh 2>&1 | tee -a "$LOG_FILE"

log "Step 3/4: Verifying removal..."

# Check if services are stopped/removed
if multipass exec "$VM_NAME" -- systemctl is-active nginx &> /dev/null; then
    error "❌ Nginx is still running"
else
    log "✅ Nginx stopped/removed"
fi

if multipass exec "$VM_NAME" -- systemctl is-active mysql &> /dev/null; then
    error "❌ MySQL is still running"
else
    log "✅ MySQL stopped/removed"
fi

if multipass exec "$VM_NAME" -- systemctl is-active php8.3-fpm &> /dev/null; then
    error "❌ PHP-FPM is still running"
else
    log "✅ PHP-FPM stopped/removed"
fi

log "Step 4/4: Retrieving uninstall log..."
multipass exec "$VM_NAME" -- sudo cat /var/log/spikster_uninstall.log 2>&1 | tee -a "$LOG_FILE"

log "========================================="
log "Uninstall Test Complete"
log "========================================="
log "Full log saved to: $LOG_FILE"
log "========================================="
