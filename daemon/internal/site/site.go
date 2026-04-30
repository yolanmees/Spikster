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
