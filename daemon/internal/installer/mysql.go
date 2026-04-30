package installer

import "os/exec"

type MySQL struct{}

func (m *MySQL) Name() string { return "mysql" }

func (m *MySQL) Install() error {
	return runCmds([][]string{
		{"apt-get", "install", "-y", "mysql-server"},
		{"systemctl", "enable", "mysql"},
		{"systemctl", "start", "mysql"},
	})
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
