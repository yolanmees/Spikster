package email

import (
	"bufio"
	"fmt"
	"os"
	"os/exec"
	"regexp"
	"strings"
)

// ── helpers ────────────────────────────────────────────────────────────────

var safeEmail = regexp.MustCompile(`^[a-zA-Z0-9@._+\-]+$`)
var safeDomain = regexp.MustCompile(`^[a-zA-Z0-9._\-]+$`)
var safeUser = regexp.MustCompile(`^[a-zA-Z0-9._\-]+$`)
var safeSelector = regexp.MustCompile(`^[a-zA-Z0-9_\-]+$`)

func validateEmail(s string) error {
	if !safeEmail.MatchString(s) {
		return fmt.Errorf("invalid email: %s", s)
	}
	return nil
}

func validateDomain(s string) error {
	if !safeDomain.MatchString(s) {
		return fmt.Errorf("invalid domain: %s", s)
	}
	return nil
}

func validateUser(s string) error {
	if !safeUser.MatchString(s) {
		return fmt.Errorf("invalid username: %s", s)
	}
	return nil
}

func validateSelector(s string) error {
	if !safeSelector.MatchString(s) {
		return fmt.Errorf("invalid selector: %s", s)
	}
	return nil
}

func run(args ...string) error {
	out, err := exec.Command(args[0], args[1:]...).CombinedOutput()
	if err != nil {
		return fmt.Errorf("%s: %s", args[0], strings.TrimSpace(string(out)))
	}
	return nil
}

func runOutput(args ...string) (string, error) {
	out, err := exec.Command(args[0], args[1:]...).Output()
	if err != nil {
		return "", fmt.Errorf("%s failed: %w", args[0], err)
	}
	return strings.TrimSpace(string(out)), nil
}

func removeLinesContaining(path, needle string) error {
	data, err := os.ReadFile(path)
	if err != nil {
		if os.IsNotExist(err) {
			return nil
		}
		return err
	}
	var kept []string
	scanner := bufio.NewScanner(strings.NewReader(string(data)))
	for scanner.Scan() {
		if !strings.Contains(scanner.Text(), needle) {
			kept = append(kept, scanner.Text())
		}
	}
	return os.WriteFile(path, []byte(strings.Join(kept, "\n")+"\n"), 0640)
}

func appendLine(path, line string) error {
	// Strip newlines to prevent config file corruption
	clean := strings.ReplaceAll(line, "\n", "")
	clean = strings.ReplaceAll(clean, "\r", "")
	f, err := os.OpenFile(path, os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0640)
	if err != nil {
		return err
	}
	defer f.Close()
	_, err = fmt.Fprintln(f, clean)
	return err
}

// ── Email account ──────────────────────────────────────────────────────────

type Account struct {
	Domain       string
	Username     string
	Email        string
	PasswordHash string
	QuotaMB      int
}

func Create(a Account) error {
	if err := validateDomain(a.Domain); err != nil {
		return err
	}
	if err := validateUser(a.Username); err != nil {
		return err
	}
	if err := validateEmail(a.Email); err != nil {
		return err
	}

	maildir := fmt.Sprintf("/var/mail/vhosts/%s/%s", a.Domain, a.Username)
	for _, sub := range []string{"cur", "new", "tmp"} {
		if err := os.MkdirAll(maildir+"/"+sub, 0770); err != nil {
			return fmt.Errorf("mkdir maildir: %w", err)
		}
	}
	if err := run("chown", "-R", "vmail:vmail",
		fmt.Sprintf("/var/mail/vhosts/%s", a.Domain)); err != nil {
		return err
	}

	if err := appendLine("/etc/postfix/vmailbox",
		fmt.Sprintf("%s %s/%s/", a.Email, a.Domain, a.Username)); err != nil {
		return fmt.Errorf("vmailbox append: %w", err)
	}
	if err := run("postmap", "/etc/postfix/vmailbox"); err != nil {
		return err
	}
	if err := appendLine("/etc/dovecot/users",
		fmt.Sprintf("%s:%s", a.Email, a.PasswordHash)); err != nil {
		return fmt.Errorf("dovecot users: %w", err)
	}
	if a.QuotaMB > 0 {
		if err := appendLine("/etc/dovecot/quota",
			fmt.Sprintf("%s:storage=%dM", a.Email, a.QuotaMB)); err != nil {
			return fmt.Errorf("quota append: %w", err)
		}
	}

	if err := run("systemctl", "reload", "postfix"); err != nil {
		return err
	}
	return run("systemctl", "reload", "dovecot")
}

func Delete(a Account) error {
	if err := validateDomain(a.Domain); err != nil {
		return err
	}
	if err := validateUser(a.Username); err != nil {
		return err
	}
	if err := validateEmail(a.Email); err != nil {
		return err
	}

	os.MkdirAll("/backups/email", 0750)
	ts, _ := runOutput("date", "+%Y-%m-%d-%H%M%S")
	archive := fmt.Sprintf("/backups/email/%s-%s.tar.gz", a.Email, ts)
	exec.Command("tar", "-czf", archive, "-C",
		fmt.Sprintf("/var/mail/vhosts/%s", a.Domain),
		a.Username+"/").Run()

	if err := removeLinesContaining("/etc/postfix/vmailbox", a.Email); err != nil {
		return err
	}
	if err := run("postmap", "/etc/postfix/vmailbox"); err != nil {
		return err
	}
	if err := removeLinesContaining("/etc/dovecot/users", a.Email); err != nil {
		return err
	}
	removeLinesContaining("/etc/dovecot/quota", a.Email)

	maildir := fmt.Sprintf("/var/mail/vhosts/%s/%s", a.Domain, a.Username)
	if err := os.RemoveAll(maildir); err != nil {
		return err
	}

	if err := run("systemctl", "reload", "postfix"); err != nil {
		return err
	}
	return run("systemctl", "reload", "dovecot")
}

func UpdatePassword(emailAddr, passwordHash string) error {
	if err := validateEmail(emailAddr); err != nil {
		return err
	}
	if err := removeLinesContaining("/etc/dovecot/users", emailAddr+":"); err != nil {
		return err
	}
	if err := appendLine("/etc/dovecot/users",
		fmt.Sprintf("%s:%s", emailAddr, passwordHash)); err != nil {
		return err
	}
	return run("systemctl", "reload", "dovecot")
}

func UpdateQuota(emailAddr string, quotaMB int) error {
	if err := validateEmail(emailAddr); err != nil {
		return err
	}
	removeLinesContaining("/etc/dovecot/quota", emailAddr)
	if quotaMB > 0 {
		if err := appendLine("/etc/dovecot/quota",
			fmt.Sprintf("%s:storage=%dM", emailAddr, quotaMB)); err != nil {
			return err
		}
	}
	return run("systemctl", "reload", "dovecot")
}

// ── Forwarder ──────────────────────────────────────────────────────────────

func CreateForwarder(source, destination string) error {
	if err := validateEmail(source); err != nil {
		return err
	}
	if err := validateEmail(destination); err != nil {
		return err
	}
	if err := appendLine("/etc/postfix/virtual",
		fmt.Sprintf("%s %s", source, destination)); err != nil {
		return err
	}
	if err := run("postmap", "/etc/postfix/virtual"); err != nil {
		return err
	}
	return run("systemctl", "reload", "postfix")
}

func DeleteForwarder(source string) error {
	if err := validateEmail(source); err != nil {
		return err
	}
	if err := removeLinesContaining("/etc/postfix/virtual", source+" "); err != nil {
		return err
	}
	if err := run("postmap", "/etc/postfix/virtual"); err != nil {
		return err
	}
	return run("systemctl", "reload", "postfix")
}

// ── Alias ─────────────────────────────────────────────────────────────────

func CreateAlias(aliasEmail, targetEmail string) error {
	if err := validateEmail(aliasEmail); err != nil {
		return err
	}
	if err := validateEmail(targetEmail); err != nil {
		return err
	}
	if err := appendLine("/etc/postfix/virtual",
		fmt.Sprintf("%s %s", aliasEmail, targetEmail)); err != nil {
		return err
	}
	if err := run("postmap", "/etc/postfix/virtual"); err != nil {
		return err
	}
	return run("systemctl", "reload", "postfix")
}

func DeleteAlias(aliasEmail string) error {
	if err := validateEmail(aliasEmail); err != nil {
		return err
	}
	if err := removeLinesContaining("/etc/postfix/virtual", aliasEmail+" "); err != nil {
		return err
	}
	if err := run("postmap", "/etc/postfix/virtual"); err != nil {
		return err
	}
	return run("systemctl", "reload", "postfix")
}

// ── DKIM ──────────────────────────────────────────────────────────────────

type DKIMResult struct {
	PrivateKey string
	PublicKey  string
}

func SetupDKIM(domain, selector string) (DKIMResult, error) {
	if err := validateDomain(domain); err != nil {
		return DKIMResult{}, err
	}
	if err := validateSelector(selector); err != nil {
		return DKIMResult{}, err
	}

	keyDir := fmt.Sprintf("/etc/opendkim/keys/%s", domain)
	if err := os.MkdirAll(keyDir, 0750); err != nil {
		return DKIMResult{}, fmt.Errorf("mkdir dkim: %w", err)
	}

	if err := run("opendkim-genkey", "-b", "2048",
		"-d", domain, "-s", selector, "-D", keyDir); err != nil {
		return DKIMResult{}, err
	}
	if err := run("chown", "opendkim:opendkim",
		fmt.Sprintf("%s/%s.private", keyDir, selector)); err != nil {
		return DKIMResult{}, err
	}

	privateKey, err := runOutput("cat", fmt.Sprintf("%s/%s.private", keyDir, selector))
	if err != nil {
		return DKIMResult{}, err
	}
	pubRaw, err := runOutput("cat", fmt.Sprintf("%s/%s.txt", keyDir, selector))
	if err != nil {
		return DKIMResult{}, err
	}

	re := regexp.MustCompile(`p=([A-Za-z0-9+/=\s]+)`)
	publicKey := ""
	if m := re.FindStringSubmatch(pubRaw); len(m) > 1 {
		publicKey = strings.NewReplacer(" ", "", "\n", "", "\t", "").Replace(m[1])
	}

	keyTableLine := fmt.Sprintf("%s._domainkey.%s %s:%s:%s/%s.private",
		selector, domain, domain, selector, keyDir, selector)
	signingLine := fmt.Sprintf("*@%s %s._domainkey.%s", domain, selector, domain)

	if err := appendLine("/etc/opendkim/KeyTable", keyTableLine); err != nil {
		return DKIMResult{}, err
	}
	if err := appendLine("/etc/opendkim/SigningTable", signingLine); err != nil {
		return DKIMResult{}, err
	}
	if err := run("systemctl", "reload", "opendkim"); err != nil {
		return DKIMResult{}, err
	}

	return DKIMResult{PrivateKey: privateKey, PublicKey: publicKey}, nil
}

// ── Autoresponder ─────────────────────────────────────────────────────────

type AutoresponderParams struct {
	Domain    string
	Username  string
	Enabled   bool
	Subject   string
	Message   string
	StartDate string
	EndDate   string
}

func UpdateAutoresponder(p AutoresponderParams) error {
	if err := validateDomain(p.Domain); err != nil {
		return err
	}
	if err := validateUser(p.Username); err != nil {
		return err
	}

	sieveDir := fmt.Sprintf("/var/mail/vhosts/%s/%s/sieve", p.Domain, p.Username)
	if err := os.MkdirAll(sieveDir, 0770); err != nil {
		return err
	}

	activeScript := sieveDir + "/active.svbin"

	if !p.Enabled {
		os.Remove(activeScript)
		return nil
	}

	subject := strings.ReplaceAll(p.Subject, `"`, `\"`)
	message := strings.ReplaceAll(p.Message, `"`, `\"`)

	var sb strings.Builder
	sb.WriteString("require [\"vacation\", \"date\", \"relational\"];\n\n")

	if p.StartDate != "" && p.EndDate != "" {
		sb.WriteString(fmt.Sprintf(
			"if allof (\n  currentdate :value \"ge\" \"date\" \"%s\",\n  currentdate :value \"le\" \"date\" \"%s\"\n) {\n  vacation :days 1 :subject \"%s\" \"%s\";\n}\n",
			p.StartDate, p.EndDate, subject, message))
	} else {
		sb.WriteString(fmt.Sprintf("vacation :days 1 :subject \"%s\" \"%s\";\n", subject, message))
	}

	sieveScript := sieveDir + "/vacation.sieve"
	if err := os.WriteFile(sieveScript, []byte(sb.String()), 0660); err != nil {
		return err
	}
	run("chown", "vmail:vmail", sieveScript)

	if err := run("sievec", sieveScript); err != nil {
		return err
	}

	svbin := sieveDir + "/vacation.svbin"
	os.Remove(activeScript)
	return os.Symlink(svbin, activeScript)
}

// ── Roundcube ─────────────────────────────────────────────────────────────

type RoundcubeParams struct {
	Domain   string
	SiteRoot string
	DBName   string
	DBUser   string
	DBPass   string
	PHP      string
}

func InstallRoundcube(p RoundcubeParams) error {
	if err := validateDomain(p.Domain); err != nil {
		return err
	}

	scriptPath := "/usr/share/spikster/install-roundcube.sh"
	if _, err := os.Stat(scriptPath); os.IsNotExist(err) {
		return fmt.Errorf("roundcube install script not found: %s", scriptPath)
	}

	// Write DB credentials to a temp env file to avoid exposing them in ps aux
	envFile, err := os.CreateTemp("", "roundcube-env-*.sh")
	if err != nil {
		return fmt.Errorf("create env file: %w", err)
	}
	envPath := envFile.Name()
	fmt.Fprintf(envFile, "RC_DOMAIN=%s\nRC_SITEROOT=%s\nRC_DBNAME=%s\nRC_DBUSER=%s\nRC_DBPASS=%s\n",
		p.Domain, p.SiteRoot, p.DBName, p.DBUser, p.DBPass)
	envFile.Close()
	defer os.Remove(envPath)

	if err := run("bash", "-c",
		fmt.Sprintf("source %s && bash %s \"$RC_DOMAIN\" \"$RC_SITEROOT\" \"$RC_DBNAME\" \"$RC_DBUSER\" \"$RC_DBPASS\"",
			envPath, scriptPath)); err != nil {
		return err
	}

	webmailPath := p.SiteRoot + "/webmail"
	phpSock := fmt.Sprintf("/var/run/php/php%s-fpm.sock", p.PHP)
	nginxConf := fmt.Sprintf(`# Roundcube Webmail — %s
location ^~ /webmail {
    alias %s;
    index index.php;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    location ~ /\. { deny all; }
    location ~ ^/webmail/(config|temp|logs|bin|SQL)/ { deny all; }
    location ~ ^/webmail/(.+\.php)$ {
        fastcgi_pass unix:%s;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME %s/$1;
        fastcgi_read_timeout 300;
    }
    location ~* ^/webmail/.+\.(css|js|png|jpg|ico|gif|xml|txt)$ {
        access_log off; expires 30d;
    }
}
`, p.Domain, webmailPath, phpSock, webmailPath)

	confPath := fmt.Sprintf("/etc/nginx/sites-available/%s-webmail.conf", p.Domain)
	if err := os.WriteFile(confPath, []byte(nginxConf), 0644); err != nil {
		return err
	}
	enabledPath := fmt.Sprintf("/etc/nginx/sites-enabled/%s-webmail.conf", p.Domain)
	os.Remove(enabledPath)
	if err := os.Symlink(confPath, enabledPath); err != nil {
		return err
	}
	if err := run("nginx", "-t"); err != nil {
		return err
	}
	return run("systemctl", "reload", "nginx")
}
