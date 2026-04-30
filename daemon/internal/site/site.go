package site

import (
	"bytes"
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"text/template"
)

type Site struct {
	ID       string
	Domain   string
	Username string
	Password string
	DBName   string
	DBPass   string
	DBRoot   string
	PHP      string
	Basepath string
}

func (s Site) WebRoot() string {
	base := filepath.Join("/home", s.Username, "web")
	if s.Basepath != "" {
		return filepath.Join(base, strings.TrimPrefix(s.Basepath, "/"))
	}
	return base
}

func Create(s Site) error {
	steps := []struct {
		name string
		fn   func() error
	}{
		{"create user", func() error { return createUser(s) }},
		{"create dirs", func() error { return createDirs(s) }},
		{"write welcome page", func() error { return writeWelcome(s) }},
		{"write nginx config", func() error { return writeNginxConfig(s) }},
		{"write php-fpm pool", func() error { return writePHPPool(s) }},
		{"reload nginx", func() error { return reloadService("nginx") }},
		{"reload php-fpm", func() error { return reloadService(fmt.Sprintf("php%s-fpm", s.PHP)) }},
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
	os.Remove(fmt.Sprintf("/etc/nginx/sites-enabled/%s.conf", s.Username))
	os.Remove(fmt.Sprintf("/etc/nginx/sites-available/%s.conf", s.Username))
	os.Remove(fmt.Sprintf("/etc/nginx/spikster/%s.conf", s.Username))
	os.Remove(fmt.Sprintf("/etc/php/%s/fpm/pool.d/%s.conf", s.PHP, s.Username))
	reloadService("nginx")
	reloadService(fmt.Sprintf("php%s-fpm", s.PHP))
	dropDatabase(s)
	return run("userdel", "-r", s.Username)
}

// ─── Steps ────────────────────────────────────────────────────────────────────

func createUser(s Site) error {
	if run("id", s.Username) == nil {
		return nil // already exists
	}
	if err := run("useradd", "-m", "-s", "/bin/bash", "-d", "/home/"+s.Username, "-G", "www-data", s.Username); err != nil {
		return err
	}
	return run("bash", "-c", fmt.Sprintf("echo '%s:%s' | chpasswd", s.Username, s.Password))
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
	custom := fmt.Sprintf("/etc/nginx/spikster/%s.conf", s.Username)
	return os.WriteFile(custom, []byte("# Custom nginx rules — edit freely\n"), 0644)
}

func writePHPPool(s Site) error {
	return writeTpl(fmt.Sprintf("/etc/php/%s/fpm/pool.d/%s.conf", s.PHP, s.Username), phpPoolTpl, s)
}

func createDatabase(s Site) error {
	sql := fmt.Sprintf(
		"CREATE DATABASE IF NOT EXISTS `%s`; CREATE USER '%s'@'%%' IDENTIFIED WITH mysql_native_password BY '%s'; GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'%%' WITH GRANT OPTION; FLUSH PRIVILEGES;",
		s.DBName, s.Username, s.DBPass, s.DBName, s.Username,
	)
	return run("mysql", "-uspikster", "-p"+s.DBRoot, "-e", sql)
}

func dropDatabase(s Site) error {
	sql := fmt.Sprintf("DROP DATABASE IF EXISTS `%s`; DROP USER IF EXISTS '%s'@'%%'; FLUSH PRIVILEGES;", s.DBName, s.Username)
	return run("mysql", "-uspikster", "-p"+s.DBRoot, "-e", sql)
}

func setPermissions(s Site) error {
	home := "/home/" + s.Username
	run("chown", "-R", s.Username+":www-data", home)
	run("chmod", "755", home)
	run("find", home+"/web", "-type", "d", "-exec", "chmod", "755", "{}", "+")
	run("find", home+"/web", "-type", "f", "-exec", "chmod", "644", "{}", "+")
	return nil
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
				newRoot += "/" + strings.TrimPrefix(newBasepath, "/")
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
