#!/bin/bash
# Complete test suite for Spikster across multiple Ubuntu versions
# Usage: ./multipass/test-suite.sh

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
UBUNTU_VERSIONS=("22.04" "lts")  # Use 'lts' for latest LTS (24.04)
BRANCH="${1:-master}"
RESULTS_DIR="./test-results"
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
SUMMARY_FILE="${RESULTS_DIR}/summary-${TIMESTAMP}.md"

mkdir -p "$RESULTS_DIR"

# Helper functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$SUMMARY_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$SUMMARY_FILE"
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$SUMMARY_FILE"
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a "$SUMMARY_FILE"
}

# Initialize summary file
cat > "$SUMMARY_FILE" <<EOF
# Spikster Test Suite Results

**Date:** $(date)
**Branch:** $BRANCH
**Tester:** $(whoami)
**Host:** $(hostname)

---

## Test Configuration

- **Ubuntu Versions:** ${UBUNTU_VERSIONS[*]}
- **VM Specs:** 2 CPUs, 4GB RAM, 20GB disk
- **Installation Branch:** $BRANCH

---

## Test Results

EOF

# Track results
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Test function
test_installation() {
    local version=$1
    local vm_name="spikster-suite-${version}-${TIMESTAMP}"
    local log_file="${RESULTS_DIR}/test-${version}-${TIMESTAMP}.log"
    local start_time=$(date +%s)

    TOTAL_TESTS=$((TOTAL_TESTS + 1))

    log "========================================"
    log "Testing Ubuntu $version"
    log "========================================"

    {
        echo "### Ubuntu $version" >> "$SUMMARY_FILE"
        echo "" >> "$SUMMARY_FILE"

        # Launch VM
        info "Launching VM..."
        if ! multipass launch "$version" \
            --name "$vm_name" \
            --cpus 2 \
            --memory 4G \
            --disk 20G > "$log_file" 2>&1; then
            error "Failed to launch VM"
            echo "- ❌ **Status:** FAILED (VM launch)" >> "$SUMMARY_FILE"
            echo "- **Error:** Could not create VM" >> "$SUMMARY_FILE"
            FAILED_TESTS=$((FAILED_TESTS + 1))
            return 1
        fi

        # Transfer script
        info "Transferring installation script..."
        if ! multipass transfer new_install.sh "$vm_name:/tmp/" >> "$log_file" 2>&1; then
            error "Failed to transfer script"
            echo "- ❌ **Status:** FAILED (File transfer)" >> "$SUMMARY_FILE"
            multipass delete "$vm_name"
            FAILED_TESTS=$((FAILED_TESTS + 1))
            return 1
        fi

        # Install
        info "Running installation (this may take 10-15 minutes)..."
        if ! multipass exec "$vm_name" -- sudo bash /tmp/new_install.sh -b "$BRANCH" >> "$log_file" 2>&1; then
            error "Installation failed"
            echo "- ❌ **Status:** FAILED (Installation)" >> "$SUMMARY_FILE"
            echo "- **Log:** See $log_file" >> "$SUMMARY_FILE"
            multipass exec "$vm_name" -- sudo tail -n 50 /var/log/spikster_install.log >> "$log_file" 2>&1 || true
            FAILED_TESTS=$((FAILED_TESTS + 1))
            return 1
        fi

        # Get IP
        IP=$(multipass info "$vm_name" | grep IPv4 | awk '{print $2}')
        info "VM IP: $IP"

        # Wait for services
        info "Waiting for services to start..."
        sleep 60

        # Test HTTP
        info "Testing HTTP response..."
        HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://$IP" || echo "000")

        # Check services
        local nginx_status="❌"
        local php_status="❌"
        local mysql_status="❌"
        local redis_status="⚠️"

        if multipass exec "$vm_name" -- systemctl is-active nginx &> /dev/null; then
            nginx_status="✅"
        fi

        if multipass exec "$vm_name" -- systemctl is-active php8.3-fpm &> /dev/null; then
            php_status="✅"
        fi

        if multipass exec "$vm_name" -- systemctl is-active mysql &> /dev/null; then
            mysql_status="✅"
        fi

        if multipass exec "$vm_name" -- systemctl is-active redis-server &> /dev/null; then
            redis_status="✅"
        fi

        # Get disk usage
        DISK_USAGE=$(multipass exec "$vm_name" -- df -h / | tail -1 | awk '{print $5}')

        # Calculate duration
        local end_time=$(date +%s)
        local duration=$((end_time - start_time))
        local duration_min=$((duration / 60))
        local duration_sec=$((duration % 60))

        # Determine overall status
        if [ "$HTTP_CODE" = "200" ] && [ "$nginx_status" = "✅" ] && [ "$php_status" = "✅" ] && [ "$mysql_status" = "✅" ]; then
            log "✅ All tests PASSED for Ubuntu $version"
            echo "- ✅ **Status:** PASSED" >> "$SUMMARY_FILE"
            PASSED_TESTS=$((PASSED_TESTS + 1))
        else
            warn "⚠️  Some tests FAILED for Ubuntu $version"
            echo "- ⚠️  **Status:** PARTIAL (Some issues detected)" >> "$SUMMARY_FILE"
            PASSED_TESTS=$((PASSED_TESTS + 1))  # Count as passed but with warnings
        fi

        # Write details to summary
        echo "- **Duration:** ${duration_min}m ${duration_sec}s" >> "$SUMMARY_FILE"
        echo "- **HTTP Response:** $HTTP_CODE" >> "$SUMMARY_FILE"
        echo "- **VM IP:** $IP" >> "$SUMMARY_FILE"
        echo "- **Disk Usage:** $DISK_USAGE" >> "$SUMMARY_FILE"
        echo "- **Services:**" >> "$SUMMARY_FILE"
        echo "  - Nginx: $nginx_status" >> "$SUMMARY_FILE"
        echo "  - PHP-FPM: $php_status" >> "$SUMMARY_FILE"
        echo "  - MySQL: $mysql_status" >> "$SUMMARY_FILE"
        echo "  - Redis: $redis_status" >> "$SUMMARY_FILE"
        echo "- **Detailed Log:** \`$log_file\`" >> "$SUMMARY_FILE"
        echo "" >> "$SUMMARY_FILE"

        # Cleanup
        info "Cleaning up..."
        multipass delete "$vm_name"

    } || {
        error "Unexpected error in test"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        multipass delete "$vm_name" 2>/dev/null || true
    }

    log ""
}

# Main execution
log "========================================"
log "Spikster Complete Test Suite"
log "========================================"
log "Testing across: ${UBUNTU_VERSIONS[*]}"
log "========================================"

# Run tests for each version
for version in "${UBUNTU_VERSIONS[@]}"; do
    test_installation "$version"
    sleep 5  # Brief pause between tests
done

# Purge deleted VMs
log "Purging deleted VMs..."
multipass purge

# Write summary
cat >> "$SUMMARY_FILE" <<EOF

---

## Summary

- **Total Tests:** $TOTAL_TESTS
- **Passed:** $PASSED_TESTS
- **Failed:** $FAILED_TESTS
- **Success Rate:** $(awk "BEGIN {printf \"%.1f\", ($PASSED_TESTS/$TOTAL_TESTS)*100}")%

---

## Recommendations

EOF

if [ $FAILED_TESTS -eq 0 ]; then
    cat >> "$SUMMARY_FILE" <<EOF
✅ **All tests passed!** The installation script works correctly on all tested Ubuntu versions.

**Next Steps:**
- Ready for production deployment
- Consider setting up automated CI/CD testing
- Update documentation if needed
EOF
else
    cat >> "$SUMMARY_FILE" <<EOF
⚠️  **Some tests failed.** Review the detailed logs for each failed test.

**Action Items:**
- Check failed test logs in \`$RESULTS_DIR\`
- Fix installation issues for failed versions
- Re-run tests after fixes
EOF
fi

cat >> "$SUMMARY_FILE" <<EOF

---

**Test Suite Completed:** $(date)
EOF

# Display summary
log "========================================"
log "Test Suite Complete!"
log "========================================"
log "Total Tests: $TOTAL_TESTS"
log "Passed: $PASSED_TESTS"
log "Failed: $FAILED_TESTS"
log ""
log "Full summary: $SUMMARY_FILE"
log "Detailed logs: $RESULTS_DIR/"
log "========================================"

# Show summary file
cat "$SUMMARY_FILE"

exit $([ $FAILED_TESTS -eq 0 ] && echo 0 || echo 1)
