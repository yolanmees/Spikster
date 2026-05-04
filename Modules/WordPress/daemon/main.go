package main

import (
	"fmt"

	modulesdk "github.com/yolanmees/spikster/daemon/internal/modulehost/sdk"
	"github.com/yolanmees/spikster/daemon/internal/wordpress"
)

func main() {
	req, err := modulesdk.ReadRequest()
	if err != nil {
		modulesdk.WriteResponse(false, "", "failed to read request: "+err.Error())
		return
	}

	switch req.Action {

	case "wordpress.install-files":
		p := req.Params
		params := wordpress.InstallParams{
			Username:   p["username"],
			Path:       p["path"],
			DBName:     p["db_name"],
			DBUser:     p["db_user"],
			DBPassword: p["db_password"],
		}
		if err := wordpress.InstallFiles(params); err != nil {
			modulesdk.WriteResponse(false, "", err.Error())
			return
		}
		modulesdk.WriteResponse(true, "wordpress files installed", "")

	case "wordpress.core-install":
		p := req.Params
		params := wordpress.CoreInstallParams{
			Username:   p["username"],
			Path:       p["path"],
			URL:        p["url"],
			Title:      p["title"],
			AdminUser:  p["admin_user"],
			AdminPass:  p["admin_pass"],
			AdminEmail: p["admin_email"],
			Locale:     p["locale"],
		}
		if err := wordpress.CoreInstall(params); err != nil {
			modulesdk.WriteResponse(false, "", err.Error())
			return
		}
		modulesdk.WriteResponse(true, "wordpress installed", "")

	case "wordpress.cli":
		out, err := wordpress.ExecCLI(req.Params["username"], req.Params["path"], req.Params["command"])
		if err != nil {
			modulesdk.WriteResponse(false, out, err.Error())
			return
		}
		modulesdk.WriteResponse(true, out, "")

	case "wordpress.uninstall-files":
		if err := wordpress.UninstallFiles(req.Params["path"]); err != nil {
			modulesdk.WriteResponse(false, "", err.Error())
			return
		}
		modulesdk.WriteResponse(true, "wordpress files removed", "")

	default:
		modulesdk.WriteResponse(false, "", fmt.Sprintf("unknown action: %s", req.Action))
	}
}
