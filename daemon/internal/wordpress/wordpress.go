package wordpress

import (
	"crypto/rand"
	"encoding/hex"
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
)

// allowedSubcommands mirrors the PHP-side validation in WPCLIService.
var allowedSubcommands = map[string]bool{
	"core": true, "plugin": true, "theme": true, "user": true,
	"option": true, "post": true, "term": true, "menu": true,
	"widget": true, "sidebar": true, "db": true, "config": true,
	"cap": true, "role": true, "transient": true, "cron": true,
	"cache": true, "site": true, "network": true, "i18n": true,
	"language": true, "maintenance-mode": true, "scaffold": true,
	"search-replace": true, "media": true, "comment": true,
	"taxonomy": true, "export": true, "import": true, "rewrite": true,
	"super-admin": true, "eval-file": true,
}

// ExecCLI runs a WP-CLI command as www-data (e.g. "plugin list --format=json").
// The command is validated against an allowlist before execution.
func ExecCLI(username, path, command string) (string, error) {
	if err := validateCommand(command); err != nil {
		return "", err
	}

	wpCli, err := findWPCLI()
	if err != nil {
		return "", err
	}

	// Build args — separate args, no shell, no injection risk.
	args := []string{"-u", "www-data", wpCli}
	args = append(args, strings.Fields(command)...)
	args = append(args, "--path="+path, "--allow-root")

	cmd := exec.Command("sudo", args...)
	out, runErr := cmd.CombinedOutput()
	output := strings.TrimSpace(string(out))

	if runErr != nil {
		return output, fmt.Errorf("wp-cli failed: %w\noutput: %s", runErr, output)
	}

	return output, nil
}

// InstallParams holds parameters for a WordPress file installation.
type InstallParams struct {
	Username   string
	Path       string
	DBName     string
	DBUser     string
	DBPassword string
}

// InstallFiles downloads WordPress, extracts it, and configures wp-config.php.
// Does NOT run `wp core install` — call CoreInstall for database table creation.
func InstallFiles(p InstallParams) error {
	if err := os.MkdirAll(p.Path, 0755); err != nil {
		return fmt.Errorf("create dir %q: %w", p.Path, err)
	}

	tmpTar := filepath.Join(os.TempDir(), fmt.Sprintf("wordpress-%s.tar.gz", randomHex(8)))
	defer os.Remove(tmpTar)

	if out, err := exec.Command("curl", "-sL", "-o", tmpTar, "https://wordpress.org/latest.tar.gz").CombinedOutput(); err != nil {
		return fmt.Errorf("download failed: %s", strings.TrimSpace(string(out)))
	}

	// Verify download size (< 1MB = corrupt)
	if fi, err := os.Stat(tmpTar); err != nil || fi.Size() < 1_000_000 {
		return fmt.Errorf("downloaded wordpress archive is missing or corrupt")
	}

	tmpDir, err := os.MkdirTemp("", "wordpress-*")
	if err != nil {
		return fmt.Errorf("temp dir: %w", err)
	}
	defer os.RemoveAll(tmpDir)

	if out, err := exec.Command("tar", "-xzf", tmpTar, "-C", tmpDir).CombinedOutput(); err != nil {
		return fmt.Errorf("extract failed: %s", strings.TrimSpace(string(out)))
	}

	// Move files from extracted wordpress/ subdir to target path
	srcDir := filepath.Join(tmpDir, "wordpress")
	entries, err := os.ReadDir(srcDir)
	if err != nil {
		return fmt.Errorf("reading extracted dir: %w", err)
	}
	for _, entry := range entries {
		src := filepath.Join(srcDir, entry.Name())
		dst := filepath.Join(p.Path, entry.Name())
		if out, err := exec.Command("mv", src, dst).CombinedOutput(); err != nil {
			return fmt.Errorf("move %q: %s", entry.Name(), strings.TrimSpace(string(out)))
		}
	}

	// Patch wp-config.php using Go string replacement (avoids sed quoting issues with special chars)
	configSample := filepath.Join(p.Path, "wp-config-sample.php")
	configPath := filepath.Join(p.Path, "wp-config.php")

	configData, err := os.ReadFile(configSample)
	if err != nil {
		return fmt.Errorf("read wp-config-sample.php: %w", err)
	}

	config := string(configData)
	config = strings.ReplaceAll(config, "database_name_here", p.DBName)
	config = strings.ReplaceAll(config, "username_here", p.DBUser)
	config = strings.ReplaceAll(config, "password_here", p.DBPassword)
	config = strings.ReplaceAll(config, "put your unique phrase here", randomHex(32))

	if err := os.WriteFile(configPath, []byte(config), 0644); err != nil {
		return fmt.Errorf("write wp-config.php: %w", err)
	}

	// Set ownership and permissions
	if out, err := exec.Command("chown", "-R", "www-data:www-data", p.Path).CombinedOutput(); err != nil {
		return fmt.Errorf("chown: %s", strings.TrimSpace(string(out)))
	}
	exec.Command("find", p.Path, "-type", "d", "-exec", "chmod", "755", "{}", "+").Run()
	exec.Command("find", p.Path, "-type", "f", "-exec", "chmod", "644", "{}", "+").Run()

	return nil
}

// CoreInstallParams holds parameters for `wp core install`.
type CoreInstallParams struct {
	Username   string
	Path       string
	URL        string
	Title      string
	AdminUser  string
	AdminPass  string
	AdminEmail string
	Locale     string
}

// CoreInstall runs `wp core install` to create DB tables and the admin user.
// Must be called after InstallFiles and after the DB + wp-config.php are ready.
func CoreInstall(p CoreInstallParams) error {
	wpCli, err := findWPCLI()
	if err != nil {
		return err
	}

	if p.Locale == "" { p.Locale = "en_US" }
	if p.URL == "" { p.URL = "http://localhost" }
	if p.Title == "" { p.Title = "WordPress" }
	if p.AdminEmail == "" { p.AdminEmail = "admin@example.com" }

	args := []string{
		"-u", "www-data", wpCli,
		"core", "install",
		"--path=" + p.Path,
		"--url=" + p.URL,
		"--title=" + p.Title,
		"--admin_user=" + p.AdminUser,
		"--admin_password=" + p.AdminPass,
		"--admin_email=" + p.AdminEmail,
		"--locale=" + p.Locale,
		"--skip-email",
		"--allow-root",
	}

	out, err := exec.Command("sudo", args...).CombinedOutput()
	if err != nil {
		return fmt.Errorf("wp core install failed: %s", strings.TrimSpace(string(out)))
	}

	return nil
}

// UninstallFiles removes the WordPress installation directory.
// Path must be under /home/ as a safety guard.
func UninstallFiles(path string) error {
	cleanPath := filepath.Clean(path)
	if cleanPath == "/" || cleanPath == "/home" || !strings.HasPrefix(cleanPath, "/home/") {
		return fmt.Errorf("refusing to delete path outside /home/: %q", cleanPath)
	}
	return os.RemoveAll(cleanPath)
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

func validateCommand(command string) error {
	command = strings.TrimSpace(command)
	parts := strings.Fields(command)
	if len(parts) == 0 {
		return fmt.Errorf("empty WP-CLI command")
	}

	subcommand := strings.ToLower(parts[0])
	if !allowedSubcommands[subcommand] {
		return fmt.Errorf("disallowed WP-CLI subcommand: %q", subcommand)
	}

	// Reject shell metacharacters in any argument
	for _, part := range parts[1:] {
		if strings.ContainsAny(part, ";&|`$(){}[]!<>#~*?\"'\n\r\t\\") {
			return fmt.Errorf("unsafe character in WP-CLI argument: %q", part)
		}
	}

	return nil
}

func findWPCLI() (string, error) {
	candidates := []string{
		"/usr/local/bin/wp",
		"/usr/bin/wp",
		"/opt/wp-cli/wp",
	}
	for _, p := range candidates {
		if _, err := os.Stat(p); err == nil {
			return p, nil
		}
	}
	if path, err := exec.LookPath("wp"); err == nil {
		return path, nil
	}
	return "", fmt.Errorf(
		"wp-cli not found; install with: " +
			"curl -sL -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar " +
			"&& chmod +x /usr/local/bin/wp",
	)
}

func randomHex(bytes int) string {
	b := make([]byte, bytes)
	if _, err := rand.Read(b); err != nil {
		return strings.Repeat("x", bytes*2)
	}
	return hex.EncodeToString(b)
}
