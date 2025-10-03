#!/bin/bash
# Spikster Multipass Test Script
# Tests the installation script in a clean Ubuntu VM
# Usage: ./multipass/test-installation.sh [ubuntu-version] [branch]

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
UBUNTU_VERSION="${1:-lts}"
BRANCH="${2:-master}"
VM_NAME="spikster-test-${UBUNTU_VERSION}-$(date +%s)"
LOG_DIR="./multipass-logs"
LOG_FILE="${LOG_DIR}/test-${UBUNTU_VERSION}-$(date +%Y%m%d-%H%M%S).log"

# Create log directory
mkdir -p "$LOG_DIR"

# Helper functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a "$LOG_FILE"
}

# Start test
log "========================================="
log "Spikster Installation Test"
log "========================================="
info "Ubuntu Version: $UBUNTU_VERSION"
info "Branch: $BRANCH"
info "VM Name: $VM_NAME"
info "Log File: $LOG_FILE"
log "========================================="

# Check if multipass is installed
if ! command -v multipass &> /dev/null; then
    error "Multipass is not installed!"
    error "Install with: brew install multipass"
    exit 1
fi

log "Step 1/8: Launching Ubuntu $UBUNTU_VERSION VM..."
multipass launch "$UBUNTU_VERSION" \
    --name "$VM_NAME" \
    --cpus 2 \
    --memory 4G \
    --disk 20G 2>&1 | tee -a "$LOG_FILE"

if [ $? -ne 0 ]; then
    error "Failed to launch VM"
    exit 1
fi

log "Step 2/8: Waiting for VM to be ready..."
sleep 10

log "Step 3/8: Getting VM information..."
multipass info "$VM_NAME" | tee -a "$LOG_FILE"
IP=$(multipass info "$VM_NAME" | grep IPv4 | awk '{print $2}')
info "VM IP Address: $IP"

log "Step 4/8: Transferring installation script..."
multipass transfer new_install.sh "$VM_NAME:/tmp/" 2>&1 | tee -a "$LOG_FILE"

if [ $? -ne 0 ]; then
    error "Failed to transfer installation script"
    multipass delete "$VM_NAME"
    exit 1
fi

log "Step 5/8: Running Spikster installation (this may take 10-15 minutes)..."
multipass exec "$VM_NAME" -- sudo bash /tmp/new_install.sh -b "$BRANCH" 2>&1 | tee -a "$LOG_FILE"

if [ $? -ne 0 ]; then
    error "Installation failed!"
    warn "Retrieving installation log..."
    multipass exec "$VM_NAME" -- sudo cat /var/log/spikster_install.log 2>&1 | tee -a "$LOG_FILE"
    error "VM preserved for debugging: $VM_NAME"
    exit 1
fi

log "Step 6/8: Waiting for services to start..."
sleep 30

log "Step 7/8: Testing HTTP response..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://$IP" || echo "000")
if [ "$HTTP_CODE" = "200" ]; then
    log "✅ HTTP test PASSED (200 OK)"
else
    warn "⚠️  HTTP test returned: $HTTP_CODE"
    warn "This might be expected depending on application setup"
fi

log "Step 8/8: Verifying services..."

# Check Nginx
if multipass exec "$VM_NAME" -- systemctl is-active nginx &> /dev/null; then
    log "✅ Nginx is running"
else
    error "❌ Nginx is not running"
fi

# Check PHP-FPM
if multipass exec "$VM_NAME" -- systemctl is-active php8.3-fpm &> /dev/null; then
    log "✅ PHP-FPM is running"
else
    error "❌ PHP-FPM is not running"
fi

# Check MySQL
if multipass exec "$VM_NAME" -- systemctl is-active mysql &> /dev/null; then
    log "✅ MySQL is running"
else
    error "❌ MySQL is not running"
fi

# Check Redis
if multipass exec "$VM_NAME" -- systemctl is-active redis-server &> /dev/null; then
    log "✅ Redis is running"
else
    warn "⚠️  Redis is not running"
fi

# Check Supervisor
if multipass exec "$VM_NAME" -- systemctl is-active supervisor &> /dev/null; then
    log "✅ Supervisor is running"
else
    warn "⚠️  Supervisor is not running"
fi

log "========================================="
log "Additional Information"
log "========================================="

# Get installation log summary
info "Last 20 lines of installation log:"
multipass exec "$VM_NAME" -- sudo tail -n 20 /var/log/spikster_install.log 2>&1 | tee -a "$LOG_FILE"

# Get disk usage
info "Disk usage:"
multipass exec "$VM_NAME" -- df -h / | tee -a "$LOG_FILE"

# Get memory usage
info "Memory usage:"
multipass exec "$VM_NAME" -- free -h | tee -a "$LOG_FILE"

log "========================================="
log "Test Summary"
log "========================================="
log "VM Name: $VM_NAME"
log "VM IP: $IP"
log "Web UI: http://$IP"
log "Default credentials: administrator@localhost / password"
log ""
log "To access the VM:"
log "  multipass shell $VM_NAME"
log ""
log "To view logs:"
log "  multipass exec $VM_NAME -- sudo tail -f /var/log/spikster_install.log"
log ""
log "To delete the VM:"
log "  multipass delete $VM_NAME && multipass purge"
log ""
log "Full log saved to: $LOG_FILE"
log "========================================="

# Ask if user wants to delete VM
echo ""
read -p "Delete VM now? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    log "Deleting VM..."
    multipass delete "$VM_NAME"
    multipass purge
    log "✅ VM deleted"
else
    info "VM preserved: $VM_NAME"
    info "Remember to delete it later with: multipass delete $VM_NAME && multipass purge"
fi

log "========================================="
log "✅ Test completed successfully!"
log "========================================="
