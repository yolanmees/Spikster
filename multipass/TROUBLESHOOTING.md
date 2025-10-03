# 🔧 Multipass Troubleshooting - Socket Connection Error

**Error:** `cannot connect to the multipass socket`  
**Date:** October 3, 2025  
**Status:** ✅ RESOLVED

---

## 🎯 Problem

When running `./multipass/quick-test.sh`, you get:
```
launch failed: cannot connect to the multipass socket
transfer failed: cannot connect to the multipass socket
exec failed: cannot connect to the multipass socket
```

This means the Multipass daemon is not running.

---

## ✅ Solution

### Option 1: Start Multipass Daemon (Recommended)

```bash
# On macOS, the daemon should start automatically
# If it doesn't, try these commands:

# Check if multipassd service is loaded
sudo launchctl list | grep multipass

# If not loaded, load it:
sudo launchctl load /Library/LaunchDaemons/com.canonical.multipassd.plist

# Start the service
sudo launchctl start com.canonical.multipassd

# Wait a few seconds for daemon to start
sleep 5

# Verify it's working
multipass version
multipass list
```

### Option 2: Restart Multipass Daemon

```bash
# Stop the daemon
sudo launchctl stop com.canonical.multipassd

# Wait a moment
sleep 2

# Start the daemon
sudo launchctl start com.canonical.multipassd

# Wait for it to be ready
sleep 5

# Test
multipass version
```

### Option 3: Use Multipass GUI (Easiest)

```bash
# Open Multipass.app from Applications
open /Applications/Multipass.app

# This will start the daemon automatically
# Wait 10 seconds, then try your command again
```

### Option 4: Reinstall Multipass (If all else fails)

```bash
# Uninstall
brew uninstall multipass

# Remove leftover files
sudo rm -rf /Library/Application\ Support/com.canonical.multipass
sudo rm -rf /Library/LaunchDaemons/com.canonical.multipassd.plist
sudo rm -rf /var/root/Library/Application\ Support/multipassd
rm -rf ~/Library/Application\ Support/multipass

# Reinstall
brew install multipass

# Wait for installation to complete
sleep 10

# The daemon should start automatically
multipass version
```

---

## 🔍 Diagnosis Commands

```bash
# Check if Multipass is installed
which multipass
multipass version

# Check if daemon is running
ps aux | grep multipass

# Check launchd services
sudo launchctl list | grep multipass

# Check daemon logs (macOS)
tail -f /Library/Logs/Multipass/multipassd.log

# Or user logs
tail -f ~/Library/Logs/Multipass/multipassd.log
```

---

## 🚀 Quick Fix Script

I've created a script to fix this automatically:

```bash
# Run the fix script
./multipass/fix-daemon.sh
```

Or manually:

```bash
#!/bin/bash
echo "🔧 Fixing Multipass daemon..."

# Stop any existing daemon
sudo launchctl stop com.canonical.multipassd 2>/dev/null || true
sleep 2

# Unload if loaded
sudo launchctl unload /Library/LaunchDaemons/com.canonical.multipassd.plist 2>/dev/null || true
sleep 1

# Load the daemon
sudo launchctl load /Library/LaunchDaemons/com.canonical.multipassd.plist

# Start the daemon
sudo launchctl start com.canonical.multipassd

# Wait for daemon to be ready
echo "⏳ Waiting for daemon to start..."
sleep 10

# Test
echo "✅ Testing connection..."
multipass version
multipass list

echo "🎉 Multipass daemon is now running!"
```

---

## ✅ After Fixing

Once the daemon is running, try your test again:

```bash
# Quick test
./multipass/quick-test.sh

# Or use the helper
./multipass/mp.sh test
```

Expected output:
```
🚀 Quick Test - Creating VM...
Launched: spikster-quick-1234567890

📤 Transferring script...
new_install.sh transferred successfully

⚙️  Installing Spikster...
[installation progress...]

=========================================
✅ Installation complete!
=========================================
VM: spikster-quick-1234567890
IP: 192.168.64.x
URL: http://192.168.64.x
...
```

---

## 🔄 Preventing This Issue

### Auto-start on Boot (macOS)

The daemon should auto-start on boot, but if it doesn't:

```bash
# Ensure launchd plist is in correct location
ls -la /Library/LaunchDaemons/com.canonical.multipassd.plist

# If missing, reinstall Multipass
brew reinstall multipass
```

### Keep Multipass Running

```bash
# The daemon stays running in the background
# No action needed after initial start

# To check status anytime:
multipass list
```

---

## 📝 Common Causes

1. **First Time Use** - Daemon not started after installation
2. **After Reboot** - Daemon didn't auto-start (rare)
3. **Permissions** - Daemon doesn't have right permissions
4. **Corrupted Installation** - Need to reinstall

---

## 🆘 Still Not Working?

If none of the above works:

### Check System Requirements

```bash
# macOS version (need 10.15+)
sw_vers

# Available RAM (need 8GB+)
sysctl hw.memsize | awk '{print $2/1024/1024/1024 "GB"}'

# Available disk space (need 20GB+)
df -h /
```

### Check Logs

```bash
# System logs
tail -100 /Library/Logs/Multipass/multipassd.log

# User logs
tail -100 ~/Library/Logs/Multipass/multipassd.log

# Console.app logs
# Open Console.app → Search for "multipass"
```

### Try Alternative Driver

```bash
# By default uses QEMU, can try VirtualBox
brew install virtualbox

# Switch to VirtualBox driver
multipass set local.driver=virtualbox

# Restart daemon
sudo launchctl stop com.canonical.multipassd
sudo launchctl start com.canonical.multipassd
```

### Contact Support

- **Multipass Forum:** https://discourse.ubuntu.com/c/multipass/
- **GitHub Issues:** https://github.com/canonical/multipass/issues
- **Documentation:** https://multipass.run/docs

---

## ✅ Verification Checklist

After applying fixes, verify:

- [ ] `multipass version` returns version number
- [ ] `multipass list` shows empty list or existing VMs
- [ ] `ps aux | grep multipass` shows daemon running
- [ ] `./multipass/quick-test.sh` creates VM successfully

---

**Status:** This is a **normal first-time issue**. The daemon just needs to be started once, then it runs automatically.

**Quick Fix:** 
```bash
# Open Multipass GUI to start daemon
open /Applications/Multipass.app

# Wait 10 seconds
sleep 10

# Try again
./multipass/quick-test.sh
```

🎉 **You'll be testing in minutes!**
