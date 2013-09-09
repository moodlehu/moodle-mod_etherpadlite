<?php  // $Id: view.php,v 1.6.2.3 2009/04/17 22:06:25 skodak Exp $

/**
 * This page prints a particular instance of etherpadlite
 *
 * @package    mod
 * @subpackage etherpadlite
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
    // Doesn't it ahve to be array('id' => $a)?
    $etherpadlite = $DB->get_record('etherpadlite', array('id' => $n), '*', MUST_EXIST);
    $course       = $DB->get_record('course', array('id' => $etherpadlite->course), '*', MUST_EXIST);
    $cm           = get_coursemodule_from_instance('etherpadlite', $etherpadlite->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

// This must be here, so that require login doesn't throw a warning
$PAGE->set_url('/mod/etherpadlite/view.php', array('id' => $cm->id));
require_login($course, true, $cm);
add_to_log($course->id, 'etherpadlite', 'view', "view.php?id=$cm->id", $etherpadlite->name, $cm->id);

if($CFG->etherpadlite_ssl) {
	// https_required doesn't work, if $CFG->loginhttps doesn't work
	$CFG->httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
	if (!isset($_SERVER['HTTPS'])) {
		$url = $CFG->httpswwwroot.'/mod/etherpadlite/view.php?id='.$id;

		redirect($url);
    }
}


// [START] Initialise the session for the Author
// php.ini separator.output auf '&' setzen
$separator = ini_get('arg_separator.output');
ini_set('arg_separator.output', '&');  

// set vars
$domain = $CFG->etherpadlite_url;
$padId = $etherpadlite->uri;
$fullurl = "domain.tld";

// make a new intance from the etherpadlite client
$instance = new EtherpadLiteClient($CFG->etherpadlite_apikey,$domain.'api');

// fullurl generation
if(isguestuser() && !etherpadlite_guestsallowed($etherpadlite)) {
	try {
		$readOnlyID = $instance->getReadOnlyID($padId);
		$readOnlyID = $readOnlyID->readOnlyID;
		$fullurl = $domain.'ro/'.$readOnlyID;
	}
	catch (Exception $e) {
		//echo "\n\ngetReadOnlyID failed with Message: ".$e->getMessage();
		throw $e;
	}
}
else {
	$fullurl = $domain.'p/'.$padId;
}

// get the groupID
$groupID = explode('$', $padId);
$groupID = $groupID[0];

// create author if not exists for logged in user (with first and lastname)
try {
	if(isguestuser() && etherpadlite_guestsallowed($etherpadlite)) {
		$author = $instance->createAuthor('Guest-'.etherpadlite_genRandomString());
	}
  else if(isset($USER->firstname, $USER->lastname)) {
  	$userName = $USER->firstname.' '.$USER->lastname;
  	$author = $instance->createAuthorIfNotExistsFor($USER->id, $userName);
  }
  else {
  	$author = $instance->createAuthorIfNotExistsFor($USER->id);
  }
  $authorID = $author->authorID;
//echo "The AuthorID is now $authorID\n\n";
} catch (Exception $e) {
  // the pad already exists or something else went wrong
  //echo "\n\ncreateAuthor Failed with message:  ". $e->getMessage();
  throw $e;
}

//$validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y")); // +1 day in the future
$validUntil = time() + $CFG->etherpadlite_cookietime;
try{
    $sessionID = $instance->createSession($groupID, $authorID, $validUntil);
}
catch (Exception $e) {
    //echo "\n\ncreateSession failed with message: ".$e->getMessage();
    throw $e;
}
$sessionID = $sessionID->sessionID;

// if we reach the etherpadlite server over https, then the cookie should only be delivered over ssl 
$ssl = (stripos($CFG->etherpadlite_url, 'https://')===0)?true:false;

setcookie("sessionID",$sessionID,$validUntil,'/',$CFG->etherpadlite_cookiedomain, $ssl); // Set a cookie 

// seperator.output wieder zur�cksetzen
ini_set('arg_separator.output', $separator);

// [END] Etherpad Lite init

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

/// Print the page header
$PAGE->set_title("Etherpad Lite: ".format_string($etherpadlite->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();

/// Print the main part of the page

$summary = format_module_intro('etherpadite', $etherpadlite, $cm->id);
if(isguestuser() && !etherpadlite_guestsallowed($etherpadlite)) {
	$summary.= "<br/><br/>".get_string('summaryguest','etherpadlite');
}
if(!empty($summary)) {
	echo $OUTPUT->box($summary, 'generalbox mod_introbox', 'etherpadliteintro');
}
echo '<iframe id="etherpadiframe" src ="'.$fullurl.'" width="100%", height="500px"></iframe>';
echo '<script type="text/javascript">
YUI().use(\'resize\', function(Y) {
    var resize = new Y.Resize({
        //Selector of the node to resize
        node: \'#etherpadiframe\',
        handles: \'br\'
    });
    resize.plug(Y.Plugin.ResizeConstrained, {
        minWidth: 380,
        minHeight: 140,
        maxWidth: 1080,
        maxHeight: 1080
    }); 
    
});
</script>
';

/// Finish the page
echo $OUTPUT->footer();
