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
 * This page prints a particular instance of etherpadlite
 *
 * @package    mod_etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // etherpadlite instance ID

if ($id) {
    $cm           = get_coursemodule_from_id('etherpadlite', $id, 0, false, MUST_EXIST);
    $course       = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $etherpadlite = $DB->get_record('etherpadlite', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($a) {
    $etherpadlite = $DB->get_record('etherpadlite', array('id' => $a), '*', MUST_EXIST);
    $course       = $DB->get_record('course', array('id' => $etherpadlite->course), '*', MUST_EXIST);
    $cm           = get_coursemodule_from_instance('etherpadlite', $etherpadlite->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

// This must be here, so that require login doesn't throw a warning.
$PAGE->set_url('/mod/etherpadlite/view.php', array('id' => $cm->id));
require_login($course, true, $cm);
$config = get_config("etherpadlite");

if($config->ssl) {
	// The https_required doesn't work, if $CFG->loginhttps doesn't work.
	$CFG->httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
	if (!isset($_SERVER['HTTPS'])) {
		$url = $CFG->httpswwwroot.'/mod/etherpadlite/view.php?id='.$id;

		redirect($url);
    }
}


// [START] Initialise the session for the Author.
// Set vars.
$domain = $config->url;
$padId = $etherpadlite->uri;
$fullurl = "domain.tld";

// Make a new intance from the etherpadlite client.
$instance = new EtherpadLiteClient($config->apikey,$domain.'api');

// Fullurl generation.
if(isguestuser() && !etherpadlite_guestsallowed($etherpadlite)) {
	try {
		$readOnlyID = $instance->getReadOnlyID($padId);
		$readOnlyID = $readOnlyID->readOnlyID;
		$fullurl = $domain.'ro/'.$readOnlyID;
	}
	catch (Exception $e) {
		throw $e;
	}
}
else {
	$fullurl = $domain.'p/'.$padId;
}

// Get the groupID
$groupID = explode('$', $padId);
$groupID = $groupID[0];

// Create author if not exists for logged in user (with full name as it is obtained from Moodle core library).
try {
    if(isguestuser() && etherpadlite_guestsallowed($etherpadlite)) {
        $author = $instance->createAuthor('Guest-'.etherpadlite_genRandomString());
    }
    else {
        $author = $instance->createAuthorIfNotExistsFor($USER->id, fullname($USER));
    }
    $authorID = $author->authorID;
} catch (Exception $e) {
    // The pad already exists or something else went wrong.
    throw $e;
}

$validUntil = time() + $config->cookietime;
try{
    $sessionID = $instance->createSession($groupID, $authorID, $validUntil);
}
catch (Exception $e) {
    throw $e;
}
$sessionID = $sessionID->sessionID;

// If we reach the etherpadlite server over https, then the cookie should only be delivered over ssl.
$ssl = (stripos($config->url, 'https://')===0)?true:false;

setcookie("sessionID",$sessionID,$validUntil,'/',$config->cookiedomain, $ssl); // Set a cookie

// [END] Etherpad Lite init.

$context = context_module::instance($cm->id);

// Display the etherpadlite and possibly results.
$eventparams = array(
    'context' => $context,
    'objectid' => $etherpadlite->id
);
$event = \mod_etherpadlite\event\course_module_viewed::create($eventparams);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('etherpadlite', $etherpadlite);
$event->trigger();

$PAGE->set_title(get_string('modulename', 'mod_etherpadlite').': '.format_string($etherpadlite->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$renderer = $PAGE->get_renderer('mod_etherpadlite');

// Print the page header.
echo $renderer->header();

// Print the etherpad content.
echo $renderer->render_etherpad($etherpadlite, $cm, $fullurl);

// Close the page.
echo $renderer->footer();
