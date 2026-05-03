package server

import (
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
)

// DeleteDirectory removes a directory recursively. Only allows
// paths under /home/<username>/ to prevent accidental system damage.
func DeleteDirectory(path string) error {
	if !isSafePath(path) {
		return fmt.Errorf("unsafe path: %q", path)
	}
	if err := os.RemoveAll(path); err != nil {
		return fmt.Errorf("remove %q: %w", path, err)
	}
	return nil
}

// UploadFile writes file content to the specified path.
// Only allows paths under /home/<username>/.
func UploadFile(filePath string, content []byte) error {
	if !isSafePath(filePath) {
		return fmt.Errorf("unsafe path: %q", filePath)
	}
	dir := filepath.Dir(filePath)
	if err := os.MkdirAll(dir, 0755); err != nil {
		return fmt.Errorf("mkdir %q: %w", dir, err)
	}
	if err := os.WriteFile(filePath, content, 0644); err != nil {
		return fmt.Errorf("write %q: %w", filePath, err)
	}
	return nil
}

// ChangeOwnership changes ownership of a path. Only allows paths
// under /home/<username>/.
func ChangeOwnership(path, owner, group string) error {
	if !isSafePath(path) {
		return fmt.Errorf("unsafe path: %q", path)
	}
	ownerGroup := owner
	if group != "" {
		ownerGroup = owner + ":" + group
	}
	out, err := exec.Command("chown", "-R", ownerGroup, path).CombinedOutput()
	if err != nil {
		return fmt.Errorf("chown %q: %s: %w", path, strings.TrimSpace(string(out)), err)
	}
	return nil
}

// SetPermissions sets Unix permissions on a path recursively.
// Only allows paths under /home/<username>/.
func SetPermissions(path string, mode os.FileMode) error {
	if !isSafePath(path) {
		return fmt.Errorf("unsafe path: %q", path)
	}
	if err := os.Chmod(path, mode); err != nil {
		return fmt.Errorf("chmod %q: %w", path, err)
	}
	return nil
}

func isSafePath(p string) bool {
	abs, err := filepath.Abs(p)
	if err != nil {
		return false
	}
	// Resolve symlinks to prevent bypass via symlink to /etc/
	if resolved, err := filepath.EvalSymlinks(abs); err == nil {
		abs = resolved
	}
	return strings.HasPrefix(abs, "/home/") && !strings.Contains(abs, "..")
}
