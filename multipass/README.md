# Multipass Testing for Spikster

This directory contains scripts and configurations for testing Spikster installation on Ubuntu VMs using Multipass.

## 📁 Directory Structure

```
multipass/
├── README.md                          # This file
├── mp.sh                              # Main helper script (all-in-one)
├── test-installation.sh               # Full installation test
├── test-uninstall.sh                  # Uninstall test
├── test-suite.sh                      # Complete test suite (all Ubuntu versions)
├── quick-test.sh                      # Quick single test
├── cleanup.sh                         # Cleanup old VMs
├── cloud-init.yaml                    # Basic cloud-init config
├── cloud-init-autoinstall.yaml        # Auto-install cloud-init
└── configs/
    ├── test-small-vps.yaml            # Small VPS simulation (1 CPU, 2GB RAM)
    ├── test-medium-vps.yaml           # Medium VPS simulation (2 CPUs, 4GB RAM)
    └── test-large-vps.yaml            # Large VPS simulation (4 CPUs, 8GB RAM)
```

## 🚀 Quick Start

### 1. Install Multipass

```bash
# macOS
brew install multipass

# Verify
multipass version
```

### 2. Make Scripts Executable

```bash
chmod +x multipass/*.sh
```

### 3. Run Your First Test

```bash
# Quick test (5 minutes)
./multipass/quick-test.sh

# Or use the helper
./multipass/mp.sh test
```

## 📋 Common Usage

### Using the Helper Script (Recommended)

The `mp.sh` script provides easy access to all Multipass operations:

```bash
# Create a test VM
./multipass/mp.sh create              # Ubuntu 24.04
./multipass/mp.sh create 22.04        # Ubuntu 22.04

# List VMs
./multipass/mp.sh list

# Install Spikster in a VM
./multipass/mp.sh install spikster-test-24.04-1234567890

# Check services
./multipass/mp.sh services spikster-test-24.04-1234567890

# View logs
./multipass/mp.sh logs spikster-test-24.04-1234567890

# Get VM IP
./multipass/mp.sh ip spikster-test-24.04-1234567890

# Delete VM
./multipass/mp.sh delete spikster-test-24.04-1234567890

# Clean up all test VMs
./multipass/mp.sh cleanup

# Show all commands
./multipass/mp.sh help
```

### Direct Script Usage

#### Test Installation

```bash
# Test on Ubuntu 24.04 (default)
./multipass/test-installation.sh

# Test on Ubuntu 22.04
./multipass/test-installation.sh 22.04

# Test specific branch
./multipass/test-installation.sh 24.04 develop
```

#### Run Complete Test Suite

```bash
# Test on all Ubuntu versions (20.04, 22.04, 24.04)
./multipass/test-suite.sh

# Test specific branch on all versions
./multipass/test-suite.sh develop
```

#### Test Uninstall

```bash
./multipass/test-uninstall.sh spikster-test-24.04-1234567890
```

#### Quick Test

```bash
# Fastest way to test installation
./multipass/quick-test.sh
```

#### Cleanup

```bash
# Remove test VMs
./multipass/cleanup.sh

# Remove ALL VMs (use with caution!)
./multipass/cleanup.sh --all
```

## 🔧 Advanced Usage

### Using Cloud-init

#### Basic Setup

```bash
multipass launch 24.04 \
    --name spikster-test \
    --cloud-init multipass/cloud-init.yaml \
    --cpus 2 \
    --memory 4G \
    --disk 20G
```

#### Auto-install Spikster

```bash
multipass launch 24.04 \
    --name spikster-auto \
    --cloud-init multipass/cloud-init-autoinstall.yaml \
    --cpus 2 \
    --memory 4G \
    --disk 20G

# Monitor installation
multipass exec spikster-auto -- sudo tail -f /var/log/spikster-autoinstall.log
```

#### VPS Simulations

```bash
# Small VPS (1 CPU, 2GB RAM, 25GB disk)
multipass launch 24.04 \
    --name small-vps \
    --cpus 1 \
    --memory 2G \
    --disk 25G \
    --cloud-init multipass/configs/test-small-vps.yaml

# Medium VPS (2 CPUs, 4GB RAM, 50GB disk) - Recommended
multipass launch 24.04 \
    --name medium-vps \
    --cpus 2 \
    --memory 4G \
    --disk 50G \
    --cloud-init multipass/configs/test-medium-vps.yaml

# Large VPS (4 CPUs, 8GB RAM, 100GB disk)
multipass launch 24.04 \
    --name large-vps \
    --cpus 4 \
    --memory 8G \
    --disk 100G \
    --cloud-init multipass/configs/test-large-vps.yaml
```

### Snapshots

```bash
# Create snapshot
multipass snapshot spikster-test --name fresh-install

# List snapshots
multipass list --snapshots

# Restore snapshot
multipass restore spikster-test --snapshot fresh-install

# Delete snapshot
multipass delete spikster-test.fresh-install
```

### File Transfer

```bash
# Transfer to VM
multipass transfer new_install.sh spikster-test:/tmp/

# Transfer from VM
multipass transfer spikster-test:/var/log/spikster_install.log ./logs/

# Mount directory (persistent)
multipass mount ~/Documents/GitHub/Spikster spikster-test:/mnt/spikster
```

## 🧪 Testing Workflow

### 1. Development Cycle

```bash
# 1. Create VM
./multipass/mp.sh create

# 2. Make changes to new_install.sh
vim new_install.sh

# 3. Test changes
VM_NAME=$(multipass list | grep spikster-test | tail -1 | awk '{print $1}')
./multipass/mp.sh install $VM_NAME

# 4. Check results
./multipass/mp.sh services $VM_NAME
./multipass/mp.sh ip $VM_NAME

# 5. If failed, check logs
./multipass/mp.sh logs $VM_NAME

# 6. Fix issues and test again
./multipass/mp.sh install $VM_NAME

# 7. When satisfied, delete VM
./multipass/mp.sh delete $VM_NAME
```

### 2. Pre-release Testing

```bash
# Test on all Ubuntu versions
./multipass/test-suite.sh

# Review results
cat test-results/summary-*.md
```

### 3. Snapshot-based Testing

```bash
# Create base VM
multipass launch 24.04 --name base --cpus 2 --memory 4G

# Create snapshot of clean state
multipass snapshot base --name clean-ubuntu

# Install Spikster
./multipass/mp.sh install base

# Create snapshot after install
multipass snapshot base --name spikster-installed

# Test configuration changes...

# Restore to clean state
multipass restore base --snapshot clean-ubuntu

# Or restore to installed state
multipass restore base --snapshot spikster-installed
```

## 📊 Viewing Results

### Installation Logs

```bash
# View logs in real-time
multipass exec <vm-name> -- sudo tail -f /var/log/spikster_install.log

# View last 50 lines
./multipass/mp.sh logs <vm-name> 50

# Save log to file
multipass exec <vm-name> -- sudo cat /var/log/spikster_install.log > install.log
```

### Test Results

Test results are saved in:

-   `multipass-logs/` - Individual test logs
-   `test-results/` - Test suite results and summaries

```bash
# View latest test summary
ls -t test-results/summary-*.md | head -1 | xargs cat

# View specific test log
cat multipass-logs/test-24.04-20251003-120000.log
```

## 🔍 Troubleshooting

### VM Won't Start

```bash
# Check Multipass daemon
multipass version

# Restart daemon (macOS)
sudo launchctl stop com.canonical.multipassd
sudo launchctl start com.canonical.multipassd

# Check logs
tail -f ~/Library/Logs/Multipass/multipassd.log
```

### Installation Fails

```bash
# Get detailed logs
./multipass/mp.sh logs <vm-name> 100

# Check disk space
multipass exec <vm-name> -- df -h

# Check services
./multipass/mp.sh services <vm-name>

# Interactive debugging
multipass shell <vm-name>
```

### Clean Reset

```bash
# Delete all VMs
./multipass/cleanup.sh --all

# Or manually
multipass delete --all
multipass purge

# Reinstall Multipass (macOS)
brew uninstall multipass
rm -rf ~/Library/Application\ Support/multipass
brew install multipass
```

## 💡 Tips & Best Practices

### 1. Resource Management

```bash
# Check disk usage
du -sh ~/Library/Application\ Support/multipass

# Regular cleanup
./multipass/cleanup.sh  # Weekly
```

### 2. Naming Convention

Use descriptive names:

```bash
# Good
spikster-test-24.04-20251003
spikster-prod-simulation-22.04
spikster-debug-issue-123

# Bad
test1
vm
ubuntu
```

### 3. Save Test Output

```bash
# Always save logs
./multipass/test-installation.sh 2>&1 | tee test-$(date +%Y%m%d).log
```

### 4. Parallel Testing

```bash
# Don't run multiple tests in parallel (resource intensive)
# Instead, use test-suite.sh for sequential testing
```

### 5. Snapshot Strategy

```bash
# Create snapshots at key stages
multipass snapshot vm --name 1-clean-ubuntu
multipass snapshot vm --name 2-packages-installed
multipass snapshot vm --name 3-spikster-installed
multipass snapshot vm --name 4-configured
```

## 📚 Additional Resources

-   [Multipass Documentation](https://multipass.run/docs)
-   [Cloud-init Documentation](https://cloudinit.readthedocs.io/)
-   [Main Strategy Document](../MULTIPASS_DEVELOPMENT_STRATEGY.md)

## 🎯 Next Steps

1. ✅ Install Multipass: `brew install multipass`
2. ✅ Make scripts executable: `chmod +x multipass/*.sh`
3. ✅ Run quick test: `./multipass/quick-test.sh`
4. ✅ Review results
5. ✅ Run full test suite: `./multipass/test-suite.sh`
6. ✅ Integrate into workflow

## ❓ Need Help?

```bash
# Show helper commands
./multipass/mp.sh help

# Multipass help
multipass help
multipass help launch

# Check Multipass forum
# https://discourse.ubuntu.com/c/multipass/
```

---

**Happy Testing!** 🚀
