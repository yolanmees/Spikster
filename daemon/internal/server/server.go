package server

import (
	"fmt"
	"os/exec"
	"strings"
)

// Fail2banList returns banned IPs from fail2ban sqlite
func Fail2banList() (string, error) {
	out, err := exec.Command("sqlite3",
		"/var/lib/fail2ban/fail2ban.sqlite3",
		"select ip,jail from bips",
	).Output()
	if err != nil {
		// Fallback: use fail2ban-client
		out, err = exec.Command("fail2ban-client", "banned").Output()
		if err != nil {
			return "", fmt.Errorf("fail2ban query failed: %w", err)
		}
	}
	return strings.TrimSpace(string(out)), nil
}

// Fail2banStatus returns fail2ban-client status output for a jail
func Fail2banStatus(jail string) (string, error) {
	args := []string{"status"}
	if jail != "" {
		args = append(args, jail)
	}
	out, err := exec.Command("fail2ban-client", args...).Output()
	if err != nil {
		return "", fmt.Errorf("fail2ban-client status failed: %w", err)
	}
	return strings.TrimSpace(string(out)), nil
}

// PackageList returns installed packages via dpkg
func PackageList() (string, error) {
	out, err := exec.Command("dpkg", "--get-selections").Output()
	if err != nil {
		return "", err
	}
	return strings.TrimSpace(string(out)), nil
}

// PackageInstall installs a package via apt
func PackageInstall(pkg string) error {
	if err := validate(pkg); err != nil {
		return err
	}
	return run("env", "DEBIAN_FRONTEND=noninteractive", "apt-get", "install", "-y", pkg)
}

// PackageRemove removes a package via apt
func PackageRemove(pkg string) error {
	if err := validate(pkg); err != nil {
		return err
	}
	return run("apt-get", "remove", "-y", pkg)
}

// validate checks package name for safety.
// Allowed set matches Debian policy for binary package names:
// letters, digits, '.', '+', '-'; first character must be alphanumeric.
// This blocks shell metacharacters (; | & ` $ space etc.) entirely.
func validate(pkg string) error {
	if pkg == "" {
		return fmt.Errorf("invalid package name: %s", pkg)
	}
	first := pkg[0]
	if !((first >= 'a' && first <= 'z') || (first >= 'A' && first <= 'Z') || (first >= '0' && first <= '9')) {
		return fmt.Errorf("invalid package name: %s", pkg)
	}
	for _, c := range pkg {
		if !((c >= 'a' && c <= 'z') || (c >= 'A' && c <= 'Z') || (c >= '0' && c <= '9') || c == '-' || c == '.' || c == '+') {
			return fmt.Errorf("invalid package name: %s", pkg)
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
