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
 * These are the settings for this module
 *
 * @package    mod_etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$settings->add(new admin_setting_configtext('etherpadlite/url', get_string('url', 'etherpadlite'),
				   get_string('urldesc', 'etherpadlite'), 'https://myserver.mydomain.local/moodle/', PARAM_RAW,40));

$settings->add(new admin_setting_configtext('etherpadlite/apikey', get_string('apikey', 'etherpadlite'),
		           get_string('apikeydesc', 'etherpadlite'), 'Enter your API Key', PARAM_RAW,40));

$settings->add(new admin_setting_configtext('etherpadlite/padname', get_string('padname', 'etherpadlite'),
				   get_string('padnamedesc', 'etherpadlite'), 'mymoodle2'));

$settings->add(new admin_setting_configtext('etherpadlite/cookiedomain', get_string('cookiedomain', 'etherpadlite'),
				   get_string('cookiedomaindesc', 'etherpadlite'), '.mydomain.local'));
$settings->add(new admin_setting_configtext('etherpadlite/cookietime', get_string('cookietime', 'etherpadlite'),
				   get_string('cookietimedesc', 'etherpadlite'), '10800'));

$settings->add(new admin_setting_configcheckbox('etherpadlite/ssl', get_string('ssl', 'etherpadlite'),
				   get_string('ssldesc', 'etherpadlite'), '0'));
$settings->add(new admin_setting_configcheckbox('etherpadlite/check_ssl', get_string('checkssl', 'etherpadlite'),
                   get_string('checkssldesc', 'etherpadlite'), 0));

$settings->add(new admin_setting_configcheckbox('etherpadlite/adminguests', get_string('adminguests', 'etherpadlite'),
				   get_string('adminguestsdesc', 'etherpadlite'), '0'));

?>