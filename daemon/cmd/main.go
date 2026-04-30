package main

import (
	"fmt"
	"os"
	"os/exec"

	"github.com/yolanmees/spikster/daemon/internal/installer"
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

	tmpBin := "/tmp/spikster-new"
	currentBin := "/usr/local/bin/spikster"

	steps := []struct {
		name string
		args []string
	}{
		{"download", []string{"curl", "-fsSL",
			"https://github.com/yolanmees/Spikster/releases/latest/download/spikster-linux-amd64",
			"-o", tmpBin}},
		{"chmod", []string{"chmod", "+x", tmpBin}},
		{"install", []string{"mv", tmpBin, currentBin}},
	}

	for _, step := range steps {
		out, err := exec.Command(step.args[0], step.args[1:]...).CombinedOutput()
		if err != nil {
			fmt.Printf("❌ Failed at [%s]: %s\n", step.name, string(out))
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
