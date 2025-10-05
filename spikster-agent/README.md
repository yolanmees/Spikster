# Spikster Agent

Lightweight system monitoring agent for Spikster control panel.

## Features

- **Lightweight**: ~5MB binary, minimal CPU/RAM usage
- **Fast**: HTTP API responses in <10ms
- **Reliable**: Auto-restart via systemd, no Python dependencies
- **Secure**: Read-only metrics, no command execution

## Metrics Collected

- CPU usage (%)
- Memory usage (total, used, free, cached, %)
- Disk usage (total, used, free, %)
- System load (1m, 5m, 15m)
- Network I/O (bytes/packets sent/received)
- System uptime

## Endpoints

- `GET /` - Service information
- `GET /health` - Health check
- `GET /metrics` - All system metrics (JSON)

## Installation

### Quick Install

```bash
# Download and install
curl -sSL https://raw.githubusercontent.com/spikster/agent/main/install.sh | sudo bash
```

### Manual Installation

1. **Download binary**:
```bash
wget https://github.com/spikster/agent/releases/latest/download/spikster-agent-linux-amd64 \
     -O /usr/local/bin/spikster-agent
chmod +x /usr/local/bin/spikster-agent
```

2. **Create systemd service**:
```bash
sudo cp spikster-agent.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable spikster-agent
sudo systemctl start spikster-agent
```

3. **Verify**:
```bash
curl http://localhost:9273/health
```

## Building from Source

### Prerequisites

- Go 1.21 or higher

### Build

```bash
# Install dependencies
go mod download

# Build
./build.sh

# Output in build/ directory
```

### Development

```bash
# Run locally
go run main.go

# Test
curl http://localhost:9273/metrics
```

## Configuration

The agent runs on port `9273` by default. To change:

Edit `/etc/systemd/system/spikster-agent.service`:
```ini
Environment="PORT=:9274"
```

Then reload:
```bash
sudo systemctl daemon-reload
sudo systemctl restart spikster-agent
```

## Firewall

The agent only needs to be accessible from your Spikster control panel:

```bash
# Allow from specific IP (replace with your panel IP)
sudo ufw allow from 1.2.3.4 to any port 9273

# Or allow from local network only
sudo ufw allow from 192.168.1.0/24 to any port 9273
```

## Monitoring

### Check status
```bash
sudo systemctl status spikster-agent
```

### View logs
```bash
sudo journalctl -u spikster-agent -f
```

### Resource usage
```bash
ps aux | grep spikster-agent
```

Expected usage:
- Memory: 5-10 MB
- CPU: <1%

## Troubleshooting

### Agent not responding

```bash
# Check if running
sudo systemctl status spikster-agent

# Restart
sudo systemctl restart spikster-agent

# Check logs
sudo journalctl -u spikster-agent -n 50
```

### High resource usage

The agent should use minimal resources. If not:
1. Check for system issues (high overall load)
2. Restart the agent
3. Check logs for errors

### Port already in use

```bash
# Check what's using port 9273
sudo lsof -i :9273

# Change port in service file (see Configuration)
```

## Uninstall

```bash
sudo systemctl stop spikster-agent
sudo systemctl disable spikster-agent
sudo rm /etc/systemd/system/spikster-agent.service
sudo rm /usr/local/bin/spikster-agent
sudo systemctl daemon-reload
```

## Security

- Agent runs as non-privileged user (spikster)
- Read-only metrics (no command execution)
- No authentication required (internal use only)
- Should NOT be exposed to public internet

## License

MIT License - see LICENSE file

## Support

- GitHub Issues: https://github.com/spikster/agent/issues
- Documentation: https://docs.spikster.com
