package email

import (
	"encoding/json"
	"fmt"
	"os/exec"
	"strings"
)

// MailQueueItem represents a single entry in the Postfix mail queue.
type MailQueueItem struct {
	ID      string `json:"id"`
	Size    string `json:"size"`
	Arrival string `json:"arrival"`
	Sender  string `json:"sender"`
	Recip   string `json:"recipient"`
}

// QueueList returns all messages in the Postfix mail queue.
func QueueList() (string, error) {
	out, err := exec.Command("mailq").Output()
	if err != nil {
		return "", fmt.Errorf("mailq: %w", err)
	}

	lines := strings.Split(string(out), "\n")
	var items []MailQueueItem

	for _, line := range lines {
		line = strings.TrimSpace(line)
		if line == "" || strings.HasPrefix(line, "-") ||
			strings.Contains(line, "Mail queue is empty") ||
			strings.Contains(line, "queue is empty") {
			continue
		}
		// Parse mailq output: ID SPACE SIZE SPACE DATE SPACE SENDER
		fields := strings.Fields(line)
		if len(fields) < 5 {
			continue
		}
		// Skip lines that are recipients (indented)
		if fields[0] != line || line[0] == ' ' {
			continue
		}
		item := MailQueueItem{
			ID:      fields[0],
			Size:    fields[1],
			Arrival: strings.Join(fields[2:len(fields)-1], " "),
			Sender:  fields[len(fields)-1],
		}
		items = append(items, item)
	}

	result, err := json.Marshal(items)
	if err != nil {
		return "", err
	}
	return string(result), nil
}

// QueueRetry forces delivery of a specific queue item or the entire queue.
func QueueRetry(queueID string) error {
	args := []string{"-f"}
	if queueID != "" {
		args = append(args, queueID)
	}
	out, err := exec.Command("postqueue", args...).CombinedOutput()
	if err != nil {
		return fmt.Errorf("postqueue retry: %s: %w", strings.TrimSpace(string(out)), err)
	}
	return nil
}

// QueueDelete removes a specific message from the Postfix queue.
func QueueDelete(queueID string) error {
	if queueID == "" {
		return fmt.Errorf("queue ID is required")
	}
	out, err := exec.Command("postsuper", "-d", queueID).CombinedOutput()
	if err != nil {
		return fmt.Errorf("postsuper delete: %s: %w", strings.TrimSpace(string(out)), err)
	}
	return nil
}

// LogTail returns the last N lines of the mail log.
func LogTail(lines int) (string, error) {
	if lines <= 0 {
		lines = 100
	}
	out, err := exec.Command("tail", "-n", fmt.Sprintf("%d", lines), "/var/log/mail.log").Output()
	if err != nil {
		return "", fmt.Errorf("tail mail.log: %w", err)
	}
	return string(out), nil
}
