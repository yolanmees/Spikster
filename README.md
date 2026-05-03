<img src="https://github.com/yolanmees/Spikster/blob/master/utility/design/banner.png?raw=true">

![GitHub stars](https://img.shields.io/github/stars/yolanmees/Spikster?style=social)
![GitHub watchers](https://img.shields.io/github/watchers/yolanmees/Spikster?style=social)
![GitHub issues](https://img.shields.io/github/issues/yolanmees/Spikster)
![GitHub](https://img.shields.io/github/license/yolanmees/Spikster)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/yolanmees/Spikster?label=version)

## About

Spikster is a Laravel based cloud server control panel that supports Digital Ocean, AWS, Vultr, Google Cloud, Linode, Azure and other VPS. It comes with nginx, Mysql, multi PHP-FPM versions, multi users, Supervisor, Composer, npm, free Let's Encrypt certificates, Git deployment, backups, ffmpeg, fail2ban, Redis, API and with a simple graphical interface useful to manage Laravel, Codeigniter, Symfony, WordPress or other PHP applications. With Spikster you don’t need to be a Sys Admin to deploy and manage websites and PHP applications powered by cloud VPS.

## Features

-   Easy install: setup one or more servers with a click in few minutes without be a Linux expert.

-   Server Management: manage one or more servers in as easy as a few clicks without be a LEMP Guru.

-   Perfect stack for PHP devs: Spikster comes with nginx, PHP, MySql, Bind9(DNS), Composer, npm and Supervisor.

-   Multi-PHP: Run simultaneous PHP versions at your ease & convenience.

-   Secure: no unsed open ports, unprivileged PHP, isolated system users and filesystem, only SFTP (no insecure FTP), Free SSL certificates everywhere.

-   Always update: Spikster takes care about your business and automatically keeps your server's software up to date so you always have the latest security patches.

-   Integrate Spikster with your own software via Rest API and Swagger.

-   Real-time servers stats: Keep an eye on everything through an awesome dashboard.

-   Easylly manage your Domains, DNS, Hosting, SSL, Databases, Logs, and more.

## Documentation

Documentation at: https://spikster.com/

## Installation

> [!IMPORTANT]
> Requires a fresh **Ubuntu 22.04 or 24.04** VPS with at least 1 vCPU, 1GB RAM and 10GB disk.
> Make sure ports **22, 80 and 443** are open before you start.

SSH into your server as root and run:

```bash
curl -fsSL https://raw.githubusercontent.com/yolanmees/Spikster/v2-update/install.sh | bash
```

That's it. The installer takes care of everything. When it finishes, you'll see:

```
Panel URL:   http://<your-server-ip>
Setup URL:   http://<your-server-ip>/setup/<token>
```

Open the **Setup URL** in your browser to create your admin account. Your server is already connected — no further configuration needed.

## Spikster LEMP environment

-   nginx: 1.18
-   PHP-FPM: 8.3, 8.2, 8.1, 8.0, 7.4
-   MySql: 8
-   node: 16
-   npm: 8
-   Composer: 2

## Screenshots

![](https://spikster.com/images/Spikster-server-overview.png)

![](https://spikster.com/images/Spikster-site-overview.png)

![](https://spikster.com/images/Spikster-wordpress.png)

## Why use Spikster?

Spikster is easy, stable, powerful and free for any personal and commercial use and it's a perfect alternative to Cpanel, Plesk, Runcloud, CyberPanel, DirectAdmin, Forge and similar software...

## Spikster Roadmap... what's next?

-   User roles and permissions
-   New install process
-   Apps installer
-   Backups
-   ...

## Join the community

Join our discord server: https://discord.gg/ne99uNEetG

## Contributing

Thank you for considering contributing to the Spikster project

#### ...anyway star this project on Github, Thank you ;)

## Licence

Spikster is open-sourced software licensed under the [MIT License](./LICENSE).

## Need support with Spikster?

Please open an issue here: https://github.com/yolanmees/Spikster/issues.

### ...enjoy Spikster :)
