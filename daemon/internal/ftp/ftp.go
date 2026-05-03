package ftp

import (
	"fmt"
	"os"
	"os/exec"
	"strings"
)

const (
	// vusersFile is the plaintext source for db_load.
	// Stored in /etc/spikster/ (mode 0750, root only) not in /etc/vsftpd/
	// to limit exposure. It is the only persistent record of FTP passwords.
	vusersFile = "/etc/spikster/ftp-vusers.txt"
	vusersDB   = "/etc/vsftpd/vusers.db"
	usersDir   = "/etc/vsftpd/users"
)

type FTPUser struct {
	Username  string
	Password  string
	HomeDir   string
	AllowedIP string
	Quota     int64
}

// EnsureInstalled installs vsftpd if not present
func EnsureInstalled() error {
	out, _ := exec.Command("dpkg", "-l", "vsftpd").Output()
	if strings.Contains(string(out), "ii  vsftpd") {
		return nil
	}
	cmds := [][]string{
		{"apt-get", "update"},
		{"env", "DEBIAN_FRONTEND=noninteractive", "apt-get", "install", "-y", "vsftpd", "db-util", "libpam-pwdfile"},
		{"systemctl", "enable", "vsftpd"},
		{"systemctl", "start", "vsftpd"},
	}
	for _, c := range cmds {
		if err := run(c...); err != nil {
			return err
		}
	}
	// Write secure main config
	if err := writeMainConfig(); err != nil {
		return err
	}
	return writePAMConfig()
}

func writeMainConfig() error {
	cfg := `listen=YES
listen_ipv6=NO
anonymous_enable=NO
local_enable=YES
write_enable=YES
local_umask=022
dirmessage_enable=YES
xferlog_enable=YES
connect_from_port_20=YES
xferlog_std_format=YES
pam_service_name=vsftpd.virtual
userlist_enable=YES
userlist_deny=NO
user_config_dir=` + usersDir + `
guest_enable=YES
guest_username=www-data
virtual_use_local_privs=YES
`
	return os.WriteFile("/etc/vsftpd.conf", []byte(cfg), 0644)
}

// CreateUser adds an FTP virtual user
func CreateUser(u FTPUser) error {
	if err := EnsureInstalled(); err != nil {
		return err
	}
	if err := os.MkdirAll(u.HomeDir, 0755); err != nil {
		return err
	}
	os.MkdirAll(usersDir, 0755)

	// Add to vusers file
	if err := appendUser(u.Username, u.Password); err != nil {
		return err
	}

	// Per-user config
	userConf := fmt.Sprintf("local_root=%s\nwrite_enable=YES\ndownload_enable=YES\nchroot_local_user=YES\nallow_writeable_chroot=YES\n", u.HomeDir)
	if err := os.WriteFile(fmt.Sprintf("%s/%s", usersDir, u.Username), []byte(userConf), 0600); err != nil {
		return err
	}

	run("chown", "-R", "www-data:www-data", u.HomeDir)
	return rebuildDB()
}

// UpdatePassword updates an FTP user's password
func UpdatePassword(username, newPassword string) error {
	if err := removeFromFile(username); err != nil {
		return err
	}
	if err := appendUser(username, newPassword); err != nil {
		return err
	}
	return rebuildDB()
}

// DeleteUser removes an FTP virtual user
func DeleteUser(username string) error {
	if err := removeFromFile(username); err != nil {
		return err
	}
	os.Remove(fmt.Sprintf("%s/%s", usersDir, username))
	return rebuildDB()
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

func appendUser(username, password string) error {
	f, err := os.OpenFile(vusersFile, os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0600)
	if err != nil {
		return err
	}
	defer f.Close()
	_, err = fmt.Fprintf(f, "%s\n%s\n", username, password)
	return err
}

func removeFromFile(username string) error {
	data, err := os.ReadFile(vusersFile)
	if err != nil {
		if os.IsNotExist(err) {
			return nil
		}
		return err
	}
	lines := strings.Split(string(data), "\n")
	var out []string
	skip := false
	for _, line := range lines {
		if line == username {
			skip = true
			continue
		}
		if skip {
			skip = false
			continue
		}
		out = append(out, line)
	}
	return os.WriteFile(vusersFile, []byte(strings.Join(out, "\n")), 0600)
}

func rebuildDB() error {
	if err := run("db_load", "-T", "-t", "hash", "-f", vusersFile, vusersDB); err != nil {
		return err
	}
	os.Chmod(vusersDB, 0600)
	// Keep the source file for future add/remove operations, but enforce strict perms
	os.Chmod(vusersFile, 0600)
	return run("systemctl", "reload", "vsftpd")
}

func writePAMConfig() error {
	pam := "auth required pam_userdb.so db=/etc/vsftpd/vusers\naccount required pam_userdb.so db=/etc/vsftpd/vusers\n"
	return os.WriteFile("/etc/pam.d/vsftpd-virtual", []byte(pam), 0644)
}

func run(args ...string) error {
	out, err := exec.Command(args[0], args[1:]...).CombinedOutput()
	if err != nil {
		return fmt.Errorf("%s: %s", args[0], strings.TrimSpace(string(out)))
	}
	return nil
}
