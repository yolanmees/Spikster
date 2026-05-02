package cron

import (
	"fmt"
	"os"
	"os/exec"
	"regexp"
	"strings"
)

const cronFile = "/etc/cron.d/spikster.crontab"

// validCronLine validates a single cron entry line.
// Format: minute hour day month weekday user command
// All time fields must be valid cron expressions (numbers, ranges, steps, lists)
var validCronLine = regexp.MustCompile(`^@(reboot|yearly|annually|monthly|weekly|daily|midnight|hourly)\s+\S+\s+.+$`)

// Write writes a crontab string to the cron file and reloads cron
func Write(content string) error {
	// Validate each non-empty, non-comment line
	lines := strings.Split(content, "\n")
	for i, line := range lines {
		line = strings.TrimSpace(line)
		if line == "" || strings.HasPrefix(line, "#") {
			continue
		}
		// Check for dangerous commands
		if strings.Contains(line, "rm -rf /") || strings.Contains(line, "mkfs") ||
			strings.Contains(line, "dd if=") || strings.Contains(line, "> /dev/sd") {
			return fmt.Errorf("cron line %d: command blocked for security", i+1)
		}
		// Validate basic structure (5-6 time fields + user + command)
		fields := strings.Fields(line)
		if len(fields) < 7 {
			return fmt.Errorf("cron line %d: invalid format, need at least 7 fields", i+1)
		}
		// Time fields must only contain valid cron characters
		timeFields := fields[:5]
		timePattern := regexp.MustCompile(`^[0-9\*\-,/]+$`)
		for j, tf := range timeFields {
			if !timePattern.MatchString(tf) {
				return fmt.Errorf("cron line %d: invalid time field %d: %q", i+1, j+1, tf)
			}
		}
	}
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
