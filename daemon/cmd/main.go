package main

import (
	"fmt"
	"os"
	"os/exec"
	"strings"

	"github.com/yolanmees/spikster/daemon/internal/installer"
	"github.com/yolanmees/spikster/daemon/internal/metrics"
	"github.com/yolanmees/spikster/daemon/internal/socket"
)

const version = "2.0.0"

func main() {
	if len(os.Args) < 2 {
		printHelp()
		os.Exit(1)
	}

	switch os.Args[1] {
	case "daemon":
		port := ""
		for i, arg := range os.Args {
			if arg == "--port" && i+1 < len(os.Args) {
				port = os.Args[i+1]
			}
		}
		go socket.StartTCP(port)
		go metrics.StartHTTPServer(":9273")
		socket.Start()

	case "install":
		resume := false
		component := ""
		for _, arg := range os.Args[2:] {
			if arg == "--resume" {
				resume = true
			} else {
				component = arg
			}
		}
		installer.Install(component, resume)

	case "repair":
		if len(os.Args) < 3 {
			fmt.Println("Usage: spikster repair <component>")
			os.Exit(1)
		}
		installer.Repair(os.Args[2])

	case "remove":
		if len(os.Args) < 3 {
			fmt.Println("Usage: spikster remove <component>")
			os.Exit(1)
		}
		installer.Remove(os.Args[2])

	case "update":
		component := ""
		if len(os.Args) > 2 {
			component = os.Args[2]
		}
		installer.Update(component)

	case "doctor":
		installer.Doctor()

	case "self-update":
		selfUpdate()

	case "version":
		fmt.Println("spikster", version)

	default:
		fmt.Println("Unknown command:", os.Args[1])
		printHelp()
		os.Exit(1)
	}
}

func selfUpdate() {
	fmt.Println("🔄 Checking for updates...")

	currentBin := "/usr/local/bin/spikster"
	versionURL := "https://github.com/yolanmees/Spikster/releases/latest/download/spikster-linux-amd64"

	// Create temp file in a protected location under /etc/spikster
	if err := os.MkdirAll("/etc/spikster", 0755); err != nil {
		fmt.Printf("❌ Cannot create /etc/spikster: %v\n", err)
		os.Exit(1)
	}

	tmpBin, err := os.CreateTemp("/etc/spikster", "spikster-update-*")
	if err != nil {
		fmt.Printf("❌ Cannot create temp file: %v\n", err)
		os.Exit(1)
	}
	tmpPath := tmpBin.Name()
	tmpBin.Close()
	defer os.Remove(tmpPath)

	// Download binary
	out, err := exec.Command("curl", "-fsSL", versionURL, "-o", tmpPath).CombinedOutput()
	if err != nil {
		fmt.Printf("❌ Download failed: %s\n", string(out))
		os.Exit(1)
	}

	// Download SHA256 checksum
	checksumURL := versionURL + ".sha256"
	checksumOut, err := exec.Command("curl", "-fsSL", checksumURL).CombinedOutput()
	if err == nil {
		// Verify checksum if available
		expected := strings.TrimSpace(string(checksumOut))
		parts := strings.SplitN(expected, " ", 2)
		if len(parts) >= 1 {
			expectedHash := parts[0]
			hashOut, err := exec.Command("sha256sum", tmpPath).CombinedOutput()
			if err == nil {
				actualParts := strings.SplitN(strings.TrimSpace(string(hashOut)), " ", 2)
				if len(actualParts) >= 1 && actualParts[0] != expectedHash {
					fmt.Printf("❌ Checksum mismatch: expected %s, got %s\n", expectedHash, actualParts[0])
					os.Exit(1)
				}
				fmt.Println("  ✅ Checksum verified")
			}
		}
	}

	// Make it executable
	if err := os.Chmod(tmpPath, 0755); err != nil {
		fmt.Printf("❌ Cannot chmod temp binary: %v\n", err)
		os.Exit(1)
	}

	// Replace binary atomically
	if err := os.Rename(tmpPath, currentBin); err != nil {
		// Fall back to mv if rename across filesystems fails
		if out, err := exec.Command("mv", tmpPath, currentBin).CombinedOutput(); err != nil {
			fmt.Printf("❌ Cannot install binary: %s\n", string(out))
			os.Exit(1)
		}
	}

	fmt.Println("📦 Restarting daemon...")
	exec.Command("systemctl", "restart", "spikster-daemon").Run()
	fmt.Println("✅ spikster updated!")
}

func printHelp() {
	fmt.Printf(`spikster %s — VPS control panel daemon

Usage:
  spikster daemon [--port 18999]   Start daemon (Unix socket + TCP)
  spikster install [component]     Install all or a specific component
  spikster install --resume        Resume interrupted installation
  spikster repair <component>      Repair a component
  spikster remove <component>      Remove a component
  spikster update [component]      Update all or a specific component
  spikster doctor                  Health check all components
  spikster self-update             Update spikster from GitHub releases
  spikster version                 Show version

Components:
  nginx, php-8.2, php-8.3, php-8.4, mysql, redis, fail2ban, certbot
`, version)
}
