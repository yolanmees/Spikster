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
		dangerous := []string{
			"rm -rf ", "rm -r -f ", "rm -fr ", "mkfs.", "mkfs ", "dd if=", "dd<",
			">/dev/sd", ">>/dev/sd", ">/dev/hd", ">/dev/nvme", ">/dev/vd",
			">/dev/md", ">/dev/loop", ">/dev/xvd",
			"wget ", "curl ", "nc ", "ncat ",
			"chmod 777", "chmod -R 777",
			":(){ :|:& };:", ".() { .|.& };.",
			"\\x", "base64 -d", "base64 --decode",
			"sh -c", "bash -c", "zsh -c", "dash -c",
			"python -c", "python3 -c", "perl -e", "ruby -e", "php -r",
			"iptables -F", "ufw disable", "reboot", "shutdown", "halt",
			"kill -9 -1", "killall -9",
			"$(id)", "$(whoami)", "$(curl", "$(wget", "$(bash", "$(sh",
			"`id`", "`whoami`", "`curl", "`wget", "`bash", "`sh",
		}
		lower := strings.ToLower(line)
		for _, d := range dangerous {
			if strings.Contains(lower, d) {
				return fmt.Errorf("cron line %d: command blocked for security", i+1)
			}
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
