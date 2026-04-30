package remote

import (
	"encoding/json"
	"fmt"
	"net"
	"time"
)

// Client connects to a remote spikster daemon via TCP
// Remote servers expose the daemon on a local port, tunneled via SSH or directly
type Client struct {
	Host    string
	Port    int
	Timeout time.Duration
}

type Request struct {
	Action string            `json:"action"`
	Params map[string]string `json:"params"`
}

type Response struct {
	Success bool   `json:"success"`
	Output  string `json:"output"`
	Error   string `json:"error,omitempty"`
}

func NewClient(host string, port int) *Client {
	return &Client{Host: host, Port: port, Timeout: 30 * time.Second}
}

func (c *Client) Send(action string, params map[string]string) (*Response, error) {
	addr := fmt.Sprintf("%s:%d", c.Host, c.Port)
	conn, err := net.DialTimeout("tcp", addr, c.Timeout)
	if err != nil {
		return nil, fmt.Errorf("connect to %s: %w", addr, err)
	}
	defer conn.Close()
	conn.SetDeadline(time.Now().Add(c.Timeout))

	req := Request{Action: action, Params: params}
	if err := json.NewEncoder(conn).Encode(req); err != nil {
		return nil, fmt.Errorf("send request: %w", err)
	}

	var resp Response
	if err := json.NewDecoder(conn).Decode(&resp); err != nil {
		return nil, fmt.Errorf("read response: %w", err)
	}

	return &resp, nil
}
