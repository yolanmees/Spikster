package socket

import (
	"fmt"
	"log"
	"net"
	"os"
)

// StartTCP starts a TCP listener for remote panel connections
// Only binds to localhost by default — SSH tunnel or VPN required for remote access
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
		go handle(conn)
	}
}
