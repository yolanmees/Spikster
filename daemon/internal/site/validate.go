package site

import (
	"fmt"
	"regexp"
)

// reUsername allows only lowercase letters, digits and underscores, max 32 chars.
// MySQL usernames are max 32 chars; Linux usernames max 32 chars.
var reUsername = regexp.MustCompile(`^[a-z][a-z0-9_]{1,31}$`)

// reDBName allows letters, digits and underscores, max 64 chars (MySQL limit).
var reDBName = regexp.MustCompile(`^[a-zA-Z][a-zA-Z0-9_]{1,63}$`)

// reDomain validates a standard domain name (per RFC 1035).
var reDomain = regexp.MustCompile(`^(?i)[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$`)

// ValidateSite checks all user-controlled fields before any shell/SQL operation.
func ValidateSite(s Site) error {
	if !reUsername.MatchString(s.Username) {
		return fmt.Errorf("invalid username %q: must be 2-32 chars, lowercase, start with letter, only [a-z0-9_]", s.Username)
	}
	if s.DBName != "" && !reDBName.MatchString(s.DBName) {
		return fmt.Errorf("invalid db name %q: must be 2-64 chars, start with letter, only [a-zA-Z0-9_]", s.DBName)
	}
	if s.Domain != "" && !reDomain.MatchString(s.Domain) {
		return fmt.Errorf("invalid domain %q", s.Domain)
	}
	return nil
}
