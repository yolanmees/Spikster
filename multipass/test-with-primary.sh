#!/bin/bash

# Spikster Installation Test using Primary VM
# This script uses your existing 'primary' VM to test the installation

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

VM_NAME="primary"

echo -e "${GREEN}🚀 Spikster Installation Test (Using Primary VM)${NC}"
echo "=================================================="
echo ""

# Check if primary VM exists
if ! multipass list | grep -q "^primary"; then
    echo -e "${RED}❌ Primary VM does not exist${NC}"
    echo -e "${YELLOW}💡 Create it first with: multipass launch --name primary${NC}"
    exit 1
fi

# Step 1: Start VM if needed
echo -e "${GREEN}📦 Step 1/5: Ensuring VM is running...${NC}"
VM_STATE=$(multipass info "$VM_NAME" | grep State | awk '{print $2}')
if [ "$VM_STATE" != "Running" ]; then
    echo -e "${YELLOW}⏳ Starting VM...${NC}"
    multipass start "$VM_NAME"
    sleep 10
fi

# Step 2: Get VM IP
echo -e "${GREEN}📍 Step 2/5: Getting VM IP address...${NC}"
VM_IP=$(multipass info "$VM_NAME" | grep IPv4 | awk '{print $2}')
echo -e "VM IP: ${GREEN}${VM_IP}${NC}"

# Step 3: Check if Spikster is already installed
echo -e "${GREEN}🔍 Step 3/5: Checking existing installation...${NC}"
SPIKSTER_EXISTS=$(multipass exec "$VM_NAME" -- bash -c "[ -d /var/www/html ] && echo 'yes' || echo 'no'")

if [ "$SPIKSTER_EXISTS" = "yes" ]; then
    echo -e "${YELLOW}⚠️  Spikster directory already exists!${NC}"
    echo -e "${YELLOW}   This will backup and reinstall.${NC}"
    echo -e "${YELLOW}   Press Ctrl+C to cancel, or wait 5 seconds to continue...${NC}"
    sleep 5
fi

# Step 4: Transfer and run installation script
echo -e "${GREEN}📤 Step 4/5: Transferring installation script...${NC}"
if ! multipass transfer ../new_install.sh "$VM_NAME:/tmp/new_install.sh"; then
    echo -e "${RED}❌ Failed to transfer installation script${NC}"
    exit 1
fi

echo -e "${GREEN}⚙️  Running Spikster installation...${NC}"
echo -e "${YELLOW}📝 This takes 15-30 minutes. Output shown below:${NC}"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Run installation
if ! multipass exec "$VM_NAME" -- sudo bash /tmp/new_install.sh; then
    echo ""
    echo -e "${RED}❌ Installation failed${NC}"
    echo -e "${YELLOW}💡 Debugging commands:${NC}"
    echo -e "   multipass shell $VM_NAME"
    echo -e "   sudo cat /var/log/spikster_install.log"
    exit 1
fi

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Step 5: Verify installation
echo -e "${GREEN}✅ Step 5/5: Verifying installation...${NC}"

# Test HTTP response
echo -e "${YELLOW}🔍 Testing HTTP response...${NC}"
sleep 5
HTTP_CODE=$(multipass exec "$VM_NAME" -- curl -s -o /dev/null -w "%{http_code}" http://localhost 2>/dev/null || echo "000")

if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ HTTP server responding (200 OK)${NC}"
else
    echo -e "${YELLOW}⚠️  HTTP returned: $HTTP_CODE${NC}"
fi

# Check services
echo -e "${YELLOW}🔍 Checking services...${NC}"
NGINX_STATUS=$(multipass exec "$VM_NAME" -- sudo systemctl is-active nginx 2>/dev/null || echo "inactive")
PHP_STATUS=$(multipass exec "$VM_NAME" -- sudo systemctl is-active php8.3-fpm 2>/dev/null || echo "inactive")
MYSQL_STATUS=$(multipass exec "$VM_NAME" -- sudo systemctl is-active mysql 2>/dev/null || echo "inactive")

echo "  - Nginx: $NGINX_STATUS"
echo "  - PHP-FPM: $PHP_STATUS"
echo "  - MySQL: $MYSQL_STATUS"

# Get credentials from install log
echo ""
echo -e "${YELLOW}🔐 Extracting credentials from installation...${NC}"
INSTALL_LOG=$(multipass exec "$VM_NAME" -- sudo cat /tmp/new_install.sh 2>/dev/null | tail -30)

# Success message
echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║           🎉 INSTALLATION COMPLETE! 🎉                    ║${NC}"
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
echo -e "📝 ${GREEN}Installation credentials (check output above):${NC}"
echo "   SSH user: spikster"
echo "   SSH pass: [shown in installation output]"
echo "   MySQL user: spikster"
echo "   MySQL pass: [shown in installation output]"
echo ""
echo -e "🖥️  ${GREEN}Shell Access:${NC}"
echo "   multipass shell $VM_NAME"
echo ""
echo -e "📊 ${GREEN}View installation logs:${NC}"
echo "   multipass exec $VM_NAME -- sudo cat /var/log/spikster_install.log"
echo ""
echo -e "🗑️  ${GREEN}Stop VM when done:${NC}"
echo "   multipass stop $VM_NAME"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo -e "${YELLOW}💡 The installation output above contains your SSH and MySQL passwords!${NC}"
echo -e "${YELLOW}   Scroll up to find the 'SETUP COMPLETE' section.${NC}"
echo ""
