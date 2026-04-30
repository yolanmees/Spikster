package server

import (
	"fmt"
	"os"
	"os/exec"
	"regexp"
	"strings"
)

// Fail2banBan bans an IP in a specific jail.
func Fail2banBan(ip, jail string) error {
	if err := validateIP(ip); err != nil {
		return err
	}
	if err := validateJail(jail); err != nil {
		return err
	}
	return run("fail2ban-client", "set", jail, "banip", ip)
}

// Fail2banUnban unbans an IP. If jail is empty, unbans from all jails.
func Fail2banUnban(ip, jail string) error {
	if err := validateIP(ip); err != nil {
		return err
	}
	if jail == "" {
		_, err := exec.Command("fail2ban-client", "unban", ip).CombinedOutput()
		return err
	}
	if err := validateJail(jail); err != nil {
		return err
	}
	return run("fail2ban-client", "set", jail, "unbanip", ip)
}

// Fail2banWhitelist adds an IP to fail2ban's ignoreip in jail.local.
func Fail2banWhitelist(ip string) error {
	if err := validateIP(ip); err != nil {
		return err
	}

	jailLocal := "/etc/fail2ban/jail.local"
	data, _ := os.ReadFile(jailLocal)
	content := string(data)

	if strings.Contains(content, ip) {
		return nil // already whitelisted
	}

	if strings.Contains(content, "ignoreip =") {
		var result strings.Builder
		for _, line := range strings.Split(content, "\n") {
			if strings.HasPrefix(strings.TrimSpace(line), "ignoreip =") {
				result.WriteString(strings.TrimRight(line, " ") + " " + ip + "\n")
			} else {
				result.WriteString(line + "\n")
			}
		}
		content = result.String()
	} else {
		content += "\n[DEFAULT]\nignoreip = 127.0.0.1/8 ::1 " + ip + "\n"
	}

	if err := os.WriteFile(jailLocal, []byte(content), 0640); err != nil {
		return fmt.Errorf("write jail.local: %w", err)
	}
	return run("fail2ban-client", "reload")
}

func validateIP(ip string) error {
	valid := regexp.MustCompile(`^[0-9a-fA-F.:\/]+$`)
	if !valid.MatchString(ip) || len(ip) > 50 {
		return fmt.Errorf("invalid IP address: %s", ip)
	}
	return nil
}

func validateJail(jail string) error {
	valid := regexp.MustCompile(`^[a-zA-Z0-9_\-]+$`)
	if !valid.MatchString(jail) {
		return fmt.Errorf("invalid jail name: %s", jail)
	}
	return nil
}
