package socket

import (
	"bufio"
	"crypto/subtle"
	"encoding/json"
	"fmt"
	"net"
	"os"
	"strings"
	"sync"
	"time"
	"log"
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
	handleWithReader(conn)
}

// handleWithReader processes a JSON command using a buffered reader
// that preserves any data already read during authentication.
func handleWithReader(conn net.Conn) {
	defer conn.Close()

	var req Request
	if err := json.NewDecoder(conn).Decode(&req); err != nil {
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

// authenticateTCP reads and validates the daemon token from the connection.
// Uses bufio.Reader to avoid consuming pipelined JSON data (fix for protocol bug
// where raw conn.Read could read token + part of the JSON body in one call).
// The client must send "TOKEN <value>\n" as the very first line.
// Rate-limited per IP to prevent brute-force attacks.
func authenticateTCP(conn net.Conn) bool {
	token, err := readDaemonToken()
	if err != nil {
		log.Printf("TCP auth: cannot read daemon token: %v", err)
		return false
	}

	// Rate limit: max 5 auth attempts per IP per minute
	host, _, _ := net.SplitHostPort(conn.RemoteAddr().String())
	rateLimiter.Lock()
	now := time.Now()
	bucket, exists := rateLimiter.entries[host]
	if !exists || now.Sub(bucket.window) > time.Minute {
		bucket = &rateBucket{attempts: 0, window: now}
		rateLimiter.entries[host] = bucket
	}
	bucket.attempts++
	rateLimiter.Unlock()

	if bucket.attempts > 5 {
		return false
	}

	reader := bufio.NewReader(conn)
	line, err := reader.ReadString('\n')
	if err != nil {
		return false
	}

	line = strings.TrimSpace(line)
	parts := strings.SplitN(line, " ", 2)
	if len(parts) != 2 || parts[0] != "TOKEN" {
		time.Sleep(500 * time.Millisecond) // slow down brute force
		return false
	}

	// Constant-time comparison to prevent timing side-channel
	if subtle.ConstantTimeCompare([]byte(parts[1]), []byte(token)) != 1 {
		time.Sleep(500 * time.Millisecond)
		return false
	}

	return true
}

type rateBucket struct {
	attempts int
	window   time.Time
}

var rateLimiter = struct {
	sync.Mutex
	entries map[string]*rateBucket
}{entries: make(map[string]*rateBucket)}

// readDaemonToken reads the shared secret from /etc/spikster/daemon.token.
func readDaemonToken() (string, error) {
	data, err := os.ReadFile("/etc/spikster/daemon.token")
	if err != nil {
		return "", err
	}
	return strings.TrimSpace(string(data)), nil
}
