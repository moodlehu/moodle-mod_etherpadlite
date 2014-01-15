<?php  // $Id: lib.php,v 1.7.2.5 2009/04/22 21:30:57 skodak Exp $

/**
 * Library of functions and constants for module etherpadlite
 * This file should have two well differenced parts:
 *   - All the core Moodle functions, neeeded to allow
 *     the module to work integrated in Moodle.
 *   - All the etherpadlite specific functions, needed
 *     to implement all the module logic. Please, note
 *     that, if the module become complex and this lib
 *     grows a lot, it's HIGHLY recommended to move all
 *     these module specific functions to a new php file,
 *     called "locallib.php" (see forum, quiz...). This will
 *     help to save some memory when Moodle is performing
 *     actions across all modules.
 *
 * @package    mod
 * @subpackage etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

include 'etherpad-lite-client.php';

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $etherpadlite An object from the form in mod_form.php
 * @return int The id of the newly inserted etherpadlite record
 */
function etherpadlite_add_instance(stdClass $etherpadlite, mod_etherpadlite_mod_form $mform = null) {

	global $DB;
	$config = get_config("etherpadlite");
	// php.ini separator.output auf '&' setzen
	$separator = ini_get('arg_separator.output');
    ini_set('arg_separator.output', '&');

	$instance = new EtherpadLiteClient($config->apikey,$config->url.'api');

	try {
		$createGroup = $instance->createGroup();
		$groupID = $createGroup->groupID;
		//echo "New GroupID is $groupID\n\n";
	} catch (Exception $e) {
		// the group already exists or something else went wrong
	    //echo "\n\ncreateGroup Failed with message ". $e->getMessage();
  		throw $e;
	}

	try {
		$newpad = $instance->createGroupPad($groupID, $config->padname);
		$padID = $newpad->padID;
	  	//echo "Created new pad with padID: $padID\n\n";
	} catch (Exception $e) {
  		// the pad already exists or something else went wrong
  		//echo "\n\ncreateGroupPad Failed with message ". $e->getMessage();
  		throw $e;
	}

	$etherpadlite->uri = $padID;

	$etherpadlite->timecreated = time();

	// seperator.output wieder zur�cksetzen
	ini_set('arg_separator.output', $separator);

    return $DB->insert_record('etherpadlite', $etherpadlite);
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $etherpadlite An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function etherpadlite_update_instance(stdClass $etherpadlite, mod_etherpadlite_mod_form $mform = null) {
	global $DB;

    $etherpadlite->timemodified = time();
    $etherpadlite->id = $etherpadlite->instance;

    # You may have to add extra stuff in here #
    if(empty($etherpadlite->guestsallowed)) {
    	$etherpadlite->guestsallowed = 0;
    }

    return $DB->update_record('etherpadlite', $etherpadlite);
}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function etherpadlite_delete_instance($id) {

	global $DB;

    if (! $etherpadlite = $DB->get_record('etherpadlite', array('id'=>$id))) {
        return false;
    }

    $result = true;

    # Delete any dependent records here #
	// php.ini separator.output auf '&' setzen
	$separator = ini_get('arg_separator.output');
    ini_set('arg_separator.output', '&');

    $config = get_config("etherpadlite");
	$instance = new EtherpadLiteClient($config->apikey,$config->url.'api');

	$padID = $etherpadlite->uri;
	$groupID = explode('$', $padID);
	$groupID = $groupID[0];

	try {
  		$instance->deleteGroup($groupID);
	} catch (Exception $e) {
 		echo "\n\ndeleteGroupFailed: ". $e->getMessage();
 		return false;
	}

	// seperator.output wieder zur�cksetzen
	ini_set('arg_separator.output', $separator);

    if (! $DB->delete_records('etherpadlite', array('id'=>$etherpadlite->id))) {
        $result = false;
    }

    return $result;
}


/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
function etherpadlite_user_outline($course, $user, $mod, $etherpadlite) {
    return $return;
}


/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function etherpadlite_user_complete($course, $user, $mod, $etherpadlite) {
    return true;
}


/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in etherpadlite activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function etherpadlite_print_recent_activity($course, $isteacher, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}


/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function etherpadlite_cron () {
    return true;
}


/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of etherpadlite. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $etherpadliteid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function etherpadlite_get_participants($etherpadliteid) {
    return false;
}


/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function etherpadlite_install() {
    return true;
}


/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function etherpadlite_uninstall() {
    return true;
}

/**
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function etherpadlite_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_COMPLETION_HAS_RULES:    return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}


//////////////////////////////////////////////////////////////////////////////////////
/// Any other etherpadlite functions go here.  Each of them must have a name that
/// starts with etherpadlite_
/// Remember (see note in first lines) that, if this section grows, it's HIGHLY
/// recommended to move all funcions below to a new "localib.php" file.
function etherpadlite_genRandomString() { // A funtion to generate a random name if something doesn't already exist
        $length = 5;
        $characters = "0123456789";
        $string = "";
        for ($p = 0; $p < $length; $p++) {
          $string .= $characters[mt_rand(0, strlen($characters))];
        }
        return $string;
}

function etherpadlite_guestsallowed($e) {
	global $CFG;

	if(get_config("etherpadlite", "adminguests") == 1) {
		if($e->guestsallowed) {
			return true;
		}
	}
	return false;
}

?>
