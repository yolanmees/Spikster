package site

import (
	"bytes"
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"text/template"
	"time"
)

type Site struct {
	ID                string
	Domain            string
	Username          string
	Password          string
	DBName            string
	DBPass            string
	DBRoot            string
	PHP               string
	Basepath          string
	NginxConfig       string
	PHPMemoryLimit    string
	PHPUploadMaxSize  string
	PHPMaxExecTime    string
	PHPMaxInputVars   string
	PHPPostMaxSize    string
}

func (s Site) WebRoot() string {
	base := filepath.Join("/home", s.Username, "web")
	if s.Basepath != "" {
		return filepath.Join(base, strings.TrimPrefix(s.Basepath, "/"))
	}
	return base
}

func Create(s Site) error {
	if err := ValidateSite(s); err != nil {
		return err
	}
	steps := []struct {
		name string
		fn   func() error
	}{
		{"create user", func() error { return createUser(s) }},
		{"create dirs", func() error { return createDirs(s) }},
		{"write welcome page", func() error { return writeWelcome(s) }},
		{"write nginx config", func() error { return writeNginxConfig(s) }},
		{"write php-fpm pool", func() error { return WritePHPPool(s) }},
		{"reload php-fpm", func() error {
			go func() {
				time.Sleep(3 * time.Second)
				reloadService(fmt.Sprintf("php%s-fpm", s.PHP))
			}()
			return nil
		}},
		{"create database", func() error { return createDatabase(s) }},
		{"set permissions", func() error { return setPermissions(s) }},
	}
	for _, step := range steps {
		if err := step.fn(); err != nil {
			return fmt.Errorf("[%s] %w", step.name, err)
		}
	}
	return nil
}

func Delete(s Site) error {
	// Best-effort cleanup of config files; ignore "not found" errors
	for _, f := range []string{
		fmt.Sprintf("/etc/nginx/sites-enabled/%s.conf", s.Username),
		fmt.Sprintf("/etc/nginx/sites-available/%s.conf", s.Username),
		fmt.Sprintf("/etc/nginx/spikster/%s.conf", s.Username),
		fmt.Sprintf("/etc/php/%s/fpm/pool.d/%s.conf", s.PHP, s.Username),
	} {
		os.Remove(f) // ignore not-found
	}
	reloadService("nginx")
	reloadService(fmt.Sprintf("php%s-fpm", s.PHP))
	if err := dropDatabase(s); err != nil {
		return fmt.Errorf("drop database: %w", err)
	}
	return run("userdel", "-r", s.Username)
}

// ─── Steps ────────────────────────────────────────────────────────────────────

func createUser(s Site) error {
	if run("id", s.Username) == nil {
		return fmt.Errorf("user %s already exists", s.Username)
	}
	if err := run("useradd", "-m", "-s", "/bin/bash", "-d", "/home/"+s.Username, "-G", "www-data", s.Username); err != nil {
		return err
	}
	cmd := exec.Command("chpasswd")
	cmd.Stdin = strings.NewReader(s.Username + ":" + s.Password + "\n")
	out, err := cmd.CombinedOutput()
	if err != nil {
		return fmt.Errorf("chpasswd: %s", strings.TrimSpace(string(out)))
	}
	return nil
}

func createDirs(s Site) error {
	for _, d := range []string{s.WebRoot(), "/home/" + s.Username + "/log", "/home/" + s.Username + "/.cache", "/home/" + s.Username + "/git"} {
		if err := os.MkdirAll(d, 0755); err != nil {
			return err
		}
	}
	return nil
}

func writeWelcome(s Site) error {
	html := `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Coming Soon</title><style>body{font-family:sans-serif;margin:3rem}h1{font-size:3rem}p{color:#777;font-size:1.5rem}</style></head><body><h1>Coming Soon</h1><p><script>document.write(window.location.hostname)</script></p></body></html>`
	return os.WriteFile(filepath.Join(s.WebRoot(), "index.php"), []byte(html), 0644)
}

var nginxTpl = `server {
    listen 80;
    listen [::]:80;
    server_tokens off;
    server_name {{.Domain}};
    root {{.WebRoot}};
    client_body_timeout 60s;
    client_header_timeout 10s;
    client_max_body_size 256M;
    access_log /home/{{.Username}}/log/access.log;
    error_log /home/{{.Username}}/log/error.log;
    include /etc/nginx/spikster/{{.Username}}.conf;
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php{{.PHP}}-fpm-{{.Username}}.sock;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
`

var phpPoolTpl = `[{{.Username}}]
user = {{.Username}}
group = {{.Username}}
listen = /run/php/php{{.PHP}}-fpm-{{.Username}}.sock
listen.owner = www-data
listen.group = www-data
pm = ondemand
pm.max_children = 50
pm.max_requests = 500
pm.process_idle_timeout = 10s
request_terminate_timeout = 300
{{- if .PHPMemoryLimit}}
php_admin_value[memory_limit] = {{.PHPMemoryLimit}}
{{- end}}
{{- if .PHPUploadMaxSize}}
php_admin_value[upload_max_filesize] = {{.PHPUploadMaxSize}}
php_admin_value[post_max_size] = {{if .PHPPostMaxSize}}{{.PHPPostMaxSize}}{{else}}{{.PHPUploadMaxSize}}{{end}}
{{- end}}
{{- if .PHPMaxExecTime}}
php_admin_value[max_execution_time] = {{.PHPMaxExecTime}}
{{- end}}
{{- if .PHPMaxInputVars}}
php_admin_value[max_input_vars] = {{.PHPMaxInputVars}}
{{- end}}
`

func writeNginxConfig(s Site) error {
	os.MkdirAll("/etc/nginx/spikster", 0755)
	available := fmt.Sprintf("/etc/nginx/sites-available/%s.conf", s.Username)
	if err := writeTpl(available, nginxTpl, s); err != nil {
		return err
	}
	enabled := fmt.Sprintf("/etc/nginx/sites-enabled/%s.conf", s.Username)
	os.Remove(enabled)
	if err := os.Symlink(available, enabled); err != nil {
		return err
	}
	return WriteCustomNginxConfig(s)
}

func WriteCustomNginxConfig(s Site) error {
	custom := fmt.Sprintf("/etc/nginx/spikster/%s.conf", s.Username)
	content := "# Custom nginx rules — edit freely\n"
	if s.NginxConfig != "" {
		content = s.NginxConfig
	}
	if err := os.WriteFile(custom, []byte(content), 0644); err != nil {
		return fmt.Errorf("write custom nginx: %w", err)
	}
	if err := run("nginx", "-t"); err != nil {
		return fmt.Errorf("nginx config test failed: %w", err)
	}
	return nil
}

func WritePHPPool(s Site) error {
	return writeTpl(fmt.Sprintf("/etc/php/%s/fpm/pool.d/%s.conf", s.PHP, s.Username), phpPoolTpl, s)
}

func ReloadPHP(s Site) error {
	return reloadService(fmt.Sprintf("php%s-fpm", s.PHP))
}

func ReloadNginx() error {
	return reloadService("nginx")
}

func createDatabase(s Site) error {
	sql := fmt.Sprintf(
		"CREATE DATABASE IF NOT EXISTS `%s`; CREATE USER IF NOT EXISTS '%s'@'localhost' IDENTIFIED WITH mysql_native_password BY '%s'; GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'localhost' WITH GRANT OPTION; FLUSH PRIVILEGES;",
		s.DBName, s.Username, s.DBPass, s.DBName, s.Username,
	)
	return runWithDBPass(s.DBRoot, sql)
}

func dropDatabase(s Site) error {
	sql := fmt.Sprintf("DROP DATABASE IF EXISTS `%s`; DROP USER IF EXISTS '%s'@'localhost'; FLUSH PRIVILEGES;", s.DBName, s.Username)
	return runWithDBPass(s.DBRoot, sql)
}

// readDBRootPass reads the spikster DB password from the state file.
func readDBRootPass() (string, error) {
	data, err := os.ReadFile("/etc/spikster/db.pass")
	if err != nil {
		return "", err
	}
	return strings.TrimSpace(string(data)), nil
}

// runWithDBPass executes a MySQL statement using a credentials temp file to
// avoid exposing the password in the process list.
func runWithDBPass(pass, sql string) error {
	cnf := fmt.Sprintf("[client]\nuser=root\npassword=%s\n", pass)
	tmp, err := os.CreateTemp("", "spikster-mysql-*.cnf")
	if err != nil {
		return err
	}
	defer os.Remove(tmp.Name())
	tmp.WriteString(cnf)
	tmp.Close()
	os.Chmod(tmp.Name(), 0600)
	out, err := exec.Command("mysql", "--defaults-extra-file="+tmp.Name(), "-e", sql).CombinedOutput()
	if err != nil {
		return fmt.Errorf("mysql: %s", strings.TrimSpace(string(out)))
	}
	return nil
}

func setPermissions(s Site) error {
	home := "/home/" + s.Username
	if err := run("chown", "-R", s.Username+":www-data", home); err != nil {
		return err
	}
	if err := run("chmod", "755", home); err != nil {
		return err
	}
	if err := run("find", home+"/web", "-type", "d", "-exec", "chmod", "755", "{}", "+"); err != nil {
		return err
	}
	return run("find", home+"/web", "-type", "f", "-exec", "chmod", "644", "{}", "+")
}

func reloadService(service string) error {
	return run("systemctl", "reload", service)
}

func run(args ...string) error {
	out, err := exec.Command(args[0], args[1:]...).CombinedOutput()
	if err != nil {
		return fmt.Errorf("%s: %s", args[0], strings.TrimSpace(string(out)))
	}
	return nil
}

func writeTpl(path, tmpl string, data interface{}) error {
	t, err := template.New("").Parse(tmpl)
	if err != nil {
		return err
	}
	var buf bytes.Buffer
	if err := t.Execute(&buf, data); err != nil {
		return err
	}
	return os.WriteFile(path, buf.Bytes(), 0644)
}

// UpdatePHP switches a site to a different PHP version
func UpdatePHP(username, oldPHP, newPHP string) error {
	oldPool := fmt.Sprintf("/etc/php/%s/fpm/pool.d/%s.conf", oldPHP, username)
	newPool := fmt.Sprintf("/etc/php/%s/fpm/pool.d/%s.conf", newPHP, username)
	nginxConf := fmt.Sprintf("/etc/nginx/sites-available/%s.conf", username)

	// Move pool config
	if err := run("mv", oldPool, newPool); err != nil {
		return fmt.Errorf("move pool: %w", err)
	}

	// Update socket reference in pool
	oldSock := fmt.Sprintf("/run/php/php%s-fpm-%s.sock", oldPHP, username)
	newSock := fmt.Sprintf("/run/php/php%s-fpm-%s.sock", newPHP, username)
	if err := replaceInFile(newPool, oldSock, newSock); err != nil {
		return fmt.Errorf("update pool socket: %w", err)
	}

	// Update nginx config
	if err := replaceInFile(nginxConf, "php"+oldPHP+"-fpm-"+username, "php"+newPHP+"-fpm-"+username); err != nil {
		return fmt.Errorf("update nginx: %w", err)
	}

	// Reload both PHP versions + nginx
	run("systemctl", "reload", "php"+oldPHP+"-fpm")
	run("systemctl", "reload", "php"+newPHP+"-fpm")
	return reloadService("nginx")
}

// UpdateDomain changes the server_name in the nginx config
func UpdateDomain(username, oldDomain, newDomain string) error {
	nginxConf := fmt.Sprintf("/etc/nginx/sites-available/%s.conf", username)
	if err := replaceInFile(nginxConf, "server_name "+oldDomain+";", "server_name "+newDomain+";"); err != nil {
		return err
	}
	return reloadService("nginx")
}

// UpdateBasepath rewrites the nginx root directive
func UpdateBasepath(username, newBasepath string) error {
	nginxConf := fmt.Sprintf("/etc/nginx/sites-available/%s.conf", username)

	// Validate — block path traversal
	cleanBasepath := strings.TrimPrefix(newBasepath, "/")
	if strings.Contains(cleanBasepath, "..") {
		return fmt.Errorf("path traversal detected in basepath: %q", newBasepath)
	}

	// Read current config
	data, err := os.ReadFile(nginxConf)
	if err != nil {
		return err
	}

	// Replace root line
	lines := strings.Split(string(data), "\n")
	for i, line := range lines {
		if strings.HasPrefix(strings.TrimSpace(line), "root ") {
			newRoot := "/home/" + username + "/web"
			if newBasepath != "" {
				newRoot += "/" + cleanBasepath
			}
			// Validate final path is under /home/
			if !strings.HasPrefix(newRoot, "/home/") {
				return fmt.Errorf("invalid root path: %q", newRoot)
			}
			lines[i] = "    root " + newRoot + ";"
			break
		}
	}
	return os.WriteFile(nginxConf, []byte(strings.Join(lines, "\n")), 0644)
}

// EnableSSL runs certbot for a domain and enables HTTP/2
func EnableSSL(username, domain string) error {
	steps := []struct {
		name string
		fn   func() error
	}{
		{"stop nginx", func() error { return run("systemctl", "stop", "nginx") }},
		{"certbot", func() error {
			return run("certbot", "--nginx", "-d", domain,
				"--non-interactive", "--agree-tos", "--register-unsafely-without-email")
		}},
		{"enable http2", func() error {
			conf := fmt.Sprintf("/etc/nginx/sites-enabled/%s.conf", username)
			return replaceInFile(conf, "443 ssl;", "443 ssl http2;")
		}},
		{"start nginx", func() error { return run("systemctl", "start", "nginx") }},
	}
	for _, step := range steps {
		if err := step.fn(); err != nil {
			run("systemctl", "start", "nginx") // ensure nginx comes back up
			return fmt.Errorf("[%s] %w", step.name, err)
		}
	}
	return nil
}

// replaceInFile does a simple string replacement in a file
func replaceInFile(path, old, new string) error {
	data, err := os.ReadFile(path)
	if err != nil {
		return err
	}
	replaced := strings.ReplaceAll(string(data), old, new)
	return os.WriteFile(path, []byte(replaced), 0644)
}

// ─── Aliases ──────────────────────────────────────────────────────────────────

type Alias struct {
	Domain   string
	Username string // parent site username
	PHP      string
	Basepath string
}

func (a Alias) WebRoot() string {
	base := filepath.Join("/home", a.Username, "web")
	if a.Basepath != "" {
		return filepath.Join(base, strings.TrimPrefix(a.Basepath, "/"))
	}
	return base
}

var aliasTpl = `server {
    listen 80;
    listen [::]:80;
    server_tokens off;
    server_name {{.Domain}};
    root {{.WebRoot}};
    client_body_timeout 60s;
    client_header_timeout 10s;
    client_max_body_size 256M;
    access_log /home/{{.Username}}/log/access.log;
    error_log /home/{{.Username}}/log/error.log;
    include /etc/nginx/spikster/{{.Username}}.conf;
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php{{.PHP}}-fpm-{{.Username}}.sock;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
`

func CreateAlias(a Alias) error {
	available := fmt.Sprintf("/etc/nginx/sites-available/%s.conf", a.Domain)
	if err := writeTpl(available, aliasTpl, a); err != nil {
		return err
	}
	enabled := fmt.Sprintf("/etc/nginx/sites-enabled/%s.conf", a.Domain)
	os.Remove(enabled)
	if err := os.Symlink(available, enabled); err != nil {
		return err
	}
	run("systemctl", "reload", fmt.Sprintf("php%s-fpm", a.PHP))
	return reloadService("nginx")
}

func DeleteAlias(domain string) error {
	os.Remove(fmt.Sprintf("/etc/nginx/sites-enabled/%s.conf", domain))
	os.Remove(fmt.Sprintf("/etc/nginx/sites-available/%s.conf", domain))
	return reloadService("nginx")
}

func EnableAliasSSL(domain string) error {
	steps := []struct {
		name string
		fn   func() error
	}{
		{"stop nginx", func() error { return run("systemctl", "stop", "nginx") }},
		{"certbot", func() error {
			return run("certbot", "--nginx", "-d", domain,
				"--non-interactive", "--agree-tos", "--register-unsafely-without-email")
		}},
		{"enable http2", func() error {
			conf := fmt.Sprintf("/etc/nginx/sites-enabled/%s.conf", domain)
			return replaceInFile(conf, "443 ssl;", "443 ssl http2;")
		}},
		{"start nginx", func() error { return run("systemctl", "start", "nginx") }},
	}
	for _, step := range steps {
		if err := step.fn(); err != nil {
			run("systemctl", "start", "nginx")
			return fmt.Errorf("[%s] %w", step.name, err)
		}
	}
	return nil
}

// ─── Site passwords ───────────────────────────────────────────────────────────

func UpdateUserPassword(username, password string) error {
	cmd := exec.Command("chpasswd")
	cmd.Stdin = strings.NewReader(username + ":" + password + "\n")
	out, err := cmd.CombinedOutput()
	if err != nil {
		return fmt.Errorf("chpasswd: %s", strings.TrimSpace(string(out)))
	}
	return nil
}

func UpdateDBPassword(username, _, newPass string) error {
	// Use spikster admin user (stored in /etc/spikster/db.pass) to change the password.
	// Never pass passwords as CLI args (visible in ps aux).
	rootPass, err := readDBRootPass()
	if err != nil {
		return fmt.Errorf("read db.pass: %w", err)
	}
	sql := fmt.Sprintf("ALTER USER '%s'@'%%%%' IDENTIFIED BY '%s'; FLUSH PRIVILEGES;", username, newPass)
	return runWithDBPass(rootPass, sql)
}

// ─── Supervisor ───────────────────────────────────────────────────────────────

var supervisorTpl = `[program:{{.Username}}]
command={{.Script}}
user={{.Username}}
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/home/{{.Username}}/log/supervisor.log
`

type SupervisorConfig struct {
	Username string
	Script   string
}

func UpdateSupervisor(username, script string) error {
	confPath := fmt.Sprintf("/etc/supervisor/conf.d/%s.conf", username)
	os.Remove(confPath)
	if script == "" {
		// Disabled — just reload
	} else {
		if err := writeTpl(confPath, supervisorTpl, SupervisorConfig{username, script}); err != nil {
			return err
		}
	}
	run("supervisorctl", "reread")
	run("supervisorctl", "update")
	if script != "" {
		run("supervisorctl", "start", username)
	}
	return run("systemctl", "restart", "supervisor")
}

func SupervisorCtl(action, process string) (string, error) {
	var args []string
	switch action {
	case "status":
		args = []string{"supervisorctl", "status"}
		if process != "" {
			args = append(args, process)
		}
	case "start":
		args = []string{"supervisorctl", "start", process}
	case "stop":
		args = []string{"supervisorctl", "stop", process}
	case "restart":
		args = []string{"supervisorctl", "restart", process}
	case "tail":
		args = []string{"supervisorctl", "tail", "-f", process}
	default:
		return "", fmt.Errorf("unknown supervisorctl action: %s", action)
	}
	out, err := exec.Command(args[0], args[1:]...).CombinedOutput()
	if err != nil {
		return "", fmt.Errorf("supervisorctl %s: %s", action, strings.TrimSpace(string(out)))
	}
	return strings.TrimSpace(string(out)), nil
}

// ─── PHP CLI ──────────────────────────────────────────────────────────────────

func SetPHPCLI(version string) error {
	return run("update-alternatives", "--set", "php", "/usr/bin/php"+version)
}

// ─── Deploy script ────────────────────────────────────────────────────────────

func WriteDeployScript(username, content string) error {
	path := fmt.Sprintf("/home/%s/git/deploy.sh", username)
	if err := os.WriteFile(path, []byte(content), 0750); err != nil {
		return err
	}
	return run("chown", username+":www-data", path)
}

// ─── Spikster user password reset ─────────────────────────────────────────────

func ResetSpiksterPassword(newPass string) error {
	cmd := exec.Command("chpasswd")
	cmd.Stdin = strings.NewReader("spikster:" + newPass + "\n")
	out, err := cmd.CombinedOutput()
	if err != nil {
		return fmt.Errorf("chpasswd: %s", strings.TrimSpace(string(out)))
	}
	return nil
}

// ─── Panel nginx domain ───────────────────────────────────────────────────────

var panelNginxTpl = `server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_tokens off;
    server_name {{.Domain}};
    root /var/www/html/public;
    index index.php;
    access_log /var/log/nginx/panel.access.log;
    error_log /var/log/nginx/panel.error.log;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
`

type PanelConf struct{ Domain string }

func AddPanelDomain(domain string) error {
	if err := writeTpl("/etc/nginx/sites-available/panel.conf", panelNginxTpl, PanelConf{domain}); err != nil {
		return err
	}
	os.Remove("/etc/nginx/sites-enabled/panel.conf")
	if err := os.Symlink("/etc/nginx/sites-available/panel.conf", "/etc/nginx/sites-enabled/panel.conf"); err != nil {
		return err
	}
	return reloadService("nginx")
}

func RemovePanelDomain() error {
	os.Remove("/etc/nginx/sites-enabled/panel.conf")
	os.Remove("/etc/nginx/sites-available/panel.conf")
	return reloadService("nginx")
}

func EnablePanelSSL(domain string) error {
	steps := []struct {
		name string
		fn   func() error
	}{
		{"certbot", func() error {
			return run("certbot", "--nginx", "-d", domain,
				"--non-interactive", "--agree-tos", "--register-unsafely-without-email")
		}},
		{"enable http2", func() error {
			return replaceInFile("/etc/nginx/sites-enabled/panel.conf", "443 ssl;", "443 ssl http2;")
		}},
		{"reload nginx", func() error { return reloadService("nginx") }},
	}
	for _, step := range steps {
		if err := step.fn(); err != nil {
			return fmt.Errorf("[%s] %w", step.name, err)
		}
	}
	return nil
}

// ─── Node.js ──────────────────────────────────────────────────────────────────

func SetupNodejs(username string, port int, script string) error {
	// Validate script name to prevent command injection
	if script == "" || strings.ContainsAny(script, ";&|`$(){}[]!<>#~") {
		return fmt.Errorf("invalid script name: %q", script)
	}
	if strings.Contains(script, "..") || strings.HasPrefix(script, "/") {
		return fmt.Errorf("script name must be a filename, not a path: %q", script)
	}
	// Install PM2 if needed
	run("npm", "install", "-g", "pm2")
	// Start app using exec.Command with separate args (no shell)
	return run("su", "-", username, "-c",
		fmt.Sprintf("cd ~/web && pm2 start '%s' --name '%s' --watch", script, username))
}

func StopNodejs(username string) error {
	return run("su", "-", username, "-c", fmt.Sprintf("pm2 delete %s", username))
}
