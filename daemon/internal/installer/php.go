package installer

import (
	"fmt"
	"os/exec"
)

type PHP struct {
	Version string
}

func (p *PHP) Name() string { return fmt.Sprintf("php-%s", p.Version) }

func (p *PHP) Install() error {
	pkg := fmt.Sprintf("php%s-fpm", p.Version)
	extras := []string{
		fmt.Sprintf("php%s-cli", p.Version),
		fmt.Sprintf("php%s-mysql", p.Version),
		fmt.Sprintf("php%s-curl", p.Version),
		fmt.Sprintf("php%s-gd", p.Version),
		fmt.Sprintf("php%s-mbstring", p.Version),
		fmt.Sprintf("php%s-xml", p.Version),
		fmt.Sprintf("php%s-zip", p.Version),
		fmt.Sprintf("php%s-bcmath", p.Version),
		fmt.Sprintf("php%s-intl", p.Version),
	}
	args := append([]string{"apt-get", "install", "-y", pkg}, extras...)
	if err := runCmd(args); err != nil {
		return err
	}
	return runCmds([][]string{
		{"systemctl", "enable", pkg},
		{"systemctl", "start", pkg},
	})
}

func (p *PHP) Repair() error {
	pkg := fmt.Sprintf("php%s-fpm", p.Version)
	return runCmds([][]string{
		{"systemctl", "restart", pkg},
	})
}

func (p *PHP) Remove() error {
	pkg := fmt.Sprintf("php%s-fpm", p.Version)
	return runCmds([][]string{
		{"systemctl", "stop", pkg},
		{"apt-get", "remove", "-y", pkg},
	})
}

func (p *PHP) Healthcheck() (bool, string) {
	service := fmt.Sprintf("php%s-fpm", p.Version)
	err := exec.Command("systemctl", "is-active", "--quiet", service).Run()
	if err != nil {
		return false, service + " is not running"
	}
	return true, "running"
}
