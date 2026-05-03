package email

import (
	"fmt"
	"os"
	"os/exec"
	"strings"
)

// SpamFilterParams configures spam/phishing filter settings.
type SpamFilterParams struct {
	Domain          string
	SpamAction      string // "tag", "reject", "discard"
	SpamScore       string // score threshold (e.g. "5.0")
	VirusAction     string // "reject", "discard"
	RejectSpam      bool
	RejectPhishing  bool
	EnableDkimCheck bool
	EnableSpfCheck  bool
	EnableDmarcCheck bool
}

// SetFilters configures spam and virus filtering via Amavis/SpamAssassin.
func SetFilters(p SpamFilterParams) error {
	// Write custom Amavis policy for this domain
	confDir := "/etc/amavis/conf.d"
	os.MkdirAll(confDir, 0755)

	confPath := fmt.Sprintf("%s/50-spikster-%s", confDir, p.Domain)

	var sb strings.Builder
	sb.WriteString(fmt.Sprintf("# Spikster spam filter config for %s\n\n", p.Domain))

	if p.SpamScore != "" {
		sb.WriteString(fmt.Sprintf("$sa_tag2_level_deflt = %s;\n", p.SpamScore))
		sb.WriteString(fmt.Sprintf("$sa_kill_level_deflt = %s;\n", p.SpamScore))
	}

	switch p.SpamAction {
	case "reject":
		sb.WriteString("$final_spam_destiny = D_REJECT;\n")
	case "discard":
		sb.WriteString("$final_spam_destiny = D_DISCARD;\n")
	default:
		sb.WriteString("$final_spam_destiny = D_PASS;\n")
	}

	switch p.VirusAction {
	case "discard":
		sb.WriteString("$final_virus_destiny = D_DISCARD;\n")
	default:
		sb.WriteString("$final_virus_destiny = D_REJECT;\n")
	}

	// Banned attachment rules
	if p.RejectPhishing {
		sb.WriteString("@banned_filename_maps = (1);\n")
	}

	if err := os.WriteFile(confPath, []byte(sb.String()), 0644); err != nil {
		return fmt.Errorf("write amavis config: %w", err)
	}

	// Reload amavis
	if out, err := exec.Command("systemctl", "reload", "amavis").CombinedOutput(); err != nil {
		return fmt.Errorf("reload amavis: %s: %w", strings.TrimSpace(string(out)), err)
	}

	return nil
}
