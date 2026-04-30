package socket

import (
	"encoding/json"
	"fmt"
	"log"
	"net"
	"os"
	"os/exec"

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
