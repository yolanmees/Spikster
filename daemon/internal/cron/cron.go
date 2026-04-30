package cron

import (
	"fmt"
	"os"
	"os/exec"
	"strings"
)

const cronFile = "/etc/cron.d/spikster.crontab"

// Write writes a crontab string to the cron file and reloads cron
func Write(content string) error {
	if err := os.WriteFile(cronFile, []byte(content), 0644); err != nil {
		return fmt.Errorf("write cron file: %w", err)
	}
	if err := run("crontab", cronFile); err != nil {
		return fmt.Errorf("crontab load: %w", err)
	}
	// Try reload, fall back to restart
	if run("systemctl", "reload", "cron") != nil {
		run("systemctl", "restart", "cron")
	}
	return nil
}

// Read returns the current crontab content
func Read() (string, error) {
	data, err := os.ReadFile(cronFile)
	if err != nil {
		if os.IsNotExist(err) {
			return "", nil
		}
		return "", err
	}
	return string(data), nil
}

func run(args ...string) error {
	out, err := exec.Command(args[0], args[1:]...).CombinedOutput()
	if err != nil {
		return fmt.Errorf("%s: %s", args[0], strings.TrimSpace(string(out)))
	}
	return nil
}
