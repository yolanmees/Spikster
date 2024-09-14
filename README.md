<img src="https://github.com/yolanmees/Spikster/blob/master/utility/design/banner.png?raw=true">

![GitHub stars](https://img.shields.io/github/stars/yolanmees/Spikster?style=social)
![GitHub watchers](https://img.shields.io/github/watchers/yolanmees/Spikster?style=social)
![GitHub issues](https://img.shields.io/github/issues/yolanmees/Spikster)
![GitHub](https://img.shields.io/github/license/yolanmees/Spikster)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/yolanmees/Spikster?label=version)

## About
Spikster is a Laravel based cloud server control panel that supports Digital Ocean, AWS, Vultr, Google Cloud, Linode, Azure and other VPS. It comes with nginx, Mysql, multi PHP-FPM versions, multi users, Supervisor, Composer, npm, free Let's Encrypt certificates, Git deployment, backups, ffmpeg, fail2ban, Redis, API and with a simple graphical interface useful to manage Laravel, Codeigniter, Symfony, WordPress or other PHP applications. With Spikster you don’t need to be a Sys Admin to deploy and manage websites and PHP applications powered by cloud VPS.

## Features
- Easy install: setup one or more servers with a click in few minutes without be a Linux expert.

- Server Management: manage one or more servers in as easy as a few clicks without be a LEMP Guru.

- Perfect stack for PHP devs: Spikster comes with nginx, PHP, MySql, Bind9(DNS), Composer, npm and Supervisor.

- Multi-PHP: Run simultaneous PHP versions at your ease & convenience.

- Secure: no unsed open ports, unprivileged PHP, isolated system users and filesystem, only SFTP (no insecure FTP), Free SSL certificates everywhere.

- Always update: Spikster takes care about your business and automatically keeps your server's software up to date so you always have the latest security patches.

- Integrate Spikster with your own software via Rest API and Swagger.

- Real-time servers stats: Keep an eye on everything through an awesome dashboard.

- Easylly manage your Domains, DNS, Hosting, SSL, Databases, Logs, and more.


## Documentation
Documentation at: https://spikster.com/

## Installation

Install Spikster

```
ssh ubuntu@<your server IP address>
sudo -s
wget -O - https://raw.githubusercontent.com/yolanmees/Spikster/master/go.sh | bash
```
Remember to open ports: 22, 80 and 443!

#### Installation Note
Before you can use Spikster, please make sure your server fulfils these requirements:

- Ubuntu version:
  - 22.04 x86_64 LTS (Recommended)
  - 23.04 x86_64
  - 24.04 x86_64 LTS
- 10GB free disk space

Hardware Requirement: At least 1 core processor / 512MB minimum RAM / At least 1 public IP  Address (IPv6 and NAT VPS are not supported) / For VPS providers such as AWS, those providers already include an external firewall for your VPS. 

Installation may take up to about 30 minutes which may also depend on your server's internet speed. After the installation is completed, you are ready to use Spikster to manage your servers.

> [!IMPORTANT]
> At the end of the installation you are given all the credentials you need for spikster / mysql.
> 
> The default login for the spikster dashboard is administrator@localhost / password

To correctly manage remote servers Spikster has to be on a public IP address (IPv4). Do not use it in localhost!

## Spikster LEMP environment
- nginx: 1.18
- PHP-FPM: 8.2, 8.1, 8.0, 7.4
- MySql: 8
- node: 16
- npm: 8
- Composer: 2

## Screenshots

<img src="https://spikster.com/images/docs/server.png"> 

<img src="https://spikster.com/images/docs/site.png"> 

<img src="https://spikster.com/images/docs/cron.png"> 

## Why use Spikster?
Spikster is easy, stable, powerful and free for any personal and commercial use and it's a perfect alternative to Cpanel, Plesk, Runcloud, CyberPanel, DirectAdmin, Forge and similar software...

## Spikster Roadmap... what's next? 
- User roles and permissions
- New install process
- Apps installer
- Backups
- ...

## Join the community
Join our discord server: https://discord.gg/ne99uNEetG


## Contributing
Thank you for considering contributing to the Spikster project

#### ...anyway star this project on Github, Thank you ;)

## Licence
Spikster is licensed under the Creative Commons Attribution-NonCommercial 4.0 International License. See the [LICENSE](./LICENSE) file for details.


## Need support with Spikster?
Please open an issue here: https://github.com/yolanmees/Spikster/issues.

### ...enjoy Spikster :)
