# Spikster Monitoring System - Deployment Guide

## 🎯 Overview

This guide covers deploying the new lightweight Go-based monitoring system to replace Glances.

## 📋 Prerequisites

- Laravel panel running on main server
- Queue worker active (`php artisan queue:work`)
- Cron scheduler running
- SSH access to remote servers

---

## 🚀 Deployment Steps

### 1️⃣ Update Panel Server (Control Panel)

#### A. Run Database Migration

```bash
cd /var/www/html
php artisan migrate
```

**Expected output:**
```
INFO  Running migrations.
2025_10_05_000001_create_server_metrics_table ........ DONE
```

#### B. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

#### C. Restart Queue Worker

```bash
# If using Supervisor
sudo supervisorctl restart spikster-worker:*

# Or restart manually
pkill -f "artisan queue:work"
php artisan queue:work --daemon &
```

#### D. Verify Scheduler

```bash
php artisan schedule:list
```

**Look for:**
- `fetch-server-metrics` - Runs every minute
- `cleanup-old-metrics` - Runs daily at 03:00

---

### 2️⃣ Deploy to New Servers (Automatic)

**For newly installed servers**, the monitoring agent is automatically installed via `go.sh`:

```bash
# Run the installation script on a fresh server
wget -O - https://raw.githubusercontent.com/yolanmees/Spikster/laravel-12/go.sh | bash
```

The script will:
1. Install Go 1.21.5
2. Download spikster-agent source
3. Build the binary
4. Install systemd service
5. Start the agent
6. Configure firewall (port 9273)

---

### 3️⃣ Deploy to Existing Servers (Manual)

For servers already running with Glances, follow these steps:

#### A. SSH into the Server

```bash
ssh spikster@YOUR_SERVER_IP
```

#### B. Stop and Disable Glances

```bash
# Kill any running Glances processes
sudo pkill -f glances

# Remove from startup (if configured)
sudo systemctl disable glances 2>/dev/null || true
sudo systemctl stop glances 2>/dev/null || true
```

#### C. Install Go (if not present)

```bash
# Check if Go is installed
if ! command -v go &> /dev/null; then
    wget https://go.dev/dl/go1.21.5.linux-amd64.tar.gz
    sudo rm -rf /usr/local/go
    sudo tar -C /usr/local -xzf go1.21.5.linux-amd64.tar.gz
    rm go1.21.5.linux-amd64.tar.gz
    export PATH=$PATH:/usr/local/go/bin
    echo 'export PATH=$PATH:/usr/local/go/bin' | sudo tee -a /etc/profile
fi
```

#### D. Download and Install Agent

```bash
# Create temp directory
mkdir -p /tmp/spikster-agent-install
cd /tmp/spikster-agent-install

# Download agent files
wget https://raw.githubusercontent.com/yolanmees/Spikster/laravel-12/spikster-agent/main.go
wget https://raw.githubusercontent.com/yolanmees/Spikster/laravel-12/spikster-agent/go.mod
wget https://raw.githubusercontent.com/yolanmees/Spikster/laravel-12/spikster-agent/spikster-agent.service

# Build agent
go mod download
go build -ldflags="-s -w" -o spikster-agent main.go

# Install binary
sudo install -m 755 spikster-agent /usr/local/bin/spikster-agent

# Install systemd service
sudo install -m 644 spikster-agent.service /etc/systemd/system/spikster-agent.service

# Enable and start service
sudo systemctl daemon-reload
sudo systemctl enable spikster-agent.service
sudo systemctl start spikster-agent.service

# Cleanup
cd /
rm -rf /tmp/spikster-agent-install
```

#### E. Configure Firewall

```bash
# Allow port 9273 (monitoring agent)
sudo ufw allow 9273/tcp comment 'Spikster Monitoring Agent'
```

#### F. Verify Installation

```bash
# Check service status
sudo systemctl status spikster-agent

# Test health endpoint
curl http://localhost:9273/health

# Test metrics endpoint
curl http://localhost:9273/metrics
```

**Expected output from `/health`:**
```json
{
  "status": "healthy",
  "timestamp": "2025-10-05T14:30:00Z"
}
```

**Expected output from `/metrics`:**
```json
{
  "timestamp": "2025-10-05T14:30:00Z",
  "cpu": {
    "percent": 12.5,
    "cores": 4
  },
  "memory": {
    "total": 8589934592,
    "used": 4294967296,
    "percent": 50.0
  },
  ...
}
```

---

## 🔍 Verification & Testing

### Panel Server Checks

#### 1. Check Queue Jobs

```bash
# Watch queue processing
php artisan queue:listen --timeout=0

# Check failed jobs
php artisan queue:failed
```

#### 2. Verify Database

```bash
php artisan tinker
```

```php
// Check if metrics are being collected
\App\Models\ServerMetric::latest()->first();

// Count metrics for a server
\App\Models\ServerMetric::where('server_id', 1)->count();

// Check latest metrics per server
\App\Models\Server::with('latestMetric')->get();
```

#### 3. Test API Endpoint

```bash
# Replace SERVER_ID with actual server_id
curl http://YOUR_PANEL_IP/api/servers/SERVER_ID/healthy
```

### Remote Server Checks

#### 1. Service Status

```bash
sudo systemctl status spikster-agent
```

**Should show:**
- ✅ Active: active (running)
- ✅ Loaded from /etc/systemd/system/spikster-agent.service

#### 2. Check Logs

```bash
# View agent logs
sudo journalctl -u spikster-agent -f

# Check for errors
sudo journalctl -u spikster-agent --since "1 hour ago" | grep -i error
```

#### 3. Resource Usage

```bash
# Check agent CPU/Memory usage
ps aux | grep spikster-agent
```

**Expected:**
- CPU: <1%
- Memory: 5-10MB

#### 4. Port Listening

```bash
# Verify port 9273 is open
sudo netstat -tlnp | grep 9273
# OR
sudo ss -tlnp | grep 9273
```

---

## 🎨 Frontend Verification

### Dashboard Stats

1. Navigate to: `http://YOUR_PANEL_IP/servers/{server_id}/edit`
2. Click on **Stats** tab
3. Verify charts are showing:
   - ✅ CPU Usage
   - ✅ Memory Usage
   - ✅ Disk Usage
   - ✅ Load Average

### Livewire Components

Inspect browser console for any errors. Charts should:
- Load within 2 seconds
- Show last 24 hours of data
- Update smoothly without SSH calls

---

## 🐛 Troubleshooting

### Agent Not Starting

**Problem:** `systemctl status spikster-agent` shows failed

```bash
# Check detailed logs
sudo journalctl -u spikster-agent -n 50 --no-pager

# Common issues:
# 1. Port already in use
sudo lsof -i :9273

# 2. Binary missing
ls -la /usr/local/bin/spikster-agent

# 3. Service file issues
sudo systemctl cat spikster-agent
```

### No Metrics in Database

**Problem:** `server_metrics` table is empty

```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# Check queue jobs
php artisan queue:work --once

# Check for failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Firewall Blocking

**Problem:** Panel can't reach agent

```bash
# On remote server - check firewall
sudo ufw status

# Allow port 9273
sudo ufw allow 9273/tcp

# Test from panel server
curl http://REMOTE_SERVER_IP:9273/health
```

### High CPU Usage

**Problem:** Agent using too much CPU

```bash
# Check agent status
sudo systemctl status spikster-agent

# Restart agent
sudo systemctl restart spikster-agent

# Check logs for errors
sudo journalctl -u spikster-agent -f
```

---

## 🔄 Migration from Glances

### Before Migration

1. **Backup existing stats** (optional):
   ```bash
   # Export stats tables
   php artisan tinker
   \App\Models\Stats\Cpu::all()->toJson();
   # Save output to file
   ```

2. **Document current monitoring**:
   - List servers being monitored
   - Note any custom Glances configurations
   - Check if any other services depend on Glances

### During Migration

1. **Deploy to one test server first**
2. **Verify metrics for 24 hours**
3. **Compare with old Glances data**
4. **Roll out to remaining servers**

### After Migration

1. **Remove Glances completely**:
   ```bash
   sudo pkill -f glances
   pip uninstall glances -y
   sudo rm -f /var/log/glances.log
   ```

2. **Remove old stats commands** (already done in scheduler):
   - ~~spikster:stats-get-cpu~~
   - ~~spikster:stats-get-mem~~
   - ~~spikster:stats-get-load~~
   - ~~spikster:stats-get-disk~~

3. **Archive old stats tables** (optional):
   ```sql
   RENAME TABLE stats_cpu TO stats_cpu_old;
   RENAME TABLE stats_mem TO stats_mem_old;
   RENAME TABLE stats_load TO stats_load_old;
   RENAME TABLE stats_disk TO stats_disk_old;
   ```

---

## 📊 Performance Comparison

| Metric | Glances (Old) | spikster-agent (New) |
|--------|---------------|----------------------|
| **CPU Usage** | 2-5% | <1% |
| **Memory** | 50-100MB | 5-10MB |
| **Response Time** | 100-500ms | <10ms |
| **Reliability** | Crashes frequently | Auto-restart via systemd |
| **Data Collection** | SSH calls | HTTP API |
| **Port Security** | 61208 exposed | 9273 (can be firewalled) |

---

## 🎯 Success Criteria

✅ All servers show metrics in dashboard  
✅ Charts display 24h data  
✅ No SSH overhead on dashboard loads  
✅ Queue jobs processing smoothly  
✅ Agent CPU < 1%, Memory < 10MB  
✅ No failed jobs in queue  
✅ Auto-cleanup running daily  

---

## 📞 Support

If you encounter issues:

1. Check logs: `sudo journalctl -u spikster-agent -f`
2. Verify queue: `php artisan queue:listen`
3. Test endpoint: `curl http://localhost:9273/metrics`
4. Review docs: `MONITORING_ANALYSIS_AND_IMPROVEMENTS.md`

---

## 🔐 Security Notes

- Port 9273 should only be accessible from panel IP
- Configure firewall rules accordingly:
  ```bash
  # Allow only from panel IP
  sudo ufw allow from PANEL_IP to any port 9273 proto tcp
  ```

- Agent runs as `spikster` user (non-root)
- Systemd hardening enabled (see service file)
- No external dependencies or network access required

---

**Last Updated:** October 5, 2025  
**Version:** 1.0.0  
**Author:** Spikster Team
