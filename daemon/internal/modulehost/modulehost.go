package modulehost

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"time"
)

// Manifest is the parsed daemon/manifest.json for a module.
type Manifest struct {
	Name    string   `json:"name"`
	Version string   `json:"version"`
	Handler string   `json:"handler"`
	Actions []string `json:"actions"`
}

// handlerEntry maps an action to the binary that handles it.
type handlerEntry struct {
	binaryPath string
}

// registry maps action → handler binary path.
var registry = map[string]handlerEntry{}

// APIContext is passed to the handler subprocess so it can call back into the daemon.
type APIContext struct {
	Socket string `json:"socket"`
	Token  string `json:"token"`
}

// handlerRequest is the JSON written to the handler's stdin.
type handlerRequest struct {
	Action string            `json:"action"`
	Params map[string]string `json:"params"`
	API    APIContext         `json:"api"`
}

// handlerResponse is the JSON read from the handler's stdout.
type handlerResponse struct {
	Success bool   `json:"success"`
	Output  string `json:"output"`
	Error   string `json:"error,omitempty"`
}

// LoadModules scans basePath/*/daemon/manifest.json and registers all handlers.
// If basePath does not exist, it logs a warning and returns without error.
func LoadModules(basePath string) {
	if _, err := os.Stat(basePath); os.IsNotExist(err) {
		log.Printf("modulehost: modules directory not found: %s (skipping)", basePath)
		return
	}

	pattern := filepath.Join(basePath, "*", "daemon", "manifest.json")
	matches, err := filepath.Glob(pattern)
	if err != nil {
		log.Printf("modulehost: glob error: %v", err)
		return
	}
	if len(matches) == 0 {
		log.Printf("modulehost: no modules found in %s", basePath)
		return
	}

	for _, manifestPath := range matches {
		data, err := os.ReadFile(manifestPath)
		if err != nil {
			log.Printf("modulehost: read manifest %s: %v", manifestPath, err)
			continue
		}

		var m Manifest
		if err := json.Unmarshal(data, &m); err != nil {
			log.Printf("modulehost: parse manifest %s: %v", manifestPath, err)
			continue
		}

		// Handler path: manifest is at Modules/<Name>/daemon/manifest.json
		// m.Handler is relative to module root, e.g. "daemon/handler"
		moduleRoot := filepath.Dir(filepath.Dir(manifestPath)) // Modules/<Name>/
		handlerBin := filepath.Join(moduleRoot, m.Handler)

		for _, action := range m.Actions {
			registry[action] = handlerEntry{binaryPath: handlerBin}
		}

		log.Printf("modulehost: loaded module %s v%s (%d actions)", m.Name, m.Version, len(m.Actions))
	}
}

// Dispatch finds the handler for action, spawns the subprocess, writes JSON to stdin,
// and reads the JSON response from stdout. Returns an error starting with
// "no module handler for action" when no handler is registered.
func Dispatch(action string, params map[string]string) (string, error) {
	entry, ok := registry[action]
	if !ok {
		return "", fmt.Errorf("no module handler for action: %s", action)
	}

	token, err := readToken()
	if err != nil {
		return "", fmt.Errorf("modulehost: read daemon token: %v", err)
	}

	req := handlerRequest{
		Action: action,
		Params: params,
		API: APIContext{
			Socket: "/var/run/spikster.sock",
			Token:  token,
		},
	}

	reqData, err := json.Marshal(req)
	if err != nil {
		return "", fmt.Errorf("modulehost: marshal request: %v", err)
	}

	ctx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()

	cmd := exec.CommandContext(ctx, entry.binaryPath)
	cmd.Stdin = strings.NewReader(string(reqData) + "\n")

	out, runErr := cmd.Output()
	if ctx.Err() == context.DeadlineExceeded {
		return "", fmt.Errorf("modulehost: handler timed out for action %s", action)
	}
	if runErr != nil {
		return "", fmt.Errorf("modulehost: handler failed for action %s: %v", action, runErr)
	}

	var resp handlerResponse
	if err := json.Unmarshal(out, &resp); err != nil {
		return "", fmt.Errorf("modulehost: parse handler response: %v", err)
	}

	if !resp.Success {
		return resp.Output, fmt.Errorf("%s", resp.Error)
	}
	return resp.Output, nil
}

// readToken reads the shared secret from /etc/spikster/daemon.token.
func readToken() (string, error) {
	data, err := os.ReadFile("/etc/spikster/daemon.token")
	if err != nil {
		return "", err
	}
	return strings.TrimSpace(string(data)), nil
}
