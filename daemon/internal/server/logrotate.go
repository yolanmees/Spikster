package server

import (
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
)

// LogRotate rotates access and error logs for all sites on the server.
func LogRotate(day string) (string, error) {
	homeDirs, err := os.ReadDir("/home")
	if err != nil {
		return "", fmt.Errorf("read /home: %w", err)
	}

	var rotated int
	for _, entry := range homeDirs {
		if !entry.IsDir() {
			continue
		}
		username := entry.Name()
		if username == "spikster" || strings.HasPrefix(username, ".") {
			continue
		}

		logDir := filepath.Join("/home", username, "log")
		if _, err := os.Stat(logDir); os.IsNotExist(err) {
			continue
		}

		// Rotate access log
		accessLog := filepath.Join(logDir, "access.log")
		accessBk := filepath.Join(logDir, fmt.Sprintf("access_bk_%s.log", day))
		rotateFile(accessLog, accessBk)

		// Rotate error log
		errorLog := filepath.Join(logDir, "error.log")
		errorBk := filepath.Join(logDir, fmt.Sprintf("error_bk_%s.log", day))
		rotateFile(errorLog, errorBk)

		// Chown to site user
		exec.Command("chown", username+":www-data", accessBk, accessLog, errorBk, errorLog).Run()
		rotated++
	}

	// Reload nginx to reopen log files
	exec.Command("systemctl", "reload", "nginx").Run()

	return fmt.Sprintf("%d sites rotated", rotated), nil
}

func rotateFile(logFile, backupFile string) {
	// Remove old backup if exists
	os.Remove(backupFile)

	// Move current log to backup
	if _, err := os.Stat(logFile); err == nil {
		os.Rename(logFile, backupFile)
	}

	// Create new empty log file
	f, err := os.Create(logFile)
	if err != nil {
		return
	}
	f.Close()
}
