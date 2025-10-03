#!/bin/bash
# Quick test script for rapid iteration
# Usage: ./multipass/quick-test.sh

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

VM_NAME="spikster-quick-$(date +%s)"

echo -e "${GREEN}🚀 Quick Test - Creating VM...${NC}"
if ! multipass launch lts --name "$VM_NAME" --cpus 2 --memory 4G --disk 20G; then
    echo -e "${RED}❌ Failed to create VM${NC}"
    exit 1
fi

echo -e "${YELLOW}⏳ Waiting for VM to be ready...${NC}"
sleep 10

# Check VM status
STATUS=$(multipass info "$VM_NAME" | grep State | awk '{print $2}')
if [ "$STATUS" != "Running" ]; then
    echo -e "${YELLOW}⚠️  VM not running yet, trying to start...${NC}"
    multipass start "$VM_NAME" || true
    sleep 10
fi

echo -e "${GREEN}📤 Transferring script...${NC}"
if ! multipass transfer new_install.sh "$VM_NAME:/tmp/"; then
    echo -e "${RED}❌ Failed to transfer script${NC}"
    echo "VM: $VM_NAME (preserved for debugging)"
    exit 1
fi

echo -e "${GREEN}⚙️  Installing Spikster (this takes 10-15 minutes)...${NC}"
if ! multipass exec "$VM_NAME" -- sudo bash /tmp/new_install.sh; then
    echo -e "${RED}❌ Installation failed${NC}"
    echo "Check logs with: multipass exec $VM_NAME -- sudo cat /var/log/spikster_install.log"
    echo "VM: $VM_NAME (preserved for debugging)"
    exit 1
fi

IP=$(multipass info "$VM_NAME" | grep IPv4 | awk '{print $2}')

echo ""
echo "========================================="
echo -e "${GREEN}✅ Installation complete!${NC}"
echo "========================================="
echo "VM: $VM_NAME"
echo "IP: $IP"
echo "URL: http://$IP"
echo ""
echo "Access VM: multipass shell $VM_NAME"
echo "Delete VM: multipass delete $VM_NAME && multipass purge"
echo "========================================="
