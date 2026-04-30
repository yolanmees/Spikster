package installer

import (
	"fmt"
	"os/exec"
)

func runCmd(args []string) error {
	cmd := exec.Command(args[0], args[1:]...)
	out, err := cmd.CombinedOutput()
	if err != nil {
		return fmt.Errorf("%s: %s", args[0], string(out))
	}
	return nil
}

func runCmds(cmds [][]string) error {
	for _, c := range cmds {
		if err := runCmd(c); err != nil {
			return err
		}
	}
	return nil
}
