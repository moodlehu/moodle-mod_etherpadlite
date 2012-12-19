# About

This is a etherpadlite-module for Moodle 2.3 - 2.4


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

<br>
*tested with Moodle 2.3 & 2.4*