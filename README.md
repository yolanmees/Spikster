<img src="https://github.com/yolanmees/Spikster/blob/master/utility/design/banner.png?raw=true">

<p align="center">
  <a href="https://github.com/yolanmees/Spikster/stargazers"><img src="https://img.shields.io/github/stars/yolanmees/Spikster?style=social" alt="GitHub Stars"></a>
  <a href="https://github.com/yolanmees/Spikster/watchers"><img src="https://img.shields.io/github/watchers/yolanmees/Spikster?style=social" alt="GitHub Watchers"></a>
  <a href="https://github.com/yolanmees/Spikster/issues"><img src="https://img.shields.io/github/issues/yolanmees/Spikster" alt="GitHub Issues"></a>
  <a href="./LICENSE"><img src="https://img.shields.io/github/license/yolanmees/Spikster" alt="License"></a>
  <a href="https://github.com/yolanmees/Spikster/releases"><img src="https://img.shields.io/github/v/tag/yolanmees/Spikster?label=version" alt="Version"></a>
</p>

<h3 align="center">Open-source VPS control panel for PHP developers.<br>Deploy and manage servers without being a sysadmin.</h3>

---

## What is Spikster?

Spikster is a **self-hosted server control panel** built with Laravel. It gives you a clean web UI to manage VPS servers on DigitalOcean, AWS, Vultr, Linode, Hetzner, Google Cloud, Azure and more — without touching the command line after setup.

It handles everything a PHP developer needs: sites, databases, SSL, queues, DNS, deployments, and real-time server monitoring — all in one place.

> **Free. Open-source. No monthly fees.**  
> A powerful alternative to Forge, RunCloud, Ploi, CyberPanel, and cPanel.

---

## Features

- **One-command install** — full LEMP stack up and running in minutes
- **Multi-PHP support** — run PHP 8.1, 8.2, 8.3, 8.4 side by side
- **Site management** — create, configure and deploy PHP/Laravel/WordPress sites
- **Free SSL** — automatic Let's Encrypt certificates for all your domains
- **Database management** — MySQL databases and users via UI
- **DNS management** — manage domains and DNS records
- **Real-time monitoring** — live CPU, RAM, disk and load stats
- **Queue worker management** — Supervisor-backed queue workers
- **Git deployment** — deploy from GitHub/GitLab with a single click
- **Security built-in** — fail2ban, isolated system users, no open ports
- **REST API + Swagger** — integrate Spikster with your own tools
- **Roles & permissions** — multi-user access control
- **Backups** — scheduled backups to remote storage
- **WebSocket support** — Laravel Reverb ready out of the box

---

## One-command Install

> [!IMPORTANT]
> Requires a fresh **Ubuntu 22.04, 24.04 or 25.10** VPS with at least 1 vCPU, 1GB RAM and 10GB disk.
> Make sure ports **22, 80 and 443** are open.

SSH into your server as root and run:

```bash
curl -fsSL https://raw.githubusercontent.com/yolanmees/Spikster/v2-update/install.sh | bash
```

That's it. The installer handles nginx, PHP, MySQL, Redis, SSL, services — everything. When done, you'll get:

```
Panel URL:   http://<your-server-ip>
Setup URL:   http://<your-server-ip>/setup/<token>
```

Open the **Setup URL** to create your admin account. Your server is already connected.

---

## Stack

| Component | Version |
|-----------|---------|
| nginx | 1.18+ |
| PHP-FPM | 8.1, 8.2, 8.3, 8.4 |
| MySQL | 8.x / 9.x |
| Redis | 7+ |
| Composer | 2 |
| Node / npm | 18 / 9 |

---

## Screenshots

![Server Overview](https://spikster.com/images/Spikster-server-overview.png)

![Site Management](https://spikster.com/images/Spikster-site-overview.png)

![WordPress Support](https://spikster.com/images/Spikster-wordpress.png)

---

## Why Spikster?

Most control panels are either expensive (Forge, RunCloud, Ploi charge monthly), bloated (cPanel, Plesk), or outdated. Spikster is **free, modern, and built by developers for developers** — with a clean UI and a codebase you can actually read and extend.

- ✅ No subscription fees
- ✅ Self-hosted — your servers, your data
- ✅ Built on Laravel — easy to customize
- ✅ Actively developed

---

## Roadmap

- [x] Roles & permissions
- [x] Automated one-command installer
- [x] Real-time server monitoring
- [x] WebSocket support (Reverb)
- [ ] App marketplace (WordPress, n8n, etc.)
- [ ] Backup to S3 / Cloudflare R2
- [ ] Mobile-friendly UI

---

## Contributing

PRs and issues are welcome. If you find Spikster useful, **drop a ⭐ on GitHub** — it helps more people find the project.

- [Open an issue](https://github.com/yolanmees/Spikster/issues)
- [Join the Discord](https://discord.gg/ne99uNEetG)

---

## Documentation

Full docs at [spikster.com](https://spikster.com/)

---

## License

Spikster is open-source software licensed under the [MIT License](./LICENSE).
