<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file holds the english language.
 *
 * @package    mod_etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['activityclose']          = 'Allow editing to';
$string['activityopen']           = 'Allow editing from';
$string['activityopenclose']      = 'Allow editing from/to';
$string['activityopenclose_help'] = 'If activated, students can edit the etherpad only in the specified period.';
$string['adminguests']            = 'Guests allowed to write?';
$string['adminguestsdesc']        = 'With this set, users who are allowed to configure a specific etherpadlite module can allow guests to write in this specific etherpadlite module';
$string['apikey']                 = 'API Key';
$string['apikeydesc']             = 'This is the API Key which this module needs to communicate with your etherpadlite server. This API key is stored on your etherpadliste server and can be copied from there.';
$string['apisettingsheading'] = 'Api settings';
$string['apisettingsheading_desc'] = 'Configure the Etherpad Lite instance used';
$string['apiversion']             = 'API version';
$string['apiversiondesc']         = 'The default version is "1.2". You only should use version "1.1" if you use a very old version of etherpad lite server!';
$string['checkssl']               = 'Verify HTTPS cert';
$string['checkssldesc']           = 'With this set, the HTTPS certificate of the etherpadlite server will be checked, to prevent man in the middle attacks';
$string['connected']              = 'Connected';
$string['connecttimeout']         = 'Connect Timeout';
$string['connecttimeoutdesc']     = 'Connect timeout is the maximum amount of time in seconds allowed to connect to the server. It can be set to 0 to disable this limit, but this is not advisable in a production environment.<br>
See also: <a href="https://curl.se/libcurl/c/CURLOPT_CONNECTTIMEOUT.html" target="blank">CURLOPT_CONNECTTIMEOUT</a>';
$string['cookiedomain']                          = 'Cookie Domain';
$string['cookiedomaindesc']                      = 'Here you can enter the domain, which should be stored in the session cookie, so that the etherpadlite server recognize it. When moodle runs on the domain moodle.example.com and your etherpadlite server on etherpadlite.example.com, then your cookie domain should be .example.com.';
$string['cookietime']                            = 'Session elapse time';
$string['cookietimedesc']                        = 'Here you have to enter the time (in seconds) until the etherpadlite session should be valid';
$string['copylink']                              = 'Copy pad link';
$string['copylinkdesc']                          = 'Add a button to the module navigation settings, which allows teachers and managers to easily get the full link for the current pad.';
$string['deletein24hours']                       = '24 hours';
$string['deleteinonehour']                       = '1 hour';
$string['deleteintwelvehours']                   = '12 hours';
$string['deletemgroupads']                       = 'Delete moodle group pads in';
$string['deletemgroupadsdesc']                   = 'Delete all group pads created in association with moodle group mode';
$string['deletenow']                             = 'Immediately';
$string['donotdelete']                           = 'Do not delete';
$string['error_config_has_no_api_key']           = 'The api key is not configured yet! Please check your configuration!';
$string['error_config_has_no_valid_baseurl']     = 'The configured server url is not valid! Please check your configuration!';
$string['error_could_not_get_api_version']       = 'The Moodle server could not get the api version! Your Moodle server may not be able to connect to the Etherpad server. Please check your configuration!';
$string['error_invalid_api_key']                 = 'The api key is not valid! Please check your configuration!';
$string['error_wrong_api_version']               = 'The api version is not compatible! Please check the version of your Etherpad server!';
$string['etherpadlite']                          = 'Etherpad Lite';
$string['etherpadlite:addinstance']              = 'Add new pad';
$string['etherpadlite_link_copied_to_clipboard'] = 'The link of this etherpad was copied to your clipboard.';
$string['etherpadliteintro']                     = 'Etherpadlite Intro';
$string['etherpadlitename']                      = 'Etherpadlite Name';
$string['fullscreen']                            = 'Fullscreen';
$string['guestsallowed']                         = 'Guests allowed to write?';
$string['guestsallowed_help']                    = 'This determines if guests are allowed to write in this pad. If not, they will be only able to read the content of the pad.';
$string['ignoresecurity']                        = 'Ignore security';
$string['ignoresecuritydesc']                    = 'If you activate this the url will be allowed despite the settings for "cURL blocked hosts list" (see: curlsecurityblockedhosts).';
$string['link_copied']                           = 'Link copied!';
$string['minwidth']                              = 'Minimum width';
$string['minwidthdesc']                          = 'If you don\'t want a very small etherpad on small displays like cell phones, you can set a minimum width that will keep the pad.';
$string['modulename']                            = 'Etherpad Lite';
$string['modulename_help']                       = 'The Etherpad Lite module enables students and teachers to write text in a collaborative way. The text is synced automatically as they type.

(The etherpadlite server, which stands behind this module, is still in beta stage. That\'s why problems might occur in rare circumstances, but aren\'t expected)';
$string['modulenameplural']                      = 'Etherpad Lites';
$string['not_connected']        = 'Not connected';
$string['padname']              = 'Padname for all instances';
$string['padnamedesc']          = 'A general padname can be helpful, if you want to find all pads from this Moodle installation on your etherpadlite server. Pad groups are generated autmatically.';
$string['pluginadministration'] = 'Etherpad Lite administration';
$string['pluginname']           = 'Etherpad Lite';
$string['privacy:metadata']     = 'Ethepadlite plugin does not store any personal data.';
$string['resetting_data']       = 'Reset Etherpad Lite data';
$string['restorewindowsize']    = 'Restore window size';
$string['ssl']          = 'HTTPS Redirect';
$string['ssldesc']      = 'With this set, your site will redirect itself to HTTPS, if an etherpadlite is opened (eye candy for the user)';
$string['summaryguest'] = 'You are logged in as guest. That\'s why you can only see the readonly version of this Pad. Reload the page to get new changes.';
$string['testconnectionfail'] = 'The connection with the Etherpad Lite instance failed.';
$string['testconnectionsuccess'] = 'The connection to the Etherpad Lite instance was successful.';
$string['testmodaltitle'] = 'Etherpad Lite Connection Test';
$string['testtoolbutton'] = 'Connection Test Tool';
$string['testtooldisabledbuttontitle'] = 'Unable to conduct the connection test due to unloaded js modules.';
$string['testtoolheader'] = 'Connection test tool';
$string['testtoolheaderdesc'] = 'To test the current Etherpad Lite URL use: {$a}';
$string['timeout']              = 'Timeout';
$string['timeoutdesc']          = 'Timeout is the total time in seconds that we wait for a response to a given request, including the time it takes to establish the connection and the time it takes for the server to respond. It can be set to 0 to disable this limit, but this is not advisable in a production environment.<br>
See also: <a href="https://curl.se/libcurl/c/CURLOPT_TIMEOUT.html" target="blank">CURLOPT_TIMEOUT</a>';
$string['url']          = 'Server URL';
$string['urldesc']      = 'This is the URL to your Etherpadlite server in the form: http[s]://host[:port]/[subDir/]';
$string['urlisblocked'] = 'The current host "{$a}" is blocked';
$string['urlisblocked_but_ignored'] = 'The current host "{$a}" is blocked but "ignoresecurity" is activated.';
$string['urlnotset']    = 'The etherpad server url is not configured for this site';
