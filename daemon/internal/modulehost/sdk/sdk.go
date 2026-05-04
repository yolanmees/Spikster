// Package modulesdk provides helpers for writing Spikster module handler binaries.
// A handler binary reads one JSON Request from stdin, performs its action,
// then writes one JSON Response to stdout.
package modulesdk

import (
	"bufio"
	"encoding/json"
	"fmt"
	"net"
	"os"
	"strings"
)

// APIContext carries the daemon's unix socket path and auth token.
type APIContext struct {
	Socket string `json:"socket"`
	Token  string `json:"token"`
}

// Request is the JSON object written to the handler's stdin by modulehost.
type Request struct {
	Action string            `json:"action"`
	Params map[string]string `json:"params"`
	API    APIContext         `json:"api"`
}

// Response is the JSON object the handler must write to stdout.
type Response struct {
	Success bool   `json:"success"`
	Output  string `json:"output"`
	Error   string `json:"error,omitempty"`
}

// ReadRequest decodes a Request from os.Stdin (newline-delimited JSON).
func ReadRequest() (*Request, error) {
	var req Request
	if err := json.NewDecoder(os.Stdin).Decode(&req); err != nil {
		return nil, fmt.Errorf("sdk: read request: %w", err)
	}
	return &req, nil
}

// WriteResponse encodes a Response to os.Stdout as JSON followed by a newline.
func WriteResponse(success bool, output string, errMsg string) {
	resp := Response{Success: success, Output: output, Error: errMsg}
	data, _ := json.Marshal(resp)
	os.Stdout.Write(data)
	os.Stdout.Write([]byte("\n"))
}

// CallAPI calls a module API action on the daemon via the unix socket.
// It authenticates with ctx.Token, sends action+params, and returns the daemon Response.
func CallAPI(ctx APIContext, action string, params map[string]string) (*Response, error) {
	conn, err := net.Dial("unix", ctx.Socket)
	if err != nil {
		return nil, fmt.Errorf("sdk: connect to daemon: %w", err)
	}
	defer conn.Close()

	// Authenticate
	fmt.Fprintf(conn, "TOKEN %s\n", ctx.Token)

	// Send request
	req := struct {
		Action string            `json:"action"`
		Params map[string]string `json:"params"`
	}{Action: action, Params: params}
	if err := json.NewEncoder(conn).Encode(req); err != nil {
		return nil, fmt.Errorf("sdk: send request: %w", err)
	}

	// Read response
	reader := bufio.NewReader(conn)
	line, err := reader.ReadString('\n')
	if err != nil {
		// Try reading whatever came back
		line = strings.TrimSpace(line)
		if line == "" {
			return nil, fmt.Errorf("sdk: read daemon response: %w", err)
		}
	}

	var resp Response
	if err := json.Unmarshal([]byte(strings.TrimSpace(line)), &resp); err != nil {
		return nil, fmt.Errorf("sdk: parse daemon response: %w", err)
	}
	return &resp, nil
}
