package modulehost

// This file documents the whitelisted "module API" actions that module handlers
// can call back into the daemon via the unix socket. The actual dispatch cases
// are injected into socket.go's dispatch() function.
//
// Supported actions (handled in socket.go):
//
//   module.exec       params: username, command   → site.ExecCommand(username, command)
//   module.file.read  params: path                → reads file, returns content (max 10MB)
//   module.file.write params: path, content       → writes file (path must be under /home/)
//   module.file.chown params: path, owner, group  → server.ChangeOwnership(path, owner, group)
//   module.mysql.query params: db_root, sql       → runs read-only SELECT query
//   module.nginx.reload  no params               → reloads nginx
//   module.cron.read  no params                  → cron.Read()
//   module.cron.write params: content            → cron.Write(content)
