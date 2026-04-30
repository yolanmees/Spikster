package installer

import "os/exec"

// Redis
type Redis struct{}
func (r *Redis) Name() string { return "redis" }
func (r *Redis) Install() error {
	return runCmds([][]string{
		{"apt-get", "install", "-y", "redis-server"},
		{"systemctl", "enable", "redis-server"},
		{"systemctl", "start", "redis-server"},
	})
}
func (r *Redis) Repair() error  { return runCmd([]string{"systemctl", "restart", "redis-server"}) }
func (r *Redis) Remove() error {
	return runCmds([][]string{{"systemctl", "stop", "redis-server"}, {"apt-get", "remove", "-y", "redis-server"}})
}
func (r *Redis) Healthcheck() (bool, string) {
	if exec.Command("systemctl", "is-active", "--quiet", "redis-server").Run() != nil {
		return false, "redis is not running"
	}
	return true, "running"
}

// Fail2ban
type Fail2ban struct{}
func (f *Fail2ban) Name() string { return "fail2ban" }
func (f *Fail2ban) Install() error {
	return runCmds([][]string{
		{"apt-get", "install", "-y", "fail2ban"},
		{"systemctl", "enable", "fail2ban"},
		{"systemctl", "start", "fail2ban"},
	})
}
func (f *Fail2ban) Repair() error  { return runCmd([]string{"systemctl", "restart", "fail2ban"}) }
func (f *Fail2ban) Remove() error {
	return runCmds([][]string{{"systemctl", "stop", "fail2ban"}, {"apt-get", "remove", "-y", "fail2ban"}})
}
func (f *Fail2ban) Healthcheck() (bool, string) {
	if exec.Command("systemctl", "is-active", "--quiet", "fail2ban").Run() != nil {
		return false, "fail2ban is not running"
	}
	return true, "running"
}

// Certbot
type Certbot struct{}
func (c *Certbot) Name() string { return "certbot" }
func (c *Certbot) Install() error {
	return runCmds([][]string{
		{"apt-get", "install", "-y", "certbot", "python3-certbot-nginx"},
	})
}
func (c *Certbot) Repair() error  { return c.Install() }
func (c *Certbot) Remove() error  { return runCmd([]string{"apt-get", "remove", "-y", "certbot"}) }
func (c *Certbot) Healthcheck() (bool, string) {
	if exec.Command("which", "certbot").Run() != nil {
		return false, "certbot not installed"
	}
	return true, "installed"
}
