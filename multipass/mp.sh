#!/bin/bash
# Multipass management helper script
# Provides easy commands for common Multipass operations
# Usage: ./multipass/mp.sh <command>

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

command="$1"
shift || true

show_help() {
    cat <<EOF
Multipass Helper Script for Spikster Testing

Usage: ./multipass/mp.sh <command> [options]

Commands:
  create <version>     Create a new test VM (default: 24.04)
                       Example: ./multipass/mp.sh create 22.04
  
  list                 List all VMs
  
  shell <vm-name>      Open shell in VM
  
  info <vm-name>       Show VM information
  
  install <vm-name>    Install Spikster in VM
  
  test <version>       Run full installation test (default: 24.04)
  
  snapshot <vm-name>   Create snapshot of VM
  
  restore <vm-name> <snapshot>  Restore VM from snapshot
  
  delete <vm-name>     Delete a VM
  
  cleanup              Delete all test VMs
  
  start <vm-name>      Start a VM
  
  stop <vm-name>       Stop a VM
  
  restart <vm-name>    Restart a VM
  
  ip <vm-name>         Get VM IP address
  
  logs <vm-name>       View Spikster installation logs
  
  services <vm-name>   Check service status
  
  help                 Show this help message

Examples:
  # Create Ubuntu 24.04 test VM
  ./multipass/mp.sh create
  
  # Create Ubuntu 22.04 test VM
  ./multipass/mp.sh create 22.04
  
  # Install Spikster in VM
  ./multipass/mp.sh install spikster-test
  
  # Run full test
  ./multipass/mp.sh test 24.04
  
  # Check services
  ./multipass/mp.sh services spikster-test
  
  # Clean up all test VMs
  ./multipass/mp.sh cleanup

EOF
}

case "$command" in
    create)
        VERSION="${1:-lts}"
        VM_NAME="spikster-test-${VERSION}-$(date +%s)"
        echo -e "${GREEN}Creating VM: $VM_NAME${NC}"
        multipass launch "$VERSION" --name "$VM_NAME" --cpus 2 --memory 4G --disk 20G
        echo -e "${GREEN}✅ VM created: $VM_NAME${NC}"
        ;;
    
    list)
        multipass list
        ;;
    
    shell)
        VM_NAME="$1"
        if [ -z "$VM_NAME" ]; then
            echo "Usage: $0 shell <vm-name>"
            exit 1
        fi
        multipass shell "$VM_NAME"
        ;;
    
    info)
        VM_NAME="$1"
        if [ -z "$VM_NAME" ]; then
            echo "Usage: $0 info <vm-name>"
            exit 1
        fi
        multipass info "$VM_NAME"
        ;;
    
    install)
        VM_NAME="$1"
        if [ -z "$VM_NAME" ]; then
            echo "Usage: $0 install <vm-name>"
            exit 1
        fi
        echo -e "${GREEN}Transferring installation script...${NC}"
        multipass transfer new_install.sh "$VM_NAME:/tmp/"
        echo -e "${GREEN}Running installation...${NC}"
        multipass exec "$VM_NAME" -- sudo bash /tmp/new_install.sh
        IP=$(multipass info "$VM_NAME" | grep IPv4 | awk '{print $2}')
        echo -e "${GREEN}✅ Installation complete!${NC}"
        echo -e "${BLUE}Access at: http://$IP${NC}"
        ;;
    
    test)
        VERSION="${1:-lts}"
        echo -e "${GREEN}Running installation test for Ubuntu $VERSION${NC}"
        ./multipass/test-installation.sh "$VERSION"
        ;;
    
    snapshot)
        VM_NAME="$1"
        SNAPSHOT_NAME="${2:-snapshot-$(date +%s)}"
        if [ -z "$VM_NAME" ]; then
            echo "Usage: $0 snapshot <vm-name> [snapshot-name]"
            exit 1
        fi
        echo -e "${GREEN}Creating snapshot: $SNAPSHOT_NAME${NC}"
        multipass snapshot "$VM_NAME" --name "$SNAPSHOT_NAME"
        echo -e "${GREEN}✅ Snapshot created${NC}"
        ;;
    
    restore)
        VM_NAME="$1"
        SNAPSHOT_NAME="$2"
        if [ -z "$VM_NAME" ] || [ -z "$SNAPSHOT_NAME" ]; then
            echo "Usage: $0 restore <vm-name> <snapshot-name>"
            exit 1
        fi
        echo -e "${YELLOW}Restoring VM to snapshot: $SNAPSHOT_NAME${NC}"
        multipass restore "$VM_NAME" --snapshot "$SNAPSHOT_NAME"
        echo -e "${GREEN}✅ VM restored${NC}"
        ;;
    
    delete)
        VM_NAME="$1"
        if [ -z "$VM_NAME" ]; then
            echo "Usage: $0 delete <vm-name>"
            exit 1
        fi
        echo -e "${YELLOW}Deleting VM: $VM_NAME${NC}"
        multipass delete "$VM_NAME"
        multipass purge
        echo -e "${GREEN}✅ VM deleted${NC}"
        ;;
    
    cleanup)
        ./multipass/cleanup.sh
        ;;
    
    start)
        VM_NAME="$1"
        if [ -z "$VM_NAME" ]; then
            echo "Usage: $0 start <vm-name>"
            exit 1
        fi
        multipass start "$VM_NAME"
        ;;
    
    stop)
        VM_NAME="$1"
        if [ -z "$VM_NAME" ]; then
            echo "Usage: $0 stop <vm-name>"
            exit 1
        fi
        multipass stop "$VM_NAME"
        ;;
    
    restart)
        VM_NAME="$1"
        if [ -z "$VM_NAME" ]; then
            echo "Usage: $0 restart <vm-name>"
            exit 1
        fi
        multipass restart "$VM_NAME"
        ;;
    
    ip)
        VM_NAME="$1"
        if [ -z "$VM_NAME" ]; then
            echo "Usage: $0 ip <vm-name>"
            exit 1
        fi
        multipass info "$VM_NAME" | grep IPv4 | awk '{print $2}'
        ;;
    
    logs)
        VM_NAME="$1"
        LINES="${2:-50}"
        if [ -z "$VM_NAME" ]; then
            echo "Usage: $0 logs <vm-name> [lines]"
            exit 1
        fi
        multipass exec "$VM_NAME" -- sudo tail -n "$LINES" /var/log/spikster_install.log
        ;;
    
    services)
        VM_NAME="$1"
        if [ -z "$VM_NAME" ]; then
            echo "Usage: $0 services <vm-name>"
            exit 1
        fi
        echo -e "${BLUE}Checking services on $VM_NAME...${NC}"
        echo ""
        echo "Nginx:"
        multipass exec "$VM_NAME" -- systemctl status nginx --no-pager || true
        echo ""
        echo "PHP-FPM:"
        multipass exec "$VM_NAME" -- systemctl status php8.3-fpm --no-pager || true
        echo ""
        echo "MySQL:"
        multipass exec "$VM_NAME" -- systemctl status mysql --no-pager || true
        echo ""
        echo "Redis:"
        multipass exec "$VM_NAME" -- systemctl status redis-server --no-pager || true
        ;;
    
    help|--help|-h)
        show_help
        ;;
    
    *)
        echo "Unknown command: $command"
        echo ""
        show_help
        exit 1
        ;;
esac
