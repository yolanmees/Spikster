package socket

import (
	"fmt"
	"log"
	"net"
	"os"
	"strings"
)

// StartTCP starts a TCP listener for remote panel connections.
// Only binds to localhost by default — SSH tunnel or VPN required for remote access.
// All connections must present a valid daemon token as the first line.
func StartTCP(port string) {
	if port == "" {
		port = "18999"
	}

	// Only localhost by default — remote access via SSH tunnel
	bind := "127.0.0.1:" + port

	// Allow override via env for trusted network setups
	if env := os.Getenv("SPIKSTER_DAEMON_BIND"); env != "" {
		bind = env
	}

	l, err := net.Listen("tcp", bind)
	if err != nil {
		log.Printf("TCP listener failed on %s: %v", bind, err)
		return
	}
	defer l.Close()

	fmt.Println("spikster daemon TCP listening on", bind)

	for {
		conn, err := l.Accept()
		if err != nil {
			log.Println("TCP accept error:", err)
			continue
		}
		go handleTCP(conn)
	}
}

func handleTCP(conn net.Conn) {
	if !authenticateTCP(conn) {
		respond(conn, false, "", "unauthorized")
		conn.Close()
		return
	}
	handle(conn)
}

// authenticateTCP reads and validates the daemon token from the connection.
// The client must send "TOKEN <value>\n" as the very first line.
func authenticateTCP(conn net.Conn) bool {
	token, err := readDaemonToken()
	if err != nil {
		log.Printf("TCP auth: cannot read daemon token: %v", err)
		return false
	}

	buf := make([]byte, 256)
	n, err := conn.Read(buf)
	if err != nil || n == 0 {
		return false
	}

	line := strings.TrimSpace(string(buf[:n]))
	parts := strings.SplitN(line, " ", 2)
	if len(parts) != 2 || parts[0] != "TOKEN" {
		return false
	}

	return parts[1] == token
}

// readDaemonToken reads the shared secret from /etc/spikster/daemon.token.
func readDaemonToken() (string, error) {
	data, err := os.ReadFile("/etc/spikster/daemon.token")
	if err != nil {
		return "", err
	}
	return strings.TrimSpace(string(data)), nil
}
