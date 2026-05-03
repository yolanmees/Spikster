package backup

import (
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
)

// EncryptBackup encrypts a backup archive using AES-256-GCM via openssl.
// The encrypted file is written to <path>.enc and the plaintext is removed.
// Password is passed via environment variable to avoid exposure in process list.
func EncryptBackup(filePath, password string) (string, error) {
	if _, err := os.Stat(filePath); os.IsNotExist(err) {
		return "", fmt.Errorf("file not found: %q", filePath)
	}

	if !isSafeBackupPath(filePath) {
		return "", fmt.Errorf("encrypt: unsafe path: %q", filePath)
	}

	encPath := filePath + ".enc"

	cmd := exec.Command("openssl", "enc", "-aes-256-gcm",
		"-pbkdf2", "-iter", "100000",
		"-in", filePath,
		"-out", encPath,
		"-pass", "env:SPIKSTER_ENC_PASS",
	)
	cmd.Env = append(os.Environ(), "SPIKSTER_ENC_PASS="+password)
	out, err := cmd.CombinedOutput()
	if err != nil {
		return "", fmt.Errorf("openssl encrypt: %s: %w", strings.TrimSpace(string(out)), err)
	}

	os.Remove(filePath)

	return encPath, nil
}

// DecryptBackup decrypts an encrypted backup archive.
func DecryptBackup(filePath, password string) (string, error) {
	if _, err := os.Stat(filePath); os.IsNotExist(err) {
		return "", fmt.Errorf("file not found: %q", filePath)
	}

	if !isSafeBackupPath(filePath) {
		return "", fmt.Errorf("decrypt: unsafe path: %q", filePath)
	}

	ext := filepath.Ext(filePath)
	var decPath string
	if ext == ".enc" {
		decPath = strings.TrimSuffix(filePath, ".enc")
	} else {
		decPath = filePath + ".dec"
	}

	cmd := exec.Command("openssl", "enc", "-d", "-aes-256-gcm",
		"-pbkdf2", "-iter", "100000",
		"-in", filePath,
		"-out", decPath,
		"-pass", "env:SPIKSTER_ENC_PASS",
	)
	cmd.Env = append(os.Environ(), "SPIKSTER_ENC_PASS="+password)
	out, err := cmd.CombinedOutput()
	if err != nil {
		return "", fmt.Errorf("openssl decrypt: %s: %w", strings.TrimSpace(string(out)), err)
	}

	return decPath, nil
}

func isSafeBackupPath(p string) bool {
	abs, err := filepath.Abs(p)
	if err != nil {
		return false
	}
	return strings.HasPrefix(abs, "/home/spikster/backups/") && !strings.Contains(abs, "..")
}
