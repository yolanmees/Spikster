package ftp

import (
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strconv"
	"strings"
)

// FTPUserExt holds full FTP user details for quota and tests.
type FTPUserExt struct {
	Username string
	Password string
	HomeDir  string
}

// GetDiskUsage returns disk usage statistics for an FTP user's home directory.
func GetDiskUsage(username string) (map[string]int64, error) {
	homeDir := filepath.Join("/home", username)
	if _, err := os.Stat(homeDir); os.IsNotExist(err) {
		return nil, fmt.Errorf("home directory not found for %q", username)
	}

	out, err := exec.Command("du", "-sb", homeDir).Output()
	if err != nil {
		return nil, fmt.Errorf("du: %w", err)
	}

	fields := strings.Fields(string(out))
	if len(fields) < 1 {
		return nil, fmt.Errorf("unexpected du output")
	}
	bytes, err := strconv.ParseInt(fields[0], 10, 64)
	if err != nil {
		return nil, err
	}

	return map[string]int64{
		"bytes":      bytes,
		"kilobytes":  bytes / 1024,
		"megabytes":  bytes / 1024 / 1024,
		"gigabytes":  bytes / 1024 / 1024 / 1024,
	}, nil
}

// UpdateQuota sets the disk quota for an FTP user.
func UpdateQuota(username string, quotaMB int) error {
	if quotaMB < 0 {
		return fmt.Errorf("quota must be non-negative")
	}
	// Set filesystem quota via setquota (block limits)
	// Convert MB to 1K blocks
	blocks := quotaMB * 1024
	soft := blocks
	hard := blocks + blocks/10 // 10% grace

	out, err := exec.Command("setquota", "-u", username,
		strconv.Itoa(soft), strconv.Itoa(hard),
		"0", "0",
		"/",
	).CombinedOutput()
	if err != nil {
		return fmt.Errorf("setquota: %s: %w", strings.TrimSpace(string(out)), err)
	}
	return nil
}

// TestConnection tests if FTP is reachable on the local server.
func TestConnection(username, password string) error {
	// Test by attempting a local FTP connection using curl
	cmd := exec.Command("curl", "-s", "-S", "--insecure",
		"--ftp-ssl", "-u", username+":"+password,
		"--max-time", "10",
		"ftp://localhost/",
	)
	out, err := cmd.CombinedOutput()
	if err != nil {
		return fmt.Errorf("ftp connection test failed: %s", strings.TrimSpace(string(out)))
	}
	return nil
}
