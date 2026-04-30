package installer

import (
	"encoding/json"
	"fmt"
	"os"
	"path/filepath"
	"time"

	"github.com/yolanmees/spikster/daemon/internal/components"
)

const stateFile = "/etc/spikster/.install-state"

type State struct {
	Installed map[string]string `json:"installed"` // component -> timestamp
}

func loadState() State {
	s := State{Installed: map[string]string{}}
	data, err := os.ReadFile(stateFile)
	if err != nil {
		return s
	}
	json.Unmarshal(data, &s)
	return s
}

func saveState(s State) {
	os.MkdirAll(filepath.Dir(stateFile), 0755)
	data, _ := json.MarshalIndent(s, "", "  ")
	os.WriteFile(stateFile, data, 0644)
}

// Registry of all available components
var registry = map[string]func() components.Component{
	"nginx":    func() components.Component { return &Nginx{} },
	"php-8.2":  func() components.Component { return &PHP{Version: "8.2"} },
	"php-8.3":  func() components.Component { return &PHP{Version: "8.3"} },
	"php-8.4":  func() components.Component { return &PHP{Version: "8.4"} },
	"mysql":    func() components.Component { return &MySQL{} },
	"redis":    func() components.Component { return &Redis{} },
	"fail2ban": func() components.Component { return &Fail2ban{} },
	"certbot":  func() components.Component { return &Certbot{} },
}

// defaultOrder defines install sequence
var defaultOrder = []string{
	"mysql", "nginx", "php-8.2", "php-8.3", "php-8.4",
	"redis", "fail2ban", "certbot",
}

func Install(component string, resume bool) {
	state := loadState()

	if component != "" {
		c, ok := registry[component]
		if !ok {
			fmt.Printf("❌ Unknown component: %s\n", component)
			os.Exit(1)
		}
		runInstall(c(), state)
		return
	}

	fmt.Println("🚀 Installing Spikster...\n")
	for _, name := range defaultOrder {
		if resume {
			if _, done := state.Installed[name]; done {
				fmt.Printf("  ⏭  %s (already installed)\n", name)
				continue
			}
		}
		c := registry[name]()
		if err := runInstall(c, state); err != nil {
			fmt.Printf("\n❌ Failed at: %s\nRun: spikster install --resume to continue\n", name)
			os.Exit(1)
		}
	}
	fmt.Println("\n✅ Installation complete!")
}

func runInstall(c components.Component, state State) error {
	fmt.Printf("  📦 Installing %s... ", c.Name())
	if err := c.Install(); err != nil {
		fmt.Println("❌")
		return err
	}
	state.Installed[c.Name()] = time.Now().Format(time.RFC3339)
	saveState(state)
	fmt.Println("✅")
	return nil
}

func Repair(component string) {
	c, ok := registry[component]
	if !ok {
		fmt.Printf("❌ Unknown component: %s\n", component)
		os.Exit(1)
	}
	fmt.Printf("🔧 Repairing %s... ", component)
	if err := c().Repair(); err != nil {
		fmt.Println("❌ ", err)
		os.Exit(1)
	}
	fmt.Println("✅")
}

func Remove(component string) {
	c, ok := registry[component]
	if !ok {
		fmt.Printf("❌ Unknown component: %s\n", component)
		os.Exit(1)
	}
	fmt.Printf("🗑  Removing %s... ", component)
	if err := c().Remove(); err != nil {
		fmt.Println("❌ ", err)
		os.Exit(1)
	}
	state := loadState()
	delete(state.Installed, component)
	saveState(state)
	fmt.Println("✅")
}

func Update(component string) {
	if component != "" {
		Repair(component)
		return
	}
	state := loadState()
	fmt.Println("🔄 Updating all components...\n")
	for name := range state.Installed {
		Repair(name)
	}
}

func Doctor() {
	fmt.Println("🩺 Spikster health check\n")
	allOk := true
	for name, factory := range registry {
		c := factory()
		ok, msg := c.Healthcheck()
		if ok {
			fmt.Printf("  ✅ %-12s %s\n", name, msg)
		} else {
			fmt.Printf("  ❌ %-12s %s\n", name, msg)
			allOk = false
		}
	}
	if allOk {
		fmt.Println("\n✅ All good!")
	} else {
		fmt.Println("\n⚠️  Some components need attention. Run: spikster repair <component>")
	}
}
