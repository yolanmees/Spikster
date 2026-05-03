package site

import (
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
)

// DeployParams holds the parameters for a site deployment.
type DeployParams struct {
	Username   string // system user for the site
	RepoURL    string // git repository URL
	Branch     string // git branch/tag to deploy
	PHP        string // PHP version (e.g. "8.3")
	Composer   bool   // run composer install
	NPM        bool   // run npm install && npm run build
	ArtisanMig bool   // run php artisan migrate --force
	ArtisanCache bool // run php artisan cache:clear && config:clear && view:clear
}

// Deploy runs a full site deployment: git clone/pull, composer, npm, artisan.
func Deploy(p DeployParams) (string, error) {
	if err := validateDeployParams(p); err != nil {
		return "", err
	}

	siteRoot := filepath.Join("/home", p.Username, "git")
	webRoot := filepath.Join("/home", p.Username, "web")

	// Determine if this is a fresh clone or a pull
	gitDir := filepath.Join(siteRoot, ".git")
	if _, err := os.Stat(gitDir); os.IsNotExist(err) {
		// Fresh clone
		if _, err := os.Stat(siteRoot); err == nil {
			// Remove existing (non-git) directory
			if err := os.RemoveAll(siteRoot); err != nil {
				return "", fmt.Errorf("remove existing dir: %w", err)
			}
		}
		if out, err := runAs(p.Username, "git", "clone", "--branch", p.Branch, p.RepoURL, siteRoot); err != nil {
			return "", fmt.Errorf("git clone: %s: %w", strings.TrimSpace(out), err)
		}
	} else {
		// Existing repo — pull
		if out, err := runAs(p.Username, "git", "-C", siteRoot, "fetch", "origin"); err != nil {
			return "", fmt.Errorf("git fetch: %s: %w", strings.TrimSpace(out), err)
		}
		if out, err := runAs(p.Username, "git", "-C", siteRoot, "checkout", p.Branch); err != nil {
			return "", fmt.Errorf("git checkout: %s: %w", strings.TrimSpace(out), err)
		}
		if out, err := runAs(p.Username, "git", "-C", siteRoot, "pull", "origin", p.Branch); err != nil {
			return "", fmt.Errorf("git pull: %s: %w", strings.TrimSpace(out), err)
		}
	}

	// Run composer if enabled and composer.json exists
	if p.Composer {
		composerJSON := filepath.Join(siteRoot, "composer.json")
		if _, err := os.Stat(composerJSON); err == nil {
			cmd := exec.Command("su", "-", p.Username, "-c",
				fmt.Sprintf("cd %s && composer install --no-dev --optimize-autoloader --no-interaction", shellEsc(siteRoot)))
			if out, err := cmd.CombinedOutput(); err != nil {
				return "", fmt.Errorf("composer install: %s: %w", strings.TrimSpace(string(out)), err)
			}
		}
	}

	// Run npm if enabled and package.json exists
	if p.NPM {
		pkgJSON := filepath.Join(siteRoot, "package.json")
		if _, err := os.Stat(pkgJSON); err == nil {
			cmd := exec.Command("su", "-", p.Username, "-c",
				fmt.Sprintf("cd %s && npm install && npm run build", shellEsc(siteRoot)))
			if out, err := cmd.CombinedOutput(); err != nil {
				return "", fmt.Errorf("npm install/build: %s: %w", strings.TrimSpace(string(out)), err)
			}
		}
	}

	// Sync files from git dir to web root (preserving user files)
	if err := syncToWeb(p.Username, siteRoot, webRoot); err != nil {
		return "", fmt.Errorf("sync: %w", err)
	}

	// Run php artisan commands if enabled
	if p.ArtisanMig {
		artisan := filepath.Join(webRoot, "artisan")
		if _, err := os.Stat(artisan); err == nil {
			cmd := exec.Command("su", "-", p.Username, "-c",
				fmt.Sprintf("cd %s && php%s artisan migrate --force --no-interaction", shellEsc(webRoot), p.PHP))
			if out, err := cmd.CombinedOutput(); err != nil {
				return "", fmt.Errorf("artisan migrate: %s: %w", strings.TrimSpace(string(out)), err)
			}
		}
	}
	if p.ArtisanCache {
		artisan := filepath.Join(webRoot, "artisan")
		if _, err := os.Stat(artisan); err == nil {
			cmd := exec.Command("su", "-", p.Username, "-c",
				fmt.Sprintf("cd %s && php%s artisan cache:clear && php%s artisan config:clear && php%s artisan view:clear",
					shellEsc(webRoot), p.PHP, p.PHP, p.PHP))
			if out, err := cmd.CombinedOutput(); err != nil {
				return "", fmt.Errorf("artisan cache clear: %s: %w", strings.TrimSpace(string(out)), err)
			}
		}
	}

	// Reload php-fpm
	if err := run("systemctl", "reload", fmt.Sprintf("php%s-fpm", p.PHP)); err != nil {
		return "", fmt.Errorf("reload php-fpm: %w", err)
	}

	return "deployed", nil
}

// Rollback resets the git repo to a specific commit and syncs to web.
func DeployRollback(username string, commitHash string) (string, error) {
	if err := validateUsername(username); err != nil {
		return "", err
	}
	if !safeCommitHash(commitHash) {
		return "", fmt.Errorf("invalid commit hash: %q", commitHash)
	}

	siteRoot := filepath.Join("/home", username, "git")
	webRoot := filepath.Join("/home", username, "web")

	if out, err := runAs(username, "git", "-C", siteRoot, "reset", "--hard", commitHash); err != nil {
		return "", fmt.Errorf("git reset: %s: %w", strings.TrimSpace(out), err)
	}

	if err := syncToWeb(username, siteRoot, webRoot); err != nil {
		return "", fmt.Errorf("sync: %w", err)
	}

	return "rolled back", nil
}

// DeployHistory returns the last N git log entries.
func DeployHistory(username string, count int) (string, error) {
	if err := validateUsername(username); err != nil {
		return "", err
	}
	siteRoot := filepath.Join("/home", username, "git")
	out, err := runAs(username, "git", "-C", siteRoot, "log", fmt.Sprintf("--oneline", "-%d", count))
	if err != nil {
		return "", fmt.Errorf("git log: %s: %w", strings.TrimSpace(out), err)
	}
	return strings.TrimSpace(out), nil
}

// ─── helpers ──────────────────────────────────────────────────────────────────

func validateDeployParams(p DeployParams) error {
	if err := validateUsername(p.Username); err != nil {
		return err
	}
	if p.RepoURL == "" {
		return fmt.Errorf("repo_url is required")
	}
	if !safeRepoURL(p.RepoURL) {
		return fmt.Errorf("invalid repo URL: %q", p.RepoURL)
	}
	if p.Branch == "" {
		return fmt.Errorf("branch is required")
	}
	if !safeBranchName(p.Branch) {
		return fmt.Errorf("invalid branch name: %q", p.Branch)
	}
	if p.PHP != "" && !isAllowedPHP(p.PHP) {
		return fmt.Errorf("invalid PHP version: %q", p.PHP)
	}
	return nil
}

func safeRepoURL(url string) bool {
	// Only allow https://, git@, ssh:// URLs
	return strings.HasPrefix(url, "https://") ||
		strings.HasPrefix(url, "git@") ||
		strings.HasPrefix(url, "ssh://")
}

func safeBranchName(name string) bool {
	// Allow letters, digits, /, -, _, .
	for _, c := range name {
		if !((c >= 'a' && c <= 'z') || (c >= 'A' && c <= 'Z') ||
			(c >= '0' && c <= '9') || c == '/' || c == '-' || c == '_' || c == '.') {
			return false
		}
	}
	return len(name) > 0 && len(name) <= 255
}

func safeCommitHash(hash string) bool {
	for _, c := range hash {
		if !((c >= 'a' && c <= 'f') || (c >= '0' && c <= '9')) {
			return false
		}
	}
	return len(hash) >= 7 && len(hash) <= 40
}

func isAllowedPHP(version string) bool {
	switch version {
	case "8.2", "8.3", "8.4":
		return true
	}
	return false
}

func runAs(username string, args ...string) (string, error) {
	suArgs := []string{"-", username, "-c"}
	cmdStr := strings.Join(quoteArgs(args), " ")
	cmd := exec.Command("su", append(suArgs, cmdStr)...)
	out, err := cmd.CombinedOutput()
	if err != nil {
		return string(out), err
	}
	return string(out), nil
}

func quoteArgs(args []string) []string {
	quoted := make([]string, len(args))
	for i, a := range args {
		quoted[i] = "'" + strings.ReplaceAll(a, "'", "'\\''") + "'"
	}
	return quoted
}

func shellEsc(s string) string {
	return "'" + strings.ReplaceAll(s, "'", "'\\''") + "'"
}

// syncToWeb copies files from git dir to web root using rsync,
// excluding .git directory and preserving user ownership.
func syncToWeb(username, src, dst string) error {
	os.MkdirAll(dst, 0755)
	cmd := exec.Command("rsync", "-a", "--delete", "--exclude=.git", "--exclude=node_modules",
		src+"/", dst+"/")
	out, err := cmd.CombinedOutput()
	if err != nil {
		return fmt.Errorf("rsync: %s: %w", strings.TrimSpace(string(out)), err)
	}
	return run("chown", "-R", username+":www-data", dst)
}
