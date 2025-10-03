#!/bin/bash
# Multipass cleanup script
# Removes old test VMs and snapshots
# Usage: ./multipass/cleanup.sh [--all]

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log "========================================="
log "Multipass Cleanup Script"
log "========================================="

# Check for --all flag
DELETE_ALL=false
if [ "$1" = "--all" ]; then
    DELETE_ALL=true
    warn "Will delete ALL VMs!"
fi

# List current VMs
log "Current VMs:"
multipass list

if [ "$DELETE_ALL" = true ]; then
    # Delete all VMs
    warn "Deleting all VMs..."
    read -p "Are you sure? This will delete ALL VMs! (yes/no): " -r
    if [[ $REPLY = "yes" ]]; then
        multipass delete --all
        multipass purge
        log "✅ All VMs deleted"
    else
        log "Cancelled"
        exit 0
    fi
else
    # Delete only test VMs (starting with 'spikster-test' or 'spikster-suite')
    log "Deleting test VMs (spikster-test*, spikster-suite*)..."
    
    # Get list of test VMs
    TEST_VMS=$(multipass list --format csv | grep -E "spikster-test|spikster-suite" | cut -d',' -f1 || true)
    
    if [ -z "$TEST_VMS" ]; then
        log "No test VMs found"
    else
        echo "$TEST_VMS" | while read -r vm; do
            if [ -n "$vm" ]; then
                log "Deleting: $vm"
                multipass delete "$vm"
            fi
        done
        
        log "Purging deleted VMs..."
        multipass purge
        log "✅ Test VMs deleted"
    fi
fi

# Clean up old log files (older than 7 days)
if [ -d "./multipass-logs" ]; then
    log "Cleaning up old log files..."
    find ./multipass-logs -name "*.log" -mtime +7 -delete 2>/dev/null || true
    log "✅ Old logs cleaned"
fi

if [ -d "./test-results" ]; then
    log "Cleaning up old test results..."
    find ./test-results -name "*.log" -mtime +7 -delete 2>/dev/null || true
    find ./test-results -name "*.md" -mtime +7 -delete 2>/dev/null || true
    log "✅ Old test results cleaned"
fi

# Show disk usage
log "========================================="
log "Multipass disk usage:"
du -sh ~/Library/Application\ Support/multipass 2>/dev/null || echo "Could not calculate disk usage"

log "========================================="
log "Remaining VMs:"
multipass list

log "========================================="
log "✅ Cleanup complete!"
log "========================================="
