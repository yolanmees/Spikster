package installer

import (
	"fmt"
	"os"
	"os/exec"
)

type MySQL struct{}

func (m *MySQL) Name() string { return "mysql" }

func (m *MySQL) Install() error {
	if err := runCmds([][]string{
		{"apt-get", "install", "-y", "mysql-server"},
		{"systemctl", "enable", "mysql"},
		{"systemctl", "start", "mysql"},
	}); err != nil {
		return err
	}
	return setupSpiksterDBUser()
}

// setupSpiksterDBUser creates the 'spikster' MySQL user that the daemon uses
// for all site database operations. Password is generated once and stored in
// /etc/spikster/db.pass so Laravel .env can read it on install.
func setupSpiksterDBUser() error {
	passFile := "/etc/spikster/db.pass"

	// Read existing password or generate a new one
	var dbPass string
	if data, err := os.ReadFile(passFile); err == nil {
		dbPass = string(data)
	} else {
		out, err := exec.Command("openssl", "rand", "-hex", "24").Output()
		if err != nil {
			return fmt.Errorf("generate db password: %w", err)
		}
		dbPass = string(out[:len(out)-1]) // trim newline
		if err := os.WriteFile(passFile, []byte(dbPass), 0600); err != nil {
			return fmt.Errorf("write db.pass: %w", err)
		}
	}

	sql := fmt.Sprintf(
		"CREATE USER IF NOT EXISTS 'spikster'@'localhost' IDENTIFIED BY '%s'; "+
			"GRANT CREATE, DROP, ALTER, INDEX, SELECT, INSERT, UPDATE, DELETE, REFERENCES ON *.* TO 'spikster'@'localhost'; "+
			"FLUSH PRIVILEGES;",
		dbPass,
	)
	return runCmd([]string{"mysql", "-uroot", "-e", sql})
}

func (m *MySQL) Repair() error {
	return runCmd([]string{"systemctl", "restart", "mysql"})
}

func (m *MySQL) Remove() error {
	return runCmds([][]string{
		{"systemctl", "stop", "mysql"},
		{"apt-get", "remove", "-y", "mysql-server"},
	})
}

func (m *MySQL) Healthcheck() (bool, string) {
	err := exec.Command("systemctl", "is-active", "--quiet", "mysql").Run()
	if err != nil {
		return false, "mysql is not running"
	}
	return true, "running"
}
