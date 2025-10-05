#!/bin/bash

# Spikster Agent Installation Script
# This script installs the Spikster monitoring agent on Ubuntu/Debian servers

set -e

VERSION="1.0.0"
INSTALL_DIR="/usr/local/bin"
SERVICE_FILE="/etc/systemd/system/spikster-agent.service"
BINARY_NAME="spikster-agent"
USER="spikster"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    log_error "Please run as root (use sudo)"
    exit 1
fi

log_info "Installing Spikster Agent v${VERSION}..."

# Detect architecture
ARCH=$(uname -m)
case $ARCH in
    x86_64)
        BINARY_ARCH="amd64"
        ;;
    aarch64|arm64)
        BINARY_ARCH="arm64"
        ;;
    *)
        log_error "Unsupported architecture: $ARCH"
        exit 1
        ;;
esac

log_info "Detected architecture: ${ARCH} (${BINARY_ARCH})"

# Check if user exists, create if not
if ! id "$USER" &>/dev/null; then
    log_info "Creating user: $USER"
    useradd -r -s /bin/false "$USER"
else
    log_info "User $USER already exists"
fi

# Download binary
log_info "Downloading Spikster Agent binary..."
DOWNLOAD_URL="https://github.com/spikster/agent/releases/download/v${VERSION}/spikster-agent-linux-${BINARY_ARCH}"

# For development/testing, you can build locally and copy
# For now, we'll create a placeholder
if [ -f "./build/${BINARY_NAME}-linux-${BINARY_ARCH}" ]; then
    log_info "Using local build..."
    cp "./build/${BINARY_NAME}-linux-${BINARY_ARCH}" "${INSTALL_DIR}/${BINARY_NAME}"
else
    log_warn "Local build not found. In production, download from: ${DOWNLOAD_URL}"
    log_info "Copying from current directory for testing..."
    if [ -f "./${BINARY_NAME}" ]; then
        cp "./${BINARY_NAME}" "${INSTALL_DIR}/${BINARY_NAME}"
    else
        log_error "Binary not found. Please build first with: ./build.sh"
        exit 1
    fi
fi

# Set permissions
chmod +x "${INSTALL_DIR}/${BINARY_NAME}"
chown root:root "${INSTALL_DIR}/${BINARY_NAME}"

log_info "Binary installed to ${INSTALL_DIR}/${BINARY_NAME}"

# Install systemd service
log_info "Installing systemd service..."
cat > "${SERVICE_FILE}" << 'EOF'
[Unit]
Description=Spikster Monitoring Agent
Documentation=https://docs.spikster.com/monitoring
After=network.target
Wants=network-online.target

[Service]
Type=simple
User=spikster
Group=spikster
WorkingDirectory=/usr/local/bin
ExecStart=/usr/local/bin/spikster-agent
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
SyslogIdentifier=spikster-agent

# Security hardening
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
ReadOnlyPaths=/
ReadWritePaths=/tmp

# Resource limits
LimitNOFILE=65536
MemoryLimit=50M
CPUQuota=10%

# Environment
Environment="PORT=:9273"

[Install]
WantedBy=multi-user.target
EOF

# Reload systemd
log_info "Reloading systemd daemon..."
systemctl daemon-reload

# Enable and start service
log_info "Enabling Spikster Agent service..."
systemctl enable spikster-agent

log_info "Starting Spikster Agent service..."
systemctl start spikster-agent

# Wait a moment for the service to start
sleep 2

# Check if service is running
if systemctl is-active --quiet spikster-agent; then
    log_info "✅ Spikster Agent installed and running successfully!"
else
    log_error "Service failed to start. Checking logs..."
    journalctl -u spikster-agent -n 20 --no-pager
    exit 1
fi

# Test the endpoint
log_info "Testing agent endpoint..."
if curl -s http://localhost:9273/health > /dev/null; then
    log_info "✅ Agent responding on http://localhost:9273"
else
    log_warn "Agent may not be responding yet. Check with: curl http://localhost:9273/health"
fi

# Show status
echo ""
log_info "Installation complete!"
echo ""
echo "Service status:"
systemctl status spikster-agent --no-pager
echo ""
echo "📋 Quick commands:"
echo "  Status:  sudo systemctl status spikster-agent"
echo "  Logs:    sudo journalctl -u spikster-agent -f"
echo "  Restart: sudo systemctl restart spikster-agent"
echo "  Test:    curl http://localhost:9273/metrics"
echo ""
log_info "Agent is now monitoring system metrics on port 9273"
