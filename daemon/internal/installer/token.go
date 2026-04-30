package installer

import (
	"fmt"
	"os"
	"os/exec"
	"strings"
)

const tokenFile = "/etc/spikster/daemon.token"

// EnsureDaemonToken generates a daemon auth token if one doesn't exist yet.
// The token is stored in /etc/spikster/daemon.token (mode 0600, owned by root).
// Laravel reads it via config('spikster.daemon_token') which reads from .env.
func EnsureDaemonToken() error {
	if _, err := os.Stat(tokenFile); err == nil {
		return nil // already exists
	}
	out, err := exec.Command("openssl", "rand", "-hex", "32").Output()
	if err != nil {
		return fmt.Errorf("generate daemon token: %w", err)
	}
	token := strings.TrimSpace(string(out))
	if err := os.WriteFile(tokenFile, []byte(token), 0600); err != nil {
		return fmt.Errorf("write daemon.token: %w", err)
	}
	fmt.Println("  🔑 daemon.token generated")
	return nil
}
