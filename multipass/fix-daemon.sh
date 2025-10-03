#!/bin/bash
# Fix Multipass daemon connection issues
# Usage: ./multipass/fix-daemon.sh

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🔧 Multipass Daemon Fix Script${NC}"
echo "=================================="
echo ""

# Check if Multipass is installed
if ! command -v multipass &> /dev/null; then
    echo -e "${RED}❌ Multipass is not installed${NC}"
    echo ""
    echo "Install with: brew install multipass"
    exit 1
fi

echo -e "${GREEN}✅ Multipass is installed${NC}"
multipass version
echo ""

# Check if daemon is running
echo -e "${YELLOW}Checking daemon status...${NC}"
if pgrep -x "multipassd" > /dev/null; then
    echo -e "${GREEN}✅ Daemon is already running${NC}"
    echo ""
    echo "Testing connection..."
    if multipass list &> /dev/null; then
        echo -e "${GREEN}✅ Connection is working!${NC}"
        multipass list
        exit 0
    else
        echo -e "${YELLOW}⚠️  Daemon running but connection failed${NC}"
        echo "Will attempt restart..."
    fi
else
    echo -e "${YELLOW}⚠️  Daemon is not running${NC}"
fi
echo ""

# Try to start/restart daemon
echo -e "${BLUE}🔄 Starting Multipass daemon...${NC}"
echo ""

# Method 1: Try using launchctl (macOS)
echo "Method 1: Using launchctl..."
if [ -f "/Library/LaunchDaemons/com.canonical.multipassd.plist" ]; then
    # Stop existing daemon
    sudo launchctl stop com.canonical.multipassd 2>/dev/null || true
    sleep 2
    
    # Unload if loaded
    sudo launchctl unload /Library/LaunchDaemons/com.canonical.multipassd.plist 2>/dev/null || true
    sleep 1
    
    # Load the daemon
    echo "  Loading daemon..."
    if sudo launchctl load /Library/LaunchDaemons/com.canonical.multipassd.plist; then
        echo -e "${GREEN}  ✅ Daemon loaded${NC}"
    else
        echo -e "${RED}  ❌ Failed to load daemon${NC}"
    fi
    
    # Start the daemon
    echo "  Starting daemon..."
    if sudo launchctl start com.canonical.multipassd; then
        echo -e "${GREEN}  ✅ Daemon started${NC}"
    else
        echo -e "${YELLOW}  ⚠️  Start command completed (may already be running)${NC}"
    fi
else
    echo -e "${YELLOW}  ⚠️  LaunchDaemon plist not found${NC}"
fi
echo ""

# Method 2: Try opening Multipass.app
echo "Method 2: Opening Multipass.app..."
if [ -d "/Applications/Multipass.app" ]; then
    open /Applications/Multipass.app
    echo -e "${GREEN}  ✅ Multipass.app opened${NC}"
else
    echo -e "${YELLOW}  ⚠️  Multipass.app not found${NC}"
fi
echo ""

# Wait for daemon to start
echo -e "${YELLOW}⏳ Waiting for daemon to initialize...${NC}"
for i in {10..1}; do
    echo -n "  $i "
    sleep 1
done
echo ""
echo ""

# Test connection
echo -e "${BLUE}🧪 Testing connection...${NC}"
echo ""

if multipass version &> /dev/null; then
    echo -e "${GREEN}✅ Version check: OK${NC}"
    multipass version
    echo ""
else
    echo -e "${RED}❌ Version check: FAILED${NC}"
    echo ""
fi

if multipass list &> /dev/null; then
    echo -e "${GREEN}✅ List command: OK${NC}"
    multipass list
    echo ""
else
    echo -e "${RED}❌ List command: FAILED${NC}"
    echo ""
fi

# Check process
if pgrep -x "multipassd" > /dev/null; then
    echo -e "${GREEN}✅ Daemon process: Running${NC}"
    ps aux | grep -i multipass | grep -v grep
    echo ""
else
    echo -e "${RED}❌ Daemon process: Not running${NC}"
    echo ""
fi

# Final verdict
echo "=================================="
if multipass list &> /dev/null; then
    echo -e "${GREEN}🎉 SUCCESS! Multipass is working!${NC}"
    echo ""
    echo "You can now run:"
    echo "  ./multipass/quick-test.sh"
    echo "  ./multipass/mp.sh test"
    echo ""
    exit 0
else
    echo -e "${RED}❌ FAILED: Multipass is still not working${NC}"
    echo ""
    echo "Additional steps to try:"
    echo ""
    echo "1. Check logs:"
    echo "   tail -50 /Library/Logs/Multipass/multipassd.log"
    echo "   tail -50 ~/Library/Logs/Multipass/multipassd.log"
    echo ""
    echo "2. Reinstall Multipass:"
    echo "   brew uninstall multipass"
    echo "   brew install multipass"
    echo ""
    echo "3. Check system requirements:"
    echo "   - macOS 10.15+ required"
    echo "   - 8GB+ RAM recommended"
    echo "   - 20GB+ free disk space"
    echo ""
    echo "4. Try VirtualBox driver:"
    echo "   brew install virtualbox"
    echo "   multipass set local.driver=virtualbox"
    echo ""
    echo "5. Check forum:"
    echo "   https://discourse.ubuntu.com/c/multipass/"
    echo ""
    exit 1
fi
