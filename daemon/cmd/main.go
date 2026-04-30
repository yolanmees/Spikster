package main

import (
	"fmt"
	"os"

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
		socket.Start()
	case "install":
		component := ""
		if len(os.Args) > 2 {
			component = os.Args[2]
		}
		installer.Install(component, false)
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
	case "version":
		fmt.Println("spikster", version)
	default:
		fmt.Println("Unknown command:", os.Args[1])
		printHelp()
		os.Exit(1)
	}
}

func printHelp() {
	fmt.Printf(`spikster %s — VPS control panel daemon

Usage:
  spikster daemon                  Start the daemon (used by systemd)
  spikster install [component]     Install all or a specific component
  spikster repair <component>      Repair a component
  spikster remove <component>      Remove a component
  spikster update [component]      Update all or a specific component
  spikster doctor                  Check health of all components

Components:
  nginx, php-8.2, php-8.3, php-8.4, mysql, redis, fail2ban, certbot

Examples:
  spikster install
  spikster install php-8.3
  spikster repair nginx
  spikster doctor
`, version)
}
