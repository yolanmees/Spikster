package socket

import (
	"encoding/json"
	"fmt"
	"log"
	"net"
	"os"
	"os/exec"
)

const socketPath = "/var/run/spikster.sock"

type Request struct {
	Action  string            `json:"action"`
	Params  map[string]string `json:"params"`
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

	// Allow www-data to write
	os.Chmod(socketPath, 0660)

	fmt.Println("spikster daemon started on", socketPath)

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
	resp := Response{Success: success, Output: output, Error: errMsg}
	json.NewEncoder(conn).Encode(resp)
}

func dispatch(req Request) (string, error) {
	switch req.Action {
	case "restart":
		service := req.Params["service"]
		if service == "" {
			return "", fmt.Errorf("missing service param")
		}
		return systemctlAction("restart", service)
	case "start":
		return systemctlAction("start", req.Params["service"])
	case "stop":
		return systemctlAction("stop", req.Params["service"])
	case "status":
		return systemctlAction("status", req.Params["service"])
	case "exec":
		// Whitelist only — never allow arbitrary exec from Laravel
		return "", fmt.Errorf("exec not allowed via socket")
	default:
		return "", fmt.Errorf("unknown action: %s", req.Action)
	}
}

func systemctlAction(action, service string) (string, error) {
	allowed := map[string]bool{
		"nginx": true, "mysql": true, "redis-server": true,
		"fail2ban": true, "php8.2-fpm": true, "php8.3-fpm": true, "php8.4-fpm": true,
	}
	if !allowed[service] {
		return "", fmt.Errorf("service not allowed: %s", service)
	}
	out, err := exec.Command("systemctl", action, service).CombinedOutput()
	return string(out), err
}
