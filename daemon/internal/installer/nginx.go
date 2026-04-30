package installer

import (
	"os/exec"
)

type Nginx struct{}

func (n *Nginx) Name() string { return "nginx" }

func (n *Nginx) Install() error {
	cmds := [][]string{
		{"apt-get", "install", "-y", "nginx"},
		{"systemctl", "enable", "nginx"},
		{"systemctl", "start", "nginx"},
	}
	return runCmds(cmds)
}

func (n *Nginx) Repair() error {
	cmds := [][]string{
		{"apt-get", "install", "--reinstall", "-y", "nginx"},
		{"systemctl", "restart", "nginx"},
	}
	return runCmds(cmds)
}

func (n *Nginx) Remove() error {
	return runCmds([][]string{
		{"systemctl", "stop", "nginx"},
		{"apt-get", "remove", "-y", "nginx"},
	})
}

func (n *Nginx) Healthcheck() (bool, string) {
	err := exec.Command("systemctl", "is-active", "--quiet", "nginx").Run()
	if err != nil {
		return false, "nginx is not running"
	}
	return true, "running"
}
