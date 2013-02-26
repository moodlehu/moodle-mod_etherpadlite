# About

This is a module, which integrates etherpad-lite in Moodle 2.3 - 2.4

Features:

- Add / View / Delete Pads
- Users have the same name & writing color in all pads
- Moodle Import / Export support
- optional guest allowance
- It supports etherpad-lite servers, which can only be accessed through the API (access only through Moodle)
- It can check the HTTPS certificate of the ep-lite server for full security (man in the middle attacks)



## Prerequirement
You need an etherpad-lite server, which is running on at least the same 2nd-level-domain as your moodle server.

On the github page you'll find all information you need, to install the server: https://github.com/ether/etherpad-lite

We recommend to use not less than the etherpad-lite version 1.2.7

It's also recommended to use the latest stable release of nodejs
(http://nodejs.org/)

	we are using nodejs 0.6.12, installed over apt-get for our productive server. But we test new ep-lite versions always with this node version, before updating productive

When you want, that the server is only accessible via Moodle, then i recommend to install ep_remove_embed over the ep-lite admin interface. This removes the embed link.<br>
*To access the admin area, uncomment the user section in settings.json*

### Working ep-lite installation
- Ubuntu 12.04
- apt-get nodejs, npm, git, nginx, abiword, make, g++
- etherpad-lite master branch from git (v1.2.7)
- ep-lite settings.json:
	-	"requireSession":true
	-	"editOnly":true
	-	"abiword": "/usr/bin/abiword"
- ep-lite plugin: *ep_remove_embed* via admin interface
- upstart script
- logrotate
- nginx as reverse proxy with https

# INSTALLATION

1. Copy this repository to the moodle subfolder: **mod/etherpadlite**

2. open your admin/index.php page and follow the instructions

# Configuration
1. Server Url from your etherpadlite server
2. ApiKey: this is stored in the file: APIKEY.txt on your etherpadlite server
3. Padname: this is optional and maybe just for debugging the databse
4. Cookie Domain: Enter the domain as described
5. Session elapse time: How long should one session be valid?
6. Https Redirect: This redirects moodle to https, so that the user feels secure <br>(later this should be used to delete sessions on the etherpadlite server)
7. Verify HTTPS cert: This lets curl check, if the https cert of the etherpadlite server is valid, to prevent man in the middle attacks
8. Guests allowed to write?: As described

*tested with Moodle 2.3 & 2.4*