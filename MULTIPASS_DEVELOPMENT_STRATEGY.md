# 🚀 Multipass Development Strategy for Spikster VPS Testing

**Purpose:** Use Multipass to create local Ubuntu VMs for testing Spikster installation scripts before deploying to production VPS.

**Created:** October 3, 2025  
**Status:** ✅ Production Ready

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Why Multipass?](#why-multipass)
3. [Prerequisites](#prerequisites)
4. [Quick Start](#quick-start)
5. [VM Management](#vm-management)
6. [Testing Workflow](#testing-workflow)
7. [Automated Testing](#automated-testing)
8. [Snapshots & Recovery](#snapshots--recovery)
9. [CI/CD Integration](#cicd-integration)
10. [Troubleshooting](#troubleshooting)
11. [Best Practices](#best-practices)

---

## 🎯 Overview

This strategy allows you to:
- ✅ Test `new_install.sh` in isolated Ubuntu environments
- ✅ Validate installation on Ubuntu 20.04, 22.04, and 24.04
- ✅ Debug issues before they reach production VPS
- ✅ Create reproducible test environments
- ✅ Automate testing with snapshots and rollbacks
- ✅ Save costs (no cloud resources needed for testing)

### Architecture

```
┌─────────────────────────────────────────────────────────┐
│  macOS Host                                             │
│  ┌────────────────────────────────────────────────────┐ │
│  │  Multipass                                         │ │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────┐ │ │
│  │  │ Ubuntu 20.04 │  │ Ubuntu 22.04 │  │ Ubuntu   │ │ │
│  │  │              │  │              │  │ 24.04    │ │ │
│  │  │ Spikster     │  │ Spikster     │  │ Spikster │ │ │
│  │  │ Test VM      │  │ Test VM      │  │ Test VM  │ │ │
│  │  └──────────────┘  └──────────────┘  └──────────┘ │ │
│  └────────────────────────────────────────────────────┘ │
│                                                         │
│  Your Project: /Users/yolanmees/Documents/GitHub/      │
│                Spikster/                                │
└─────────────────────────────────────────────────────────┘
```

---

## 🤔 Why Multipass?

| Feature | Multipass | Docker | VirtualBox | Cloud VPS |
|---------|-----------|--------|------------|-----------|
| **Ubuntu VMs** | ✅ Native | ⚠️ Containers | ✅ Yes | ✅ Yes |
| **systemd** | ✅ Full | ❌ Limited | ✅ Full | ✅ Full |
| **Speed** | ✅ Fast | ✅ Very Fast | ⚠️ Slow | ⚠️ Network delay |
| **Cost** | ✅ Free | ✅ Free | ✅ Free | ❌ Paid |
| **Snapshots** | ✅ Yes | ⚠️ Images | ✅ Yes | ⚠️ Varies |
| **Cloud-init** | ✅ Native | ❌ No | ⚠️ Complex | ✅ Yes |
| **Isolation** | ✅ Full VM | ⚠️ Container | ✅ Full VM | ✅ Full VM |
| **Mac Support** | ✅ Excellent | ✅ Good | ⚠️ OK | ✅ Yes |

**Winner:** Multipass - Perfect balance of speed, isolation, and VPS similarity!

---

## 📦 Prerequisites

### 1. Install Multipass

```bash
# macOS (Homebrew)
brew install multipass

# Verify installation
multipass version
```

### 2. System Requirements

- **macOS:** 10.15+ (Catalina or newer)
- **RAM:** 8GB minimum (16GB recommended)
- **Disk:** 20GB free space per VM
- **CPU:** 2+ cores

### 3. Configure Multipass

```bash
# Set default CPU/memory (optional)
multipass set local.driver=qemu  # or virtualbox
multipass set local.memory=4G
multipass set local.cpus=2
multipass set local.disk=20G
```

---

## 🚀 Quick Start

### Test Spikster Installation (5 Minutes)

```bash
# 1. Launch Ubuntu 24.04 VM
multipass launch 24.04 --name spikster-test --cpus 2 --memory 4G --disk 20G

# 2. Transfer installation script
multipass transfer new_install.sh spikster-test:/tmp/

# 3. Enter VM
multipass shell spikster-test

# 4. Run installation (inside VM)
sudo bash /tmp/new_install.sh

# 5. Test the installation
curl http://localhost

# 6. Exit and cleanup
exit
multipass delete spikster-test
multipass purge
```

**That's it!** You've just tested your installation script in a clean Ubuntu VM.

---

## 🛠️ VM Management

### Create VMs

```bash
# Ubuntu 24.04 (Noble)
multipass launch 24.04 --name spikster-ubuntu24 --cpus 2 --memory 4G --disk 20G

# Ubuntu 22.04 (Jammy)
multipass launch 22.04 --name spikster-ubuntu22 --cpus 2 --memory 4G --disk 20G

# Ubuntu 20.04 (Focal)
multipass launch 20.04 --name spikster-ubuntu20 --cpus 2 --memory 4G --disk 20G

# With cloud-init (advanced)
multipass launch 24.04 --name spikster-test --cloud-init multipass/cloud-init.yaml
```

### List & Manage VMs

```bash
# List all VMs
multipass list

# Get VM info
multipass info spikster-test

# Start/Stop/Restart
multipass start spikster-test
multipass stop spikster-test
multipass restart spikster-test

# Delete VM
multipass delete spikster-test
multipass purge  # Permanently remove deleted VMs
```

### Access VMs

```bash
# Shell access
multipass shell spikster-test

# Execute commands without entering shell
multipass exec spikster-test -- whoami
multipass exec spikster-test -- df -h
multipass exec spikster-test -- systemctl status nginx

# SSH access (advanced)
multipass exec spikster-test -- sudo cat /root/.ssh/authorized_keys
```

### Transfer Files

```bash
# Transfer script to VM
multipass transfer new_install.sh spikster-test:/tmp/
multipass transfer uninstall-spikster.sh spikster-test:/tmp/

# Transfer directory
multipass transfer ./storage/app/cipi/ spikster-test:/tmp/cipi/

# Transfer from VM to host
multipass transfer spikster-test:/var/log/spikster_install.log ./logs/

# Mount host directory (persistent)
multipass mount ~/Documents/GitHub/Spikster spikster-test:/mnt/spikster
multipass unmount spikster-test
```

---

## 🧪 Testing Workflow

### Full Installation Test

```bash
#!/bin/bash
# File: multipass/test-installation.sh

VM_NAME="spikster-test-$(date +%s)"
UBUNTU_VERSION="24.04"

echo "🚀 Creating VM: $VM_NAME"
multipass launch $UBUNTU_VERSION \
    --name $VM_NAME \
    --cpus 2 \
    --memory 4G \
    --disk 20G

echo "📦 Transferring installation script"
multipass transfer new_install.sh $VM_NAME:/tmp/

echo "⚙️  Running installation"
multipass exec $VM_NAME -- sudo bash /tmp/new_install.sh -b master

echo "✅ Testing HTTP response"
IP=$(multipass info $VM_NAME | grep IPv4 | awk '{print $2}')
sleep 30  # Wait for services to start
curl -I http://$IP

echo "📊 Checking services"
multipass exec $VM_NAME -- systemctl status nginx --no-pager
multipass exec $VM_NAME -- systemctl status php8.3-fpm --no-pager
multipass exec $VM_NAME -- systemctl status mysql --no-pager

echo "📝 Installation log"
multipass exec $VM_NAME -- sudo tail -n 50 /var/log/spikster_install.log

echo "✅ Test complete! VM: $VM_NAME"
echo "   Access: multipass shell $VM_NAME"
echo "   Web UI: http://$IP"
```

### Uninstall Test

```bash
#!/bin/bash
# File: multipass/test-uninstall.sh

VM_NAME="$1"

if [ -z "$VM_NAME" ]; then
    echo "Usage: $0 <vm-name>"
    exit 1
fi

echo "🗑️  Testing uninstall on $VM_NAME"

multipass transfer uninstall-spikster.sh $VM_NAME:/tmp/
multipass exec $VM_NAME -- sudo bash /tmp/uninstall-spikster.sh

echo "✅ Verifying removal"
multipass exec $VM_NAME -- systemctl status nginx || echo "✅ Nginx removed"
multipass exec $VM_NAME -- systemctl status mysql || echo "✅ MySQL removed"

echo "📝 Uninstall log"
multipass exec $VM_NAME -- sudo tail -n 30 /var/log/spikster_uninstall.log
```

### Upgrade Test

```bash
#!/bin/bash
# File: multipass/test-upgrade.sh

VM_NAME="spikster-upgrade-test"

# Create snapshot before upgrade
echo "📸 Creating snapshot"
multipass snapshot $VM_NAME --name before-upgrade

# Test upgrade
echo "⬆️  Testing upgrade"
multipass exec $VM_NAME -- bash -c "cd /var/www/html && git pull origin master"
multipass exec $VM_NAME -- bash -c "cd /var/www/html && composer update"
multipass exec $VM_NAME -- bash -c "cd /var/www/html && php artisan migrate --force"

# Verify
IP=$(multipass info $VM_NAME | grep IPv4 | awk '{print $2}')
curl -I http://$IP

# If failed, restore
if [ $? -ne 0 ]; then
    echo "❌ Upgrade failed, restoring snapshot"
    multipass restore $VM_NAME --snapshot before-upgrade
else
    echo "✅ Upgrade successful"
fi
```

---

## 🔄 Snapshots & Recovery

### Create Snapshots

```bash
# Create snapshot
multipass snapshot spikster-test --name fresh-install

# List snapshots
multipass list --snapshots

# Snapshot output:
# Name           Parent          Comment
# fresh-install  spikster-test   -
```

### Restore Snapshots

```bash
# Restore to snapshot
multipass restore spikster-test --snapshot fresh-install

# This is perfect for:
# - Testing different configurations
# - Rolling back failed tests
# - Creating clean test states
```

### Snapshot Workflow

```bash
# 1. Create base VM with fresh Ubuntu
multipass launch 24.04 --name spikster-base

# 2. Create snapshot: "clean-ubuntu"
multipass snapshot spikster-base --name clean-ubuntu

# 3. Install Spikster
multipass exec spikster-base -- sudo bash /tmp/new_install.sh

# 4. Create snapshot: "spikster-installed"
multipass snapshot spikster-base --name spikster-installed

# 5. Configure sites/apps
# ... your configuration ...

# 6. Create snapshot: "configured"
multipass snapshot spikster-base --name configured

# Now you can jump to any state:
multipass restore spikster-base --snapshot clean-ubuntu
multipass restore spikster-base --snapshot spikster-installed
multipass restore spikster-base --snapshot configured
```

### Delete Snapshots

```bash
# Delete specific snapshot
multipass delete spikster-test.fresh-install

# Purge deleted snapshots
multipass purge
```

---

## 🤖 Automated Testing

### Complete Test Suite

```bash
#!/bin/bash
# File: multipass/test-suite.sh

set -e

UBUNTU_VERSIONS=("20.04" "22.04" "24.04")
RESULTS_DIR="./test-results"
mkdir -p $RESULTS_DIR

test_installation() {
    local version=$1
    local vm_name="spikster-test-$version-$(date +%s)"
    local log_file="$RESULTS_DIR/test-$version-$(date +%s).log"
    
    echo "========================================" | tee -a $log_file
    echo "Testing Ubuntu $version" | tee -a $log_file
    echo "========================================" | tee -a $log_file
    
    # Launch VM
    echo "📦 Launching VM..." | tee -a $log_file
    multipass launch $version \
        --name $vm_name \
        --cpus 2 \
        --memory 4G \
        --disk 20G 2>&1 | tee -a $log_file
    
    # Transfer script
    echo "📤 Transferring installation script..." | tee -a $log_file
    multipass transfer new_install.sh $vm_name:/tmp/ 2>&1 | tee -a $log_file
    
    # Install
    echo "⚙️  Running installation..." | tee -a $log_file
    multipass exec $vm_name -- sudo bash /tmp/new_install.sh -b master 2>&1 | tee -a $log_file
    
    # Get IP
    IP=$(multipass info $vm_name | grep IPv4 | awk '{print $2}')
    echo "🌐 VM IP: $IP" | tee -a $log_file
    
    # Wait for services
    echo "⏳ Waiting for services to start..." | tee -a $log_file
    sleep 60
    
    # Test HTTP
    echo "🧪 Testing HTTP response..." | tee -a $log_file
    if curl -f -s -o /dev/null -w "%{http_code}" http://$IP | grep -q 200; then
        echo "✅ HTTP test PASSED" | tee -a $log_file
    else
        echo "❌ HTTP test FAILED" | tee -a $log_file
        multipass exec $vm_name -- sudo tail -n 100 /var/log/spikster_install.log | tee -a $log_file
    fi
    
    # Test services
    echo "🔍 Checking services..." | tee -a $log_file
    multipass exec $vm_name -- systemctl is-active nginx 2>&1 | tee -a $log_file
    multipass exec $vm_name -- systemctl is-active php8.3-fpm 2>&1 | tee -a $log_file
    multipass exec $vm_name -- systemctl is-active mysql 2>&1 | tee -a $log_file
    multipass exec $vm_name -- systemctl is-active redis-server 2>&1 | tee -a $log_file
    
    # Test database
    echo "🗄️  Testing database..." | tee -a $log_file
    multipass exec $vm_name -- sudo mysql -e "SHOW DATABASES;" 2>&1 | tee -a $log_file
    
    # Test Laravel
    echo "🎨 Testing Laravel..." | tee -a $log_file
    multipass exec $vm_name -- bash -c "cd /var/www/html && php artisan --version" 2>&1 | tee -a $log_file
    
    # Cleanup
    echo "🧹 Cleaning up..." | tee -a $log_file
    multipass delete $vm_name
    
    echo "✅ Test complete for Ubuntu $version" | tee -a $log_file
    echo "" | tee -a $log_file
}

# Run tests
for version in "${UBUNTU_VERSIONS[@]}"; do
    test_installation $version
done

# Purge deleted VMs
multipass purge

echo "========================================"
echo "All tests complete!"
echo "Results saved in: $RESULTS_DIR"
echo "========================================"
```

### Run Test Suite

```bash
# Make executable
chmod +x multipass/test-suite.sh

# Run tests
./multipass/test-suite.sh

# Review results
ls -lh test-results/
cat test-results/test-24.04-*.log
```

---

## 🔧 Cloud-init Configuration

### Basic Cloud-init

```yaml
# File: multipass/cloud-init.yaml

#cloud-config

# Hostname
hostname: spikster-test

# Update packages on first boot
package_update: true
package_upgrade: true

# Install basic packages
packages:
  - curl
  - wget
  - git
  - vim
  - htop

# Create directories
runcmd:
  - mkdir -p /tmp/spikster
  - echo "VM ready for Spikster installation" > /tmp/ready.txt

# Set timezone
timezone: Europe/Amsterdam

# Add swap
swap:
  filename: /swapfile
  size: 1G
  maxsize: 1G
```

### Advanced Cloud-init (Auto-install)

```yaml
# File: multipass/cloud-init-autoinstall.yaml

#cloud-config

hostname: spikster-autoinstall
timezone: Europe/Amsterdam

package_update: true
package_upgrade: true

# Pre-configure debconf for non-interactive install
debconf_selections: |
  mysql-server mysql-server/root_password password CHANGEME
  mysql-server mysql-server/root_password_again password CHANGEME

runcmd:
  # Clone repository
  - git clone https://github.com/yolanmees/Spikster.git /tmp/spikster
  
  # Run installation
  - cd /tmp/spikster && bash new_install.sh -b master
  
  # Create completion marker
  - echo "Installation complete at $(date)" > /var/log/spikster-autoinstall.log

final_message: |
  Spikster installation complete!
  Access the application at http://$(hostname -I | awk '{print $1}')
```

### Use Cloud-init

```bash
# Launch with cloud-init
multipass launch 24.04 \
    --name spikster-auto \
    --cloud-init multipass/cloud-init.yaml

# Launch with auto-install
multipass launch 24.04 \
    --name spikster-auto-install \
    --cloud-init multipass/cloud-init-autoinstall.yaml \
    --cpus 2 \
    --memory 4G \
    --disk 20G

# Wait and check
sleep 300  # 5 minutes
multipass exec spikster-auto-install -- cat /var/log/spikster-autoinstall.log
```

---

## 🔗 CI/CD Integration

### GitHub Actions Workflow

```yaml
# File: .github/workflows/test-multipass.yml

name: Test Installation with Multipass

on:
  push:
    branches: [master, develop]
    paths:
      - 'new_install.sh'
      - 'uninstall-spikster.sh'
  pull_request:
    branches: [master]

jobs:
  test-ubuntu:
    runs-on: macos-latest
    strategy:
      matrix:
        ubuntu: ['20.04', '22.04', '24.04']
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v3
      
      - name: Install Multipass
        run: brew install multipass
      
      - name: Launch Ubuntu VM
        run: |
          multipass launch ${{ matrix.ubuntu }} \
            --name test-vm \
            --cpus 2 \
            --memory 4G \
            --disk 20G
      
      - name: Transfer installation script
        run: multipass transfer new_install.sh test-vm:/tmp/
      
      - name: Run installation
        run: |
          multipass exec test-vm -- sudo bash /tmp/new_install.sh -b master
      
      - name: Test HTTP response
        run: |
          IP=$(multipass info test-vm | grep IPv4 | awk '{print $2}')
          sleep 60
          curl -f http://$IP || exit 1
      
      - name: Check services
        run: |
          multipass exec test-vm -- systemctl is-active nginx
          multipass exec test-vm -- systemctl is-active php8.3-fpm
          multipass exec test-vm -- systemctl is-active mysql
      
      - name: Save logs
        if: failure()
        run: |
          multipass exec test-vm -- sudo cat /var/log/spikster_install.log > install.log
      
      - name: Upload logs
        if: failure()
        uses: actions/upload-artifact@v3
        with:
          name: installation-logs-${{ matrix.ubuntu }}
          path: install.log
      
      - name: Cleanup
        if: always()
        run: |
          multipass delete test-vm
          multipass purge
```

---

## 🐛 Troubleshooting

### VM Won't Start

```bash
# Check Multipass status
multipass version
multipass list

# Check system resources
top
df -h

# Restart Multipass daemon (macOS)
sudo launchctl stop com.canonical.multipassd
sudo launchctl start com.canonical.multipassd

# Check logs
tail -f ~/Library/Logs/Multipass/multipassd.log
```

### Installation Fails

```bash
# Get detailed logs
multipass exec spikster-test -- sudo cat /var/log/spikster_install.log

# Check disk space
multipass exec spikster-test -- df -h

# Check services
multipass exec spikster-test -- systemctl status nginx
multipass exec spikster-test -- systemctl status mysql

# Interactive debugging
multipass shell spikster-test
sudo tail -f /var/log/spikster_install.log
```

### Network Issues

```bash
# Check VM IP
multipass info spikster-test

# Test connectivity from host
ping <VM_IP>
curl http://<VM_IP>

# Test from within VM
multipass exec spikster-test -- curl http://localhost
multipass exec spikster-test -- netstat -tlnp
```

### Performance Issues

```bash
# Increase resources
multipass stop spikster-test
multipass set local.spikster-test.cpus=4
multipass set local.spikster-test.memory=8G
multipass start spikster-test

# Or recreate with more resources
multipass delete spikster-test
multipass launch 24.04 --name spikster-test --cpus 4 --memory 8G --disk 40G
```

### Clean Reset

```bash
# Delete all VMs
multipass delete --all
multipass purge

# Reset Multipass (macOS)
brew uninstall multipass
rm -rf ~/Library/Application\ Support/multipass
brew install multipass
```

---

## ✅ Best Practices

### 1. Naming Convention

```bash
# Use descriptive names with version and purpose
multipass launch 24.04 --name spikster-test-24-$(date +%Y%m%d)
multipass launch 22.04 --name spikster-prod-22-final
multipass launch 20.04 --name spikster-debug-20
```

### 2. Resource Management

```bash
# Match production VPS specs
# Small VPS: 1 CPU, 2GB RAM, 25GB disk
multipass launch 24.04 --name small --cpus 1 --memory 2G --disk 25G

# Medium VPS: 2 CPUs, 4GB RAM, 50GB disk
multipass launch 24.04 --name medium --cpus 2 --memory 4G --disk 50G

# Large VPS: 4 CPUs, 8GB RAM, 100GB disk
multipass launch 24.04 --name large --cpus 4 --memory 8G --disk 100G
```

### 3. Snapshot Strategy

```bash
# Create snapshots at key stages
multipass snapshot vm --name 1-clean-ubuntu
multipass snapshot vm --name 2-packages-installed
multipass snapshot vm --name 3-spikster-installed
multipass snapshot vm --name 4-configured
multipass snapshot vm --name 5-tested
```

### 4. Log Everything

```bash
# Save all test outputs
./multipass/test-installation.sh 2>&1 | tee logs/test-$(date +%Y%m%d-%H%M%S).log

# Create logs directory
mkdir -p logs
```

### 5. Cleanup Regularly

```bash
# Weekly cleanup script
#!/bin/bash
# File: multipass/cleanup.sh

echo "🧹 Cleaning up old VMs..."
multipass list --format json | jq -r '.list[] | select(.state == "Deleted") | .name' | while read vm; do
    echo "Purging: $vm"
done

multipass purge

echo "📊 Current VMs:"
multipass list

echo "💾 Disk usage:"
du -sh ~/Library/Application\ Support/multipass
```

### 6. Test Matrix

Always test on all supported Ubuntu versions:

```bash
# Test matrix
VERSIONS=("20.04" "22.04" "24.04")
CONFIGS=("default" "no-mysql" "no-nginx")

for version in "${VERSIONS[@]}"; do
    for config in "${CONFIGS[@]}"; do
        echo "Testing: Ubuntu $version with $config"
        # Run tests...
    done
done
```

### 7. Documentation

Document every test run:

```bash
# Create test report
cat > test-report-$(date +%Y%m%d).md <<EOF
# Spikster Installation Test Report

**Date:** $(date)
**Tester:** $(whoami)
**Script Version:** $(git rev-parse --short HEAD)

## Test Results

### Ubuntu 24.04
- Status: ✅ PASSED
- Duration: 8m 32s
- Issues: None

### Ubuntu 22.04
- Status: ✅ PASSED
- Duration: 8m 45s
- Issues: None

### Ubuntu 20.04
- Status: ⚠️ WARNING
- Duration: 9m 12s
- Issues: PHP version mismatch warning

## Recommendations
- Update documentation for Ubuntu 20.04
- Add warning about PHP 8.3 on older Ubuntu

EOF
```

---

## 📚 Additional Resources

### Multipass Commands Reference

```bash
# Help
multipass help
multipass help launch
multipass help transfer

# VM info
multipass info --all
multipass info spikster-test

# Resource usage
multipass exec spikster-test -- free -h
multipass exec spikster-test -- df -h
multipass exec spikster-test -- top -bn1 | head -20

# Network
multipass exec spikster-test -- ip addr
multipass exec spikster-test -- ss -tlnp
```

### Useful Aliases

Add to your `~/.zshrc`:

```bash
# Multipass aliases
alias mp='multipass'
alias mpl='multipass list'
alias mps='multipass shell'
alias mpi='multipass info'
alias mpstart='multipass start'
alias mpstop='multipass stop'
alias mpdel='multipass delete'

# Spikster testing
alias spiktest='./multipass/test-installation.sh'
alias spikclean='multipass delete --all && multipass purge'
```

### Project Structure

```
Spikster/
├── multipass/
│   ├── README.md
│   ├── cloud-init.yaml
│   ├── cloud-init-autoinstall.yaml
│   ├── test-installation.sh
│   ├── test-uninstall.sh
│   ├── test-upgrade.sh
│   ├── test-suite.sh
│   ├── cleanup.sh
│   └── configs/
│       ├── test-small-vps.yaml
│       ├── test-medium-vps.yaml
│       └── test-large-vps.yaml
├── new_install.sh
├── uninstall-spikster.sh
└── MULTIPASS_DEVELOPMENT_STRATEGY.md
```

---

## 🎯 Summary

**You now have:**
- ✅ Complete Multipass development strategy
- ✅ Automated testing workflows
- ✅ Snapshot-based testing
- ✅ Multi-version Ubuntu testing
- ✅ CI/CD integration templates
- ✅ Best practices and troubleshooting guides

**Next Steps:**
1. Install Multipass on your Mac
2. Create your first test VM
3. Test the installation script
4. Set up automated testing
5. Integrate with CI/CD

**Benefits:**
- 💰 Save cloud costs (no VPS needed for testing)
- ⚡ Fast iterations (local VMs)
- 🔒 Safe testing (isolated environments)
- 📊 Reproducible tests (snapshots)
- 🚀 Production confidence (tested before deploy)

---

**Ready to test?** Start with the Quick Start section and you'll be testing in 5 minutes! 🚀
