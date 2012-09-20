/**
 * This is a javascript file, for the module 'etherpalite', which stores a cookie for an external etherpalite server
 *
 * @package      blocks
 * @subpackage   course_overview_sem
 * 
 * @author       Timo Welde <tjwelde@gmail.com>
 * @copyright    Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license      http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.mod_etherpadlite = {};

M.mod_etherpadlite.init_cookie = function(Y, sessionid, validuntil, domain) {

	expires = new Date();
	expires.setTime(expires.getTime() + (validuntil*100));

	Y.Cookie.set("sessionID", sessionid, {
	    expires: expires,
	    path: "/",
	    domain: domain
	});
}