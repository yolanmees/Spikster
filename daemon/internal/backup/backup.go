package backup

import (
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"time"
)

const backupBase = "/home/spikster/backups"

type BackupRequest struct {
	SiteID   string
	Username string
	DBName   string
	DBPass   string
	DBRoot   string
	SiteRoot string
}

type RestoreRequest struct {
	ArchivePath string
	Username    string
	DBName      string
	DBRoot      string
	SiteRoot    string
}

// CreateFull creates a full backup (files + database)
func CreateFull(r BackupRequest) (string, error) {
	ts := time.Now().Format("20060102-150405")
	backupName := fmt.Sprintf("%s-%s-full", r.Username, ts)
	backupDir := filepath.Join(backupBase, r.SiteID, backupName)
	finalArchive := filepath.Join(backupBase, r.SiteID, backupName+".tar.gz")

	if err := os.MkdirAll(backupDir, 0750); err != nil {
		return "", fmt.Errorf("mkdir: %w", err)
	}
	defer os.RemoveAll(backupDir)

	// Files backup
	filesArchive := filepath.Join(backupDir, "files.tar.gz")
	if err := run("tar", "-czf", filesArchive, "-C", r.SiteRoot, "."); err != nil {
		return "", fmt.Errorf("tar files: %w", err)
	}

	// Database dump
	dbDump := filepath.Join(backupDir, "database.sql.gz")
	dumpCmd := fmt.Sprintf("mysqldump -uspikster -p%s %s | gzip > %s", r.DBRoot, r.DBName, dbDump)
	if err := run("bash", "-c", dumpCmd); err != nil {
		return "", fmt.Errorf("mysqldump: %w", err)
	}

	// Final archive
	if err := run("tar", "-czf", finalArchive, "-C", filepath.Dir(backupDir), backupName); err != nil {
		return "", fmt.Errorf("final archive: %w", err)
	}

	return finalArchive, nil
}

// Restore extracts a backup archive and restores files + database
func Restore(r RestoreRequest) error {
	restoreDir := filepath.Join(backupBase, "restore-tmp-"+time.Now().Format("20060102150405"))
	defer os.RemoveAll(restoreDir)

	if err := os.MkdirAll(restoreDir, 0750); err != nil {
		return err
	}

	// Extract outer archive
	if err := run("tar", "-xzf", r.ArchivePath, "-C", restoreDir); err != nil {
		return fmt.Errorf("extract archive: %w", err)
	}

	// Find inner dir
	entries, _ := os.ReadDir(restoreDir)
	if len(entries) == 0 {
		return fmt.Errorf("empty backup archive")
	}
	innerDir := filepath.Join(restoreDir, entries[0].Name())

	// Restore files
	filesArchive := filepath.Join(innerDir, "files.tar.gz")
	if _, err := os.Stat(filesArchive); err == nil {
		os.MkdirAll(r.SiteRoot, 0755)
		if err := run("tar", "-xzf", filesArchive, "-C", r.SiteRoot); err != nil {
			return fmt.Errorf("restore files: %w", err)
		}
		run("chown", "-R", r.Username+":www-data", r.SiteRoot)
	}

	// Restore database
	dbDump := filepath.Join(innerDir, "database.sql.gz")
	if _, err := os.Stat(dbDump); err == nil {
		restoreCmd := fmt.Sprintf("zcat %s | mysql -uspikster -p%s %s", dbDump, r.DBRoot, r.DBName)
		if err := run("bash", "-c", restoreCmd); err != nil {
			return fmt.Errorf("restore db: %w", err)
		}
	}

	return nil
}

func run(args ...string) error {
	out, err := exec.Command(args[0], args[1:]...).CombinedOutput()
	if err != nil {
		return fmt.Errorf("%s: %s", args[0], strings.TrimSpace(string(out)))
	}
	return nil
}
