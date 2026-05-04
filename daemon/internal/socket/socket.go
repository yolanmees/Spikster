package socket

import (
	"encoding/json"
	"fmt"
	"net"
	"strconv"
	"time"
	"log"
	"os"
	"os/exec"
	"bufio"
	"strings"
	"path/filepath"

	"github.com/yolanmees/spikster/daemon/internal/backup"
	"github.com/yolanmees/spikster/daemon/internal/email"
	"github.com/yolanmees/spikster/daemon/internal/metrics"
	"github.com/yolanmees/spikster/daemon/internal/server"
	"github.com/yolanmees/spikster/daemon/internal/cron"
	"github.com/yolanmees/spikster/daemon/internal/ftp"
	"github.com/yolanmees/spikster/daemon/internal/site"
	"github.com/yolanmees/spikster/daemon/internal/modulehost"
)

const socketPath = "/var/run/spikster.sock"

type Request struct {
	Action string            `json:"action"`
	Params map[string]string `json:"params"`
}

type Response struct {
	Success bool   `json:"success"`
	Output  string `json:"output"`
	Error   string `json:"error,omitempty"`
}

func Start() {
	modulehost.LoadModules("/var/www/html/Modules")
	os.Remove(socketPath)
	l, err := net.Listen("unix", socketPath)
	if err != nil {
		log.Fatal("Failed to start socket:", err)
	}
	defer l.Close()
	os.Chmod(socketPath, 0660)
	fmt.Println("spikster daemon listening on", socketPath)

	for {
		conn, err := l.Accept()
		if err != nil {
			log.Println("Accept error:", err)
			continue
		}
		go handle(conn)
	}
}

func handle(conn net.Conn) {
	defer conn.Close()

	// Require token authentication (same as TCP handler)
	token, err := readDaemonToken()
	if err != nil {
		log.Printf("Unix socket auth: cannot read daemon token: %v", err)
		respond(conn, false, "", "authentication error")
		return
	}

	reader := bufio.NewReader(conn)
	line, err := reader.ReadString('\n')
	if err != nil {
		respond(conn, false, "", "authentication required")
		return
	}

	line = strings.TrimSpace(line)
	parts := strings.SplitN(line, " ", 2)
	if len(parts) != 2 || parts[0] != "TOKEN" || parts[1] != token {
		respond(conn, false, "", "unauthorized")
		return
	}

	var req Request
	if err := json.NewDecoder(reader).Decode(&req); err != nil {
		respond(conn, false, "", "invalid request")
		return
	}
	output, err := dispatch(req)
	if err != nil {
		auditLog(req.Action, false, err.Error())
		respond(conn, false, output, err.Error())
		return
	}
	auditLog(req.Action, true, "")
	respond(conn, true, output, "")
}

// auditLog writes an action entry to /var/log/spikster-daemon.log.
func auditLog(action string, success bool, errMsg string) {
	f, err := os.OpenFile("/var/log/spikster-daemon.log",
		os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0640)
	if err != nil {
		log.Printf("audit log: %v", err)
		return
	}
	defer f.Close()
	status := "OK"
	if !success {
		status = "ERR: " + errMsg
	}
	fmt.Fprintf(f, "%s  action=%-40s  %s\n",
		time.Now().Format("2006-01-02 15:04:05"), action, status)
}

func respond(conn net.Conn, success bool, output, errMsg string) {
	json.NewEncoder(conn).Encode(Response{Success: success, Output: output, Error: errMsg})
}

func dispatch(req Request) (string, error) {
	switch req.Action {

	// ── Metrics ──────────────────────────────────────────────────────────────
	case "metrics":
		out, err := metrics.CollectJSON()
		if err != nil { return "", err }
		return out, nil

	// ── Malware scan ─────────────────────────────────────────────────────────
	case "server.malware-scan":
		out, err := server.MalwareScan()
		if err != nil { return "", err }
		return out, nil

	// ── Service management ────────────────────────────────────────────────
	case "restart":
		return systemctlAction("restart", req.Params["service"])
	case "start":
		return systemctlAction("start", req.Params["service"])
	case "stop":
		return systemctlAction("stop", req.Params["service"])
	case "reload":
		return systemctlAction("reload", req.Params["service"])
	case "status":
		return systemctlAction("status", req.Params["service"])

	// ── Site management ───────────────────────────────────────────────────
	case "site.create":
		s := siteFromParams(req.Params)
		if err := site.Create(s); err != nil {
			return "", err
		}
		return "site created", nil

	case "site.delete":
		s := siteFromParams(req.Params)
		if err := site.Delete(s); err != nil {
			return "", err
		}
		return "site deleted", nil

	case "site.update-php":
		err := site.UpdatePHP(req.Params["username"], req.Params["old_php"], req.Params["new_php"])
		if err != nil { return "", err }
		return "php updated", nil

	case "site.update-domain":
		err := site.UpdateDomain(req.Params["username"], req.Params["old_domain"], req.Params["new_domain"])
		if err != nil { return "", err }
		return "domain updated", nil

	case "site.update-basepath":
		err := site.UpdateBasepath(req.Params["username"], req.Params["basepath"])
		if err != nil { return "", err }
		return "basepath updated", nil

	case "site.ssl":
		err := site.EnableSSL(req.Params["username"], req.Params["domain"])
		if err != nil { return "", err }
		return "ssl enabled", nil

	// ── Alias management ─────────────────────────────────────────────────
	case "alias.create":
		a := site.Alias{
			Domain:   req.Params["domain"],
			Username: req.Params["username"],
			PHP:      req.Params["php"],
			Basepath: req.Params["basepath"],
		}
		if err := site.CreateAlias(a); err != nil { return "", err }
		return "alias created", nil

	case "alias.delete":
		if err := site.DeleteAlias(req.Params["domain"]); err != nil { return "", err }
		return "alias deleted", nil

	case "alias.ssl":
		if err := site.EnableAliasSSL(req.Params["domain"]); err != nil { return "", err }
		return "alias ssl enabled", nil

	// ── Cron management ──────────────────────────────────────────────────
	case "cron.write":
		if err := cron.Write(req.Params["content"]); err != nil { return "", err }
		return "cron updated", nil

	case "cron.read":
		content, err := cron.Read()
		if err != nil { return "", err }
		return content, nil

	// ── Site passwords ───────────────────────────────────────────────────────
	case "site.user-password":
		err := site.UpdateUserPassword(req.Params["username"], req.Params["password"])
		if err != nil { return "", err }
		return "password updated", nil

	case "site.db-password":
		err := site.UpdateDBPassword(req.Params["username"], req.Params["old_pass"], req.Params["new_pass"])
		if err != nil { return "", err }
		return "db password updated", nil

	// ── Supervisor ────────────────────────────────────────────────────────────
	case "site.supervisor":
		err := site.UpdateSupervisor(req.Params["username"], req.Params["script"])
		if err != nil { return "", err }
		return "supervisor updated", nil

	case "server.supervisorctl":
		process := req.Params["process"]
		if process != "" && !safeProcessName(process) {
			return "", fmt.Errorf("invalid process name: %q", process)
		}
		out, err := site.SupervisorCtl(req.Params["action"], process)
		if err != nil { return "", err }
		return out, nil

	// ── PHP CLI ───────────────────────────────────────────────────────────────
	case "server.php-cli":
		err := site.SetPHPCLI(req.Params["version"])
		if err != nil { return "", err }
		return "php cli updated", nil

	// ── Deploy ──────────────────────────────────────────────────────────────────
	case "site.deploy":
		d := site.DeployParams{
			Username:      req.Params["username"],
			RepoURL:       req.Params["repo_url"],
			Branch:        req.Params["branch"],
			PHP:           req.Params["php"],
			Composer:      req.Params["composer"] == "true",
			NPM:           req.Params["npm"] == "true",
			ArtisanMig:    req.Params["artisan_migrate"] == "true",
			ArtisanCache:  req.Params["artisan_cache"] == "true",
		}
		out, err := site.Deploy(d)
		if err != nil { return "", err }
		return out, nil

	case "site.deploy-rollback":
		out, err := site.DeployRollback(req.Params["username"], req.Params["commit_hash"])
		if err != nil { return "", err }
		return out, nil

	case "site.deploy-history":
		count := 10
		if n, err := strconv.Atoi(req.Params["count"]); err == nil && n > 0 {
			count = n
		}
		out, err := site.DeployHistory(req.Params["username"], count)
		if err != nil { return "", err }
		return out, nil

	// ── Deploy script ─────────────────────────────────────────────────────────
	case "site.php-settings":
		s := siteFromParams(req.Params)
		if err := site.WritePHPPool(s); err != nil {
			return "", err
		}
		if err := site.ReloadPHP(s); err != nil {
			return "", err
		}
		return "php settings updated", nil

	case "site.exec":
		username := req.Params["username"]
		command := req.Params["command"]
		if command == "" {
			return "", fmt.Errorf("command is required")
		}
		out, err := site.ExecCommand(username, command)
		if err != nil {
			// Return output even on error (command may have failed but still produced output)
			return out, err
		}
		return out, nil

	case "site.deploy-script":
		err := site.WriteDeployScript(req.Params["username"], req.Params["content"])
		if err != nil { return "", err }
		return "deploy script updated", nil

	case "site.nginx-config":
		s := siteFromParams(req.Params)
		if err := site.WriteCustomNginxConfig(s); err != nil {
			return "", err
		}
		if err := site.ReloadNginx(); err != nil {
			return "", err
		}
		return "nginx config updated", nil

	// ── Spikster password reset ───────────────────────────────────────────────
	case "server.root-reset":
		err := site.ResetSpiksterPassword(req.Params["new_pass"])
		if err != nil { return "", err }
		return "password reset", nil

	// ── Panel domain ──────────────────────────────────────────────────────────
	case "panel.domain-add":
		err := site.AddPanelDomain(req.Params["domain"])
		if err != nil { return "", err }
		return "panel domain added", nil

	case "panel.domain-remove":
		if err := site.RemovePanelDomain(); err != nil { return "", err }
		return "panel domain removed", nil

	case "panel.domain-ssl":
		err := site.EnablePanelSSL(req.Params["domain"])
		if err != nil { return "", err }
		return "panel ssl enabled", nil

	// ── Node.js ───────────────────────────────────────────────────────────────
	case "nodejs.setup":
		port := 3000
		if p := req.Params["port"]; p != "" {
			if n, err := strconv.Atoi(p); err == nil && n > 1024 && n < 65535 {
				port = n
			}
		}
		err := site.SetupNodejs(req.Params["username"], port, req.Params["script"])
		if err != nil { return "", err }
		return "nodejs setup", nil

	case "nodejs.stop":
		if err := site.StopNodejs(req.Params["username"]); err != nil { return "", err }
		return "nodejs stopped", nil

	// ── Backup ───────────────────────────────────────────────────────────────
	case "backup.create":
		p := req.Params
		r := backup.BackupRequest{
			SiteID: p["site_id"], Username: p["username"],
			DBName: p["db_name"], DBPass: p["db_pass"], DBRoot: p["db_root"],
			SiteRoot: p["site_root"],
		}
		path, err := backup.CreateFull(r)
		if err != nil { return "", err }
		return path, nil

	case "backup.encrypt":
		path, err := backup.EncryptBackup(req.Params["filepath"], req.Params["password"])
		if err != nil { return "", err }
		return path, nil

	case "backup.decrypt":
		path, err := backup.DecryptBackup(req.Params["filepath"], req.Params["password"])
		if err != nil { return "", err }
		return path, nil

	case "backup.restore":
		p := req.Params
		r := backup.RestoreRequest{
			ArchivePath: p["archive"], Username: p["username"],
			DBName: p["db_name"], DBRoot: p["db_root"], SiteRoot: p["site_root"],
		}
		if err := backup.Restore(r); err != nil { return "", err }
		return "restored", nil

	case "backup.upload-s3":
		p := req.Params
		params := backup.UploadS3Params{
			FilePath:     p["filepath"],
			Bucket:       p["bucket"],
			Region:       p["region"],
			AccessKey:    p["access_key"],
			SecretKey:    p["secret_key"],
			S3Key:        p["s3_key"],
			Endpoint:     p["endpoint"],
			StorageClass: p["storage_class"],
		}
		if err := backup.UploadToS3(params); err != nil { return "", err }
		return "uploaded to s3", nil

	case "backup.upload-ftp":
		p := req.Params
		params := backup.UploadFTPParams{
			FilePath:   p["filepath"],
			FileName:   p["filename"],
			Host:       p["host"],
			Port:       p["port"],
			Username:   p["username"],
			Password:   p["password"],
			RemotePath: p["remote_path"],
			Passive:    true,
		}
		if f := p["passive"]; f == "false" || f == "0" {
			params.Passive = false
		}
		if err := backup.UploadToFTP(params); err != nil { return "", err }
		return "uploaded to ftp", nil

	// ── FTP ───────────────────────────────────────────────────────────────────
	case "ftp.create":
		p := req.Params
		u := ftp.FTPUser{Username: p["username"], Password: p["password"], HomeDir: p["home_dir"]}
		if err := ftp.CreateUser(u); err != nil { return "", err }
		return "ftp user created", nil

	case "ftp.update-password":
		if err := ftp.UpdatePassword(req.Params["username"], req.Params["password"]); err != nil { return "", err }
		return "ftp password updated", nil

	case "ftp.delete":
		if err := ftp.DeleteUser(req.Params["username"]); err != nil { return "", err }
		return "ftp user deleted", nil

	case "ftp.disk-usage":
		usage, err := ftp.GetDiskUsage(req.Params["username"])
		if err != nil { return "", err }
		enc, _ := json.Marshal(usage)
		return string(enc), nil

	case "ftp.update-quota":
		quotaMB := 0
		if q := req.Params["quota_mb"]; q != "" {
			if n, err := strconv.Atoi(q); err == nil {
				quotaMB = n
			}
		}
		if err := ftp.UpdateQuota(req.Params["username"], quotaMB); err != nil { return "", err }
		return "ftp quota updated", nil

	case "ftp.test":
		if err := ftp.TestConnection(req.Params["username"], req.Params["password"]); err != nil { return "", err }
		return "ftp connection ok", nil


	// ── Server utils ─────────────────────────────────────────────────────────
	case "server.fail2ban-list":
		out, err := server.Fail2banList()
		if err != nil { return "", err }
		return out, nil

	case "server.fail2ban-status":
		out, err := server.Fail2banStatus(req.Params["jail"])
		if err != nil { return "", err }
		return out, nil

	case "server.package-list":
		out, err := server.PackageList()
		if err != nil { return "", err }
		return out, nil

	case "server.package-install":
		if err := server.PackageInstall(req.Params["package"]); err != nil { return "", err }
		return "installed", nil

	case "server.package-remove":
		if err := server.PackageRemove(req.Params["package"]); err != nil { return "", err }
		return "removed", nil


	// ── Email management ──────────────────────────────────────────────────────
	case "email.create":
		p := req.Params
		quota := 0
		if q := p["quota_mb"]; q != "" {
			if n, err := strconv.Atoi(q); err == nil {
				quota = n
			}
		}
		a := email.Account{
			Domain: p["domain"], Username: p["username"],
			Email: p["email"], PasswordHash: p["password_hash"], QuotaMB: quota,
		}
		if err := email.Create(a); err != nil { return "", err }
		return "email account created", nil

	case "email.delete":
		p := req.Params
		a := email.Account{Domain: p["domain"], Username: p["username"], Email: p["email"]}
		if err := email.Delete(a); err != nil { return "", err }
		return "email account deleted", nil

	case "email.update-password":
		if err := email.UpdatePassword(req.Params["email"], req.Params["password_hash"]); err != nil { return "", err }
		return "email password updated", nil

	case "email.update-quota":
		quota := 0
		if q := req.Params["quota_mb"]; q != "" {
			if n, err := strconv.Atoi(q); err == nil {
				quota = n
			}
		}
		if err := email.UpdateQuota(req.Params["email"], quota); err != nil { return "", err }
		return "email quota updated", nil

	case "email.forwarder-create":
		if err := email.CreateForwarder(req.Params["source"], req.Params["destination"]); err != nil { return "", err }
		return "forwarder created", nil

	case "email.forwarder-delete":
		if err := email.DeleteForwarder(req.Params["source"]); err != nil { return "", err }
		return "forwarder deleted", nil

	case "email.alias-create":
		if err := email.CreateAlias(req.Params["alias"], req.Params["target"]); err != nil { return "", err }
		return "email alias created", nil

	case "email.alias-delete":
		if err := email.DeleteAlias(req.Params["alias"]); err != nil { return "", err }
		return "email alias deleted", nil

	case "email.dkim-setup":
		result, err := email.SetupDKIM(req.Params["domain"], req.Params["selector"])
		if err != nil { return "", err }
		enc, _ := json.Marshal(result)
		return string(enc), nil

	case "email.autoresponder-update":
		p := req.Params
		par := email.AutoresponderParams{
			Domain: p["domain"], Username: p["username"],
			Enabled: p["enabled"] == "true",
			Subject: p["subject"], Message: p["message"],
			StartDate: p["start_date"], EndDate: p["end_date"],
		}
		if err := email.UpdateAutoresponder(par); err != nil { return "", err }
		return "autoresponder updated", nil

	case "email.roundcube-install":
		p := req.Params
		par := email.RoundcubeParams{
			Domain: p["domain"], SiteRoot: p["site_root"],
			DBName: p["db_name"], DBUser: p["db_user"], DBPass: p["db_pass"],
			PHP: p["php"],
		}
		if err := email.InstallRoundcube(par); err != nil { return "", err }
		return "roundcube installed", nil

	case "email.set-filters":
		p := req.Params
		par := email.SpamFilterParams{
			Domain:           p["domain"],
			SpamAction:       p["spam_action"],
			SpamScore:        p["spam_score"],
			VirusAction:      p["virus_action"],
			RejectSpam:       p["reject_spam"] == "true",
			RejectPhishing:   p["reject_phishing"] == "true",
			EnableDkimCheck:  p["enable_dkim_check"] == "true",
			EnableSpfCheck:   p["enable_spf_check"] == "true",
			EnableDmarcCheck: p["enable_dmarc_check"] == "true",
		}
		if err := email.SetFilters(par); err != nil { return "", err }
		return "spam filters updated", nil

	case "email.queue-list":
		out, err := email.QueueList()
		if err != nil { return "", err }
		return out, nil

	case "email.queue-retry":
		if err := email.QueueRetry(req.Params["queue_id"]); err != nil { return "", err }
		return "queue retry triggered", nil

	case "email.queue-delete":
		if err := email.QueueDelete(req.Params["queue_id"]); err != nil { return "", err }
		return "queue item deleted", nil

	case "email.log-tail":
		lines := 100
		if n, err := strconv.Atoi(req.Params["lines"]); err == nil && n > 0 {
			lines = n
		}
		out, err := email.LogTail(lines)
		if err != nil { return "", err }
		return out, nil

	// ── Fail2ban management ───────────────────────────────────────────────────
	case "fail2ban.ban":
		ip := req.Params["ip"]
		if net.ParseIP(ip) == nil {
			return "", fmt.Errorf("invalid IP address: %q", ip)
		}
		if err := server.Fail2banBan(ip, req.Params["jail"]); err != nil { return "", err }
		return "ip banned", nil

	case "fail2ban.unban":
		ip := req.Params["ip"]
		if net.ParseIP(ip) == nil {
			return "", fmt.Errorf("invalid IP address: %q", ip)
		}
		if err := server.Fail2banUnban(ip, req.Params["jail"]); err != nil { return "", err }
		return "ip unbanned", nil

	case "fail2ban.whitelist":
		ip := req.Params["ip"]
		if net.ParseIP(ip) == nil {
			return "", fmt.Errorf("invalid IP address: %q", ip)
		}
		if err := server.Fail2banWhitelist(ip); err != nil { return "", err }
		return "ip whitelisted", nil

	// ── File operations ──────────────────────────────────────────────────────
	case "file.delete-dir":
		if err := server.DeleteDirectory(req.Params["path"]); err != nil { return "", err }
		return "directory deleted", nil

	case "file.upload":
		content := req.Params["content"]
		if len(content) > 100*1024*1024 { // 100MB limit
			return "", fmt.Errorf("file too large: %d bytes", len(content))
		}
		if err := server.UploadFile(req.Params["path"], []byte(content)); err != nil { return "", err }
		return "file uploaded", nil

	case "file.chown":
		if err := server.ChangeOwnership(req.Params["path"], req.Params["owner"], req.Params["group"]); err != nil { return "", err }
		return "ownership changed", nil

	case "file.chmod":
		mode := os.FileMode(0644)
		if m := req.Params["mode"]; m != "" {
			if n, err := strconv.ParseUint(m, 8, 32); err == nil {
				mode = os.FileMode(n) & 0777 // strip setuid/setgid/sticky
			}
		}
		if err := server.SetPermissions(req.Params["path"], mode); err != nil { return "", err }
		return "permissions set", nil

	// ── Log rotation ─────────────────────────────────────────────────────────
	case "log.rotate":
		day := req.Params["day"]
		if day == "" {
			day = fmt.Sprintf("%d", int(time.Now().Weekday()))
		}
		// Validate day is numeric to prevent path traversal
		if _, err := strconv.Atoi(day); err != nil {
			return "", fmt.Errorf("invalid day parameter: must be numeric")
		}
		out, err := server.LogRotate(day)
		if err != nil { return "", err }
		return out, nil


	// ── Module API (whitelisted actions for module handlers) ──────────────────
	case "module.exec":
		username := req.Params["username"]
		command := req.Params["command"]
		if command == "" {
			return "", fmt.Errorf("module.exec: command is required")
		}
		return site.ExecCommand(username, command)

	case "module.file.read":
		path := req.Params["path"]
		if !strings.HasPrefix(filepath.Clean(path), "/home/") {
			return "", fmt.Errorf("module.file.read: path must be under /home/")
		}
		data, err := os.ReadFile(path)
		if err != nil {
			return "", fmt.Errorf("module.file.read: %w", err)
		}
		if len(data) > 10*1024*1024 {
			return "", fmt.Errorf("module.file.read: file exceeds 10MB limit")
		}
		return string(data), nil

	case "module.file.write":
		path := req.Params["path"]
		if !strings.HasPrefix(filepath.Clean(path), "/home/") {
			return "", fmt.Errorf("module.file.write: path must be under /home/")
		}
		if err := os.WriteFile(path, []byte(req.Params["content"]), 0644); err != nil {
			return "", fmt.Errorf("module.file.write: %w", err)
		}
		return "file written", nil

	case "module.file.chown":
		if err := server.ChangeOwnership(req.Params["path"], req.Params["owner"], req.Params["group"]); err != nil {
			return "", err
		}
		return "ownership changed", nil

	case "module.mysql.query":
		sql := strings.TrimSpace(req.Params["sql"])
		upper := strings.ToUpper(sql)
		if !strings.HasPrefix(upper, "SELECT") {
			return "", fmt.Errorf("module.mysql.query: only SELECT queries are allowed")
		}
		cnf := fmt.Sprintf("[client]\nuser=root\npassword=%s\n", req.Params["db_root"])
		tmp, err := os.CreateTemp("", "spikster-mod-mysql-*.cnf")
		if err != nil {
			return "", err
		}
		defer os.Remove(tmp.Name())
		tmp.WriteString(cnf)
		tmp.Close()
		os.Chmod(tmp.Name(), 0600)
		out, err := exec.Command("mysql", "--defaults-extra-file="+tmp.Name(), "-e", sql).CombinedOutput()
		if err != nil {
			return "", fmt.Errorf("module.mysql.query: %s", strings.TrimSpace(string(out)))
		}
		return string(out), nil

	case "module.nginx.reload":
		out, err := exec.Command("systemctl", "reload", "nginx").CombinedOutput()
		if err != nil {
			return "", fmt.Errorf("nginx reload: %s", strings.TrimSpace(string(out)))
		}
		return "nginx reloaded", nil

	case "module.cron.read":
		return cron.Read()

	case "module.cron.write":
		if err := cron.Write(req.Params["content"]); err != nil {
			return "", err
		}
		return "cron updated", nil

	// ── Module handler fallback ───────────────────────────────────────────────
	default:
		out, err := modulehost.Dispatch(req.Action, req.Params)
		if err != nil && strings.Contains(err.Error(), "no module handler for action") {
			return "", fmt.Errorf("unknown action: %s", req.Action)
		}
		return out, err
	}
}

var allowedServices = map[string]bool{
	"nginx": true, "mysql": true, "redis-server": true,
	"fail2ban": true, "php8.2-fpm": true, "php8.3-fpm": true, "php8.4-fpm": true,
}

func systemctlAction(action, service string) (string, error) {
	if !allowedServices[service] {
		return "", fmt.Errorf("service not allowed: %s", service)
	}
	switch action {
	case "restart", "start", "stop", "reload", "status":
	default:
		return "", fmt.Errorf("action not allowed: %s", action)
	}
	out, err := exec.Command("systemctl", action, service).CombinedOutput()
	return string(out), err
}

func safeProcessName(name string) bool {
	if len(name) == 0 || len(name) > 64 {
		return false
	}
	for _, c := range name {
		if !((c >= 'a' && c <= 'z') || (c >= 'A' && c <= 'Z') ||
			(c >= '0' && c <= '9') || c == '_' || c == '-') {
			return false
		}
	}
	return true
}

func siteFromParams(p map[string]string) site.Site {
	return site.Site{
		ID:       p["id"],
		Domain:   p["domain"],
		Username: p["username"],
		Password: p["password"],
		DBName:   p["db_name"],
		DBPass:   p["db_pass"],
		DBRoot:   p["db_root"],
		PHP:               p["php"],
		Basepath:          p["basepath"],
		NginxConfig:       p["nginx_config"],
		PHPMemoryLimit:    p["php_memory_limit"],
		PHPMaxExecTime:    p["php_max_execution_time"],
		PHPMaxInputVars:   p["php_max_input_vars"],
		PHPPostMaxSize:    p["php_post_max_size"],
	}
}
