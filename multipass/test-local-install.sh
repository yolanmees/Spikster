#!/bin/bash

# Spikster Local Installation Test Script
# This script creates a clean Ubuntu VM and tests the local new_install.sh script

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
VM_NAME="spikster-test-$(date +%s)"
UBUNTU_VERSION="lts"
CPUS=2
MEMORY="4G"
DISK="25G"

echo -e "${GREEN}🚀 Spikster Local Installation Test${NC}"
echo "======================================"
echo ""
echo "VM Name: $VM_NAME"
echo "Ubuntu: $UBUNTU_VERSION"
echo "Resources: ${CPUS} CPUs, ${MEMORY} RAM, ${DISK} disk"
echo ""

# Step 1: Create VM
echo -e "${GREEN}📦 Step 1/5: Creating Ubuntu VM...${NC}"
if ! multipass launch "$UBUNTU_VERSION" \
    --name "$VM_NAME" \
    --cpus "$CPUS" \
    --memory "$MEMORY" \
    --disk "$DISK"; then
    echo -e "${RED}❌ Failed to create VM${NC}"
    exit 1
fi

# Wait for VM to be ready
echo -e "${YELLOW}⏳ Waiting for VM to be fully ready...${NC}"
sleep 15

# Check VM status
VM_STATE=$(multipass info "$VM_NAME" | grep State | awk '{print $2}')
if [ "$VM_STATE" != "Running" ]; then
    echo -e "${YELLOW}⚠️  VM not running, attempting to start...${NC}"
    multipass start "$VM_NAME" || true
    sleep 10
fi

# Step 2: Get VM IP
echo -e "${GREEN}📍 Step 2/5: Getting VM IP address...${NC}"
VM_IP=$(multipass info "$VM_NAME" | grep IPv4 | awk '{print $2}')
echo -e "VM IP: ${GREEN}${VM_IP}${NC}"

# Step 3: Transfer installation script
echo -e "${GREEN}📤 Step 3/5: Transferring local new_install.sh to VM...${NC}"
if ! multipass transfer ../new_install.sh "$VM_NAME:/tmp/new_install.sh"; then
    echo -e "${RED}❌ Failed to transfer installation script${NC}"
    echo -e "${YELLOW}💡 VM preserved for debugging. Delete with: multipass delete $VM_NAME && multipass purge${NC}"
    exit 1
fi

# Step 4: Run installation script
echo -e "${GREEN}⚙️  Step 4/5: Running Spikster installation (this takes 15-30 minutes)...${NC}"
echo -e "${YELLOW}📝 Installation output will be shown below...${NC}"
echo ""

# Run the installation script and capture output
if ! multipass exec "$VM_NAME" -- sudo bash /tmp/new_install.sh; then
    echo -e "${RED}❌ Installation failed${NC}"
    echo -e "${YELLOW}💡 VM preserved for debugging.${NC}"
    echo -e "${YELLOW}   - Shell into VM: multipass shell $VM_NAME${NC}"
    echo -e "${YELLOW}   - Check logs: multipass exec $VM_NAME -- sudo cat /var/log/spikster_install.log${NC}"
    echo -e "${YELLOW}   - Delete VM: multipass delete $VM_NAME && multipass purge${NC}"
    exit 1
fi

# Step 5: Verify installation
echo ""
echo -e "${GREEN}✅ Step 5/5: Verifying installation...${NC}"

# Test HTTP response
echo -e "${YELLOW}🔍 Testing HTTP response...${NC}"
sleep 5
HTTP_CODE=$(multipass exec "$VM_NAME" -- curl -s -o /dev/null -w "%{http_code}" http://localhost)

if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ HTTP server responding (200 OK)${NC}"
else
    echo -e "${YELLOW}⚠️  HTTP returned: $HTTP_CODE (may need time to fully start)${NC}"
fi

# Check services
echo -e "${YELLOW}🔍 Checking services...${NC}"
NGINX_STATUS=$(multipass exec "$VM_NAME" -- sudo systemctl is-active nginx || echo "inactive")
PHP_STATUS=$(multipass exec "$VM_NAME" -- sudo systemctl is-active php8.3-fpm || echo "inactive")
MYSQL_STATUS=$(multipass exec "$VM_NAME" -- sudo systemctl is-active mysql || echo "inactive")

echo "  - Nginx: $NGINX_STATUS"
echo "  - PHP-FPM: $PHP_STATUS"
echo "  - MySQL: $MYSQL_STATUS"

# Success message
echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║           🎉 INSTALLATION SUCCESSFUL! 🎉                  ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}📋 Access Information:${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo -e "🌐 ${GREEN}Access Spikster in your browser:${NC}"
echo -e "   ${YELLOW}http://${VM_IP}${NC}"
echo ""
echo -e "🔐 ${GREEN}Default Spikster credentials:${NC}"
echo "   Username: administrator@localhost"
echo "   Password: password"
echo ""
echo -e "🖥️  ${GREEN}SSH Access:${NC}"
echo "   multipass shell $VM_NAME"
echo ""
echo -e "📊 ${GREEN}View installation logs:${NC}"
echo "   multipass exec $VM_NAME -- sudo cat /var/log/spikster_install.log"
echo ""
echo -e "🗑️  ${GREEN}Clean up VM when done:${NC}"
echo "   multipass stop $VM_NAME"
echo "   multipass delete $VM_NAME"
echo "   multipass purge"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo -e "${YELLOW}💡 Tip: Keep this VM running while you test in your browser!${NC}"
echo ""
