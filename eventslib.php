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
 * Module "etherpadlite" - Event handler library
 *
 * @package     mod
 * @subpackage  mod_etherpadlite
 * @copyright   2015 Alexander Bias, University of Ulm <alexander.bias@uni-ulm.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Require etherpad lite communication library
require_once($CFG->dirroot.'/mod/etherpadlite/etherpad-lite-client.php');


/**
 * Event handler function
 *
 * @param object $eventdata Event data
 * @return bool
 */
function delete_session($eventdata) {
    // Try to delete the Moodle user's etherpad lite session(s)
    try {
        // Get saved authorID from user preferences
        $authorID = get_user_preferences('mod_etherpadlite-authorID', null, $eventdata->userid);

        // Continue only if there's an authorID
        // If there's no authorID, the Moodle user did not use an etherpad lite activity at all or at least did not use it  since the session(s) have been deleted on the last logout
        if (!empty($authorID)) {
            // Get plugin config
            $config = get_config("etherpadlite");

            // Make a new instance from the etherpadlite client
            $instance = new EtherpadLiteClient($config->apikey, $config->url.'api');

            // Get all etherpad lite sessions of the author
            $sessions = $instance->listSessionsOfAuthor($authorID);

            // Delete all etherpad lite sessions of the author
            if (count(get_object_vars($sessions)) > 0) {
                foreach ($sessions as $sid => $s) {
                    // Delete session
                    $instance->deleteSession($sid);

                    // Log the event
                    $logevent = \mod_etherpadlite\event\session_deleted::create(array(
                        'userid' => $eventdata->userid,
                        'context' => context_user::instance($eventdata->userid),
                        'other' => array('etherpadsessionid' => $sid, 'etherpadinstance' => $config->url)
                    ));
                    $logevent->trigger();
                }

                // Delete the authorID in the user preferences
                // We don't need it anymore, it will be saved again the next time the Moodle user uses an etherpad lite activity
                $authorID = unset_user_preference('mod_etherpadlite-authorID');
            }
        }
    }
    // Don't care if deleting the etherpad lite session did not work as we can't do anything about it
    catch (Exception $e) {
        // Nothing to do
    }

    return true;
}
