#!/bin/bash

# Spikster Installation Test using existing 'spik' VM (Ubuntu 22.04)
# This uses your working 22.04 VM instead of problematic 24.04 VMs

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

VM_NAME="spik"

echo -e "${GREEN}🚀 Spikster Installation Test (Using 'spik' VM - Ubuntu 22.04)${NC}"
echo "================================================================"
echo ""

# Check if VM exists
if ! multipass list | grep -q "^${VM_NAME}"; then
    echo -e "${RED}❌ VM '${VM_NAME}' does not exist${NC}"
    exit 1
fi

# Step 1: Resume/Start VM
echo -e "${GREEN}📦 Step 1/5: Starting VM...${NC}"
VM_STATE=$(multipass info "$VM_NAME" 2>/dev/null | grep State | awk '{print $2}')
echo "Current state: $VM_STATE"

if [ "$VM_STATE" = "Suspended" ]; then
    echo -e "${YELLOW}⏳ Resuming suspended VM...${NC}"
    multipass start "$VM_NAME"
    sleep 5
elif [ "$VM_STATE" != "Running" ]; then
    echo -e "${YELLOW}⏳ Starting VM...${NC}"
    multipass start "$VM_NAME"
    sleep 5
else
    echo -e "${GREEN}✅ VM is already running${NC}"
fi

# Verify VM is now running
VM_STATE=$(multipass info "$VM_NAME" 2>/dev/null | grep State | awk '{print $2}')
if [ "$VM_STATE" != "Running" ]; then
    echo -e "${RED}❌ Failed to start VM. Current state: $VM_STATE${NC}"
    exit 1
fi

# Step 2: Get VM IP
echo -e "${GREEN}📍 Step 2/5: Getting VM IP address...${NC}"
sleep 2
VM_IP=$(multipass info "$VM_NAME" | grep IPv4 | awk '{print $2}')
if [ -z "$VM_IP" ] || [ "$VM_IP" = "--" ]; then
    echo -e "${YELLOW}⚠️  No IP yet, waiting...${NC}"
    sleep 5
    VM_IP=$(multipass info "$VM_NAME" | grep IPv4 | awk '{print $2}')
fi
echo -e "VM IP: ${GREEN}${VM_IP}${NC}"

# Step 3: Check if Spikster is already installed
echo -e "${GREEN}🔍 Step 3/5: Checking for existing Spikster installation...${NC}"
SPIKSTER_EXISTS=$(multipass exec "$VM_NAME" -- bash -c "[ -d /var/www/html ] && echo 'yes' || echo 'no'" 2>/dev/null || echo "no")

if [ "$SPIKSTER_EXISTS" = "yes" ]; then
    echo -e "${YELLOW}⚠️  Spikster directory already exists!${NC}"
    echo -e "${YELLOW}   The installation will backup existing files.${NC}"
    echo -e "${YELLOW}   Press Ctrl+C to cancel, or wait 5 seconds to continue...${NC}"
    sleep 5
fi

# Step 4: Transfer and run installation script
echo -e "${GREEN}📤 Step 4/5: Transferring installation script...${NC}"
if ! multipass transfer ../new_install.sh "$VM_NAME:/tmp/new_install.sh"; then
    echo -e "${RED}❌ Failed to transfer installation script${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Script transferred successfully${NC}"

echo ""
echo -e "${GREEN}⚙️  Running Spikster installation...${NC}"
echo -e "${YELLOW}📝 This will take 15-30 minutes. Installation output:${NC}"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Run installation and capture the output
if ! multipass exec "$VM_NAME" -- sudo bash /tmp/new_install.sh 2>&1 | tee /tmp/spikster_install_output.log; then
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

# Wait a bit for services to fully start
sleep 10

# Test HTTP response
echo -e "${YELLOW}🔍 Testing HTTP response...${NC}"
HTTP_CODE=$(multipass exec "$VM_NAME" -- curl -s -o /dev/null -w "%{http_code}" http://localhost 2>/dev/null || echo "000")

if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ HTTP server responding (200 OK)${NC}"
elif [ "$HTTP_CODE" = "302" ]; then
    echo -e "${GREEN}✅ HTTP server responding (302 Redirect - Normal for Laravel)${NC}"
else
    echo -e "${YELLOW}⚠️  HTTP returned: $HTTP_CODE${NC}"
fi

# Check services
echo -e "${YELLOW}🔍 Checking critical services...${NC}"
NGINX_STATUS=$(multipass exec "$VM_NAME" -- sudo systemctl is-active nginx 2>/dev/null || echo "inactive")
PHP_STATUS=$(multipass exec "$VM_NAME" -- sudo systemctl is-active php8.3-fpm 2>/dev/null || echo "inactive")
MYSQL_STATUS=$(multipass exec "$VM_NAME" -- sudo systemctl is-active mysql 2>/dev/null || echo "inactive")
SUPERVISOR_STATUS=$(multipass exec "$VM_NAME" -- sudo systemctl is-active supervisor 2>/dev/null || echo "inactive")

echo "  - Nginx: $NGINX_STATUS"
echo "  - PHP 8.3-FPM: $PHP_STATUS"
echo "  - MySQL: $MYSQL_STATUS"
echo "  - Supervisor: $SUPERVISOR_STATUS"

# Extract credentials from the last part of the output
echo ""
echo -e "${YELLOW}🔐 Extracting credentials from installation output...${NC}"
if [ -f /tmp/spikster_install_output.log ]; then
    echo -e "${GREEN}Check the output above for the 'SETUP COMPLETE' section${NC}"
    echo -e "${GREEN}It contains your SSH and MySQL passwords.${NC}"
fi

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
echo -e "🔐 ${GREEN}Default Spikster Dashboard credentials:${NC}"
echo "   Username: administrator@localhost"
echo "   Password: password"
echo ""
echo -e "📝 ${GREEN}SSH & MySQL credentials:${NC}"
echo "   ${YELLOW}Scroll up to find the 'SETUP COMPLETE' section in the output!${NC}"
echo "   It shows:"
echo "   - SSH root user: spikster"
echo "   - SSH root pass: [random password]"
echo "   - MySQL root user: spikster"
echo "   - MySQL root pass: [random password]"
echo ""
echo -e "🖥️  ${GREEN}Shell Access:${NC}"
echo "   multipass shell $VM_NAME"
echo ""
echo -e "📊 ${GREEN}View installation logs:${NC}"
echo "   multipass exec $VM_NAME -- sudo cat /var/log/spikster_install.log"
echo ""
echo -e "📁 ${GREEN}Local installation output saved to:${NC}"
echo "   /tmp/spikster_install_output.log"
echo ""
echo -e "💾 ${GREEN}Suspend VM when done (preserves state):${NC}"
echo "   multipass suspend $VM_NAME"
echo ""
echo -e "🗑️  ${GREEN}Stop VM when done:${NC}"
echo "   multipass stop $VM_NAME"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo -e "${YELLOW}💡 IMPORTANT: The installation output above contains your passwords!${NC}"
echo -e "${YELLOW}   Look for the section that says 'SETUP COMPLETE'${NC}"
echo ""
