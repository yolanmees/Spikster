package backup

import (
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strconv"
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
	// Pre-flight: check available disk space (require at least 512MB free)
	if freeBytes, err := diskFreeBytes(backupBase); err == nil && freeBytes < 512*1024*1024 {
		return "", fmt.Errorf("not enough disk space: only %dMB free in backup dir", freeBytes/1024/1024)
	}

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

	// Database dump — use credentials file, never -p on cmdline
	dbDump := filepath.Join(backupDir, "database.sql.gz")
	if err := mysqldumpWithCreds(r.DBRoot, r.DBName, dbDump); err != nil {
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

	// Extract outer archive with path sanitization
	// List archive contents first to check for path traversal
	listOut, listErr := exec.Command("tar", "-tzf", r.ArchivePath).CombinedOutput()
	if listErr != nil {
		return fmt.Errorf("list archive: %w", listErr)
	}
	for _, entry := range strings.Split(strings.TrimSpace(string(listOut)), "\n") {
		if strings.Contains(entry, "..") || strings.HasPrefix(entry, "/") {
			return fmt.Errorf("archive contains unsafe path: %q", entry)
		}
	}

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

	// Restore database — use credentials file
	dbDump := filepath.Join(innerDir, "database.sql.gz")
	if _, err := os.Stat(dbDump); err == nil {
		if err := mysqlRestoreWithCreds(r.DBRoot, r.DBName, dbDump); err != nil {
			return fmt.Errorf("restore db: %w", err)
		}
	}

	return nil
}

// ─── MySQL credential helpers ─────────────────────────────────────────────────

// mysqldumpWithCreds dumps a database using a temp credentials file.
func mysqldumpWithCreds(rootPass, dbName, destGz string) error {
	cnfFile, cleanup, err := writeTempCnf(rootPass)
	if err != nil {
		return err
	}
	defer cleanup()

	outFile, err := os.Create(destGz)
	if err != nil {
		return err
	}
	defer outFile.Close()

	dump := exec.Command("mysqldump",
		"--defaults-extra-file="+cnfFile,
		"--single-transaction", "--quick",
		dbName,
	)
	gzip := exec.Command("gzip")
	gzip.Stdout = outFile

	pipe, err := dump.StdoutPipe()
	if err != nil {
		return err
	}
	gzip.Stdin = pipe

	if err := gzip.Start(); err != nil {
		return err
	}
	if err := dump.Run(); err != nil {
		return err
	}
	return gzip.Wait()
}

// mysqlRestoreWithCreds restores a gzipped SQL dump.
func mysqlRestoreWithCreds(rootPass, dbName, srcGz string) error {
	cnfFile, cleanup, err := writeTempCnf(rootPass)
	if err != nil {
		return err
	}
	defer cleanup()

	zcat := exec.Command("zcat", srcGz)
	mysql := exec.Command("mysql", "--defaults-extra-file="+cnfFile, dbName)

	pipe, err := zcat.StdoutPipe()
	if err != nil {
		return err
	}
	mysql.Stdin = pipe

	if err := mysql.Start(); err != nil {
		return err
	}
	if err := zcat.Run(); err != nil {
		return err
	}
	return mysql.Wait()
}

// writeTempCnf writes a MySQL credentials file to a temp path.
// Returns the file path and a cleanup func.
func writeTempCnf(pass string) (string, func(), error) {
	tmp, err := os.CreateTemp("", "spikster-mysql-*.cnf")
	if err != nil {
		return "", nil, err
	}
	fmt.Fprintf(tmp, "[client]\nuser=spikster\npassword=%s\n", pass)
	tmp.Close()
	os.Chmod(tmp.Name(), 0600)
	return tmp.Name(), func() { os.Remove(tmp.Name()) }, nil
}

// diskFreeBytes returns available bytes on the filesystem containing path.
func diskFreeBytes(path string) (uint64, error) {
	os.MkdirAll(path, 0750)
	out, err := exec.Command("df", "--output=avail", "-B1", path).Output()
	if err != nil {
		return 0, err
	}
	lines := strings.Split(strings.TrimSpace(string(out)), "\n")
	if len(lines) < 2 {
		return 0, fmt.Errorf("unexpected df output")
	}
	n, err := strconv.ParseUint(strings.TrimSpace(lines[1]), 10, 64)
	return n, err
}

func run(args ...string) error {
	out, err := exec.Command(args[0], args[1:]...).CombinedOutput()
	if err != nil {
		return fmt.Errorf("%s: %s", args[0], strings.TrimSpace(string(out)))
	}
	return nil
}

// ─── Upload helpers ─────────────────────────────────────────────────────────

type UploadS3Params struct {
	FilePath     string
	Bucket       string
	Region       string
	AccessKey    string
	SecretKey    string
	S3Key        string
	Endpoint     string
	StorageClass string
}

func UploadToS3(p UploadS3Params) error {
	// Set AWS credentials via env (never CLI args)
	cmd := exec.Command("aws", "s3", "cp", p.FilePath, "s3://"+p.Bucket+"/"+p.S3Key)
	cmd.Env = append(os.Environ(),
		"AWS_ACCESS_KEY_ID="+p.AccessKey,
		"AWS_SECRET_ACCESS_KEY="+p.SecretKey,
		"AWS_DEFAULT_REGION="+p.Region,
	)
	if p.Endpoint != "" {
		cmd.Env = append(cmd.Env, "AWS_ENDPOINT_URL="+p.Endpoint)
	}
	if err := cmd.Run(); err != nil {
		return fmt.Errorf("s3 upload: %w", err)
	}
	return nil
}

type UploadFTPParams struct {
	FilePath   string
	FileName   string
	Host       string
	Port       string
	Username   string
	Password   string
	RemotePath string
	Passive    bool
}

func UploadToFTP(p UploadFTPParams) error {
	passiveFlag := ""
	if p.Passive {
		passiveFlag = "--ssl"
	}
	url := fmt.Sprintf("ftp://%s:%s@%s:%s%s/%s", p.Username, p.Password, p.Host, p.Port, p.RemotePath, p.FileName)
	cmd := exec.Command("curl", "-s", "-S", "-T", p.FilePath, passiveFlag, url)
	if out, err := cmd.CombinedOutput(); err != nil {
		return fmt.Errorf("ftp upload: %s: %w", strings.TrimSpace(string(out)), err)
	}
	return nil
}
