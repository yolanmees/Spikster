package socket

import (
	"encoding/json"
	"fmt"
	"log"
	"net"
	"os"
	"os/exec"

	"github.com/yolanmees/spikster/daemon/internal/backup"
	"github.com/yolanmees/spikster/daemon/internal/server"
	"github.com/yolanmees/spikster/daemon/internal/cron"
	"github.com/yolanmees/spikster/daemon/internal/ftp"
	"github.com/yolanmees/spikster/daemon/internal/site"
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
	var req Request
	if err := json.NewDecoder(conn).Decode(&req); err != nil {
		respond(conn, false, "", "invalid request")
		return
	}
	output, err := dispatch(req)
	if err != nil {
		respond(conn, false, output, err.Error())
		return
	}
	respond(conn, true, output, "")
}

func respond(conn net.Conn, success bool, output, errMsg string) {
	json.NewEncoder(conn).Encode(Response{Success: success, Output: output, Error: errMsg})
}

func dispatch(req Request) (string, error) {
	switch req.Action {

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

	// ── PHP CLI ───────────────────────────────────────────────────────────────
	case "server.php-cli":
		err := site.SetPHPCLI(req.Params["version"])
		if err != nil { return "", err }
		return "php cli updated", nil

	// ── Deploy script ─────────────────────────────────────────────────────────
	case "site.deploy-script":
		err := site.WriteDeployScript(req.Params["username"], req.Params["content"])
		if err != nil { return "", err }
		return "deploy script updated", nil

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

	case "backup.restore":
		p := req.Params
		r := backup.RestoreRequest{
			ArchivePath: p["archive"], Username: p["username"],
			DBName: p["db_name"], DBRoot: p["db_root"], SiteRoot: p["site_root"],
		}
		if err := backup.Restore(r); err != nil { return "", err }
		return "restored", nil

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


	// ── Server utils ─────────────────────────────────────────────────────────
	case "server.fail2ban-list":
		out, err := server.Fail2banList()
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


	default:
		return "", fmt.Errorf("unknown action: %s", req.Action)
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
	out, err := exec.Command("systemctl", action, service).CombinedOutput()
	return string(out), err
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
		PHP:      p["php"],
		Basepath: p["basepath"],
	}
}
