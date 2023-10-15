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
 * Library of functions and constants for module etherpadlite.
 *
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
 * @package    mod_etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('ETHERPADLITE_RESETFORM_RESET', 'etherpadlite_reset_data_');

/**
 * Create a new etherpadlite instance.
 *
 * Given an object containing all the necessary data, (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number of the new instance.
 *
 * @param  stdClass                  $etherpadlite An object from the form in mod_form.php
 * @param  mod_etherpadlite_mod_form $mform
 * @return int                       The id of the newly inserted etherpadlite record
 */
function etherpadlite_add_instance(stdClass $etherpadlite, $mform = null) {
    global $DB;
    $config = get_config('etherpadlite');

    try {
        $client = \mod_etherpadlite\api\client::get_instance($config->apikey, $config->url);
    } catch (\mod_etherpadlite\api\api_exception $e) {
        \core\notification::add($e->getMessage(), \core\notification::ERROR);

        return false;
    }

    if (!$epgroupid = $client->create_group()) {
        // The group already exists or something else went wrong.
        throw new \moodle_exception('could not create etherpad group');
    }

    if (!$padid = $client->create_group_pad($epgroupid, $config->padname)) {
        // The pad already exists or something else went wrong.
        throw new \moodle_exception('could not create etherpad group pad');
    }

    $etherpadlite->uri = $padid;

    $etherpadlite->timecreated = time();

    $padinstanceid = $DB->insert_record('etherpadlite', $etherpadlite);

    // Get all groups.
    $groups = groups_get_all_groups($etherpadlite->course, 0, $etherpadlite->groupingid);

    if ($etherpadlite->groupmode != 0 && $groups) {
        $mgroupdb = [];
        foreach ($groups as $group) {
            $mgroup          = new stdClass();
            $mgroup->padid   = $padinstanceid;
            $mgroup->groupid = $group->id;
            $mgroupdb[]      = $mgroup;

            try {
                $padid = $client->create_group_pad($epgroupid, $config->padname . $group->id);
            } catch (Exception $e) {
                continue;
            }
        }
        $DB->insert_records('etherpadlite_mgroups', $mgroupdb);
    }

    return $padinstanceid;
}

/**
 * Update an existing etherpadlite instance.
 *
 * Given an object containing all the necessary data, (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param  stdClass                  $etherpadlite An object from the form in mod_form.php
 * @param  mod_etherpadlite_mod_form $mform
 * @return bool                      Success/Fail
 */
function etherpadlite_update_instance(stdClass $etherpadlite, $mform = null) {
    global $DB;
    require_once(__DIR__ . '/locallib.php');

    $etherpadlite->timemodified = time();
    $etherpadlite->id           = $etherpadlite->instance;

    // You may have to add extra stuff in here.
    if (empty($etherpadlite->guestsallowed)) {
        $etherpadlite->guestsallowed = 0;
    }
    // If groupmode is not set anymore, delete mgroupspads if exist.
    $formdata        = $mform->get_data();
    $etherpadliteuri = $DB->get_field('etherpadlite', 'uri', ['id' => $etherpadlite->id]);
    $config          = get_config('etherpadlite');
    try {
        $client = \mod_etherpadlite\api\client::get_instance($config->apikey, $config->url);
    } catch (\mod_etherpadlite\api\api_exception $e) {
        \core\notification::add($e->getMessage(), \core\notification::ERROR);

        return false;
    }
    if ($formdata->groupmode != 0) {
        // Deletion will be done by adhoc task triggered by cm_update.
        mod_etherpadlite_add_mgrouppads($formdata, $etherpadlite->id, $etherpadliteuri, $client);
    }

    return $DB->update_record('etherpadlite', $etherpadlite);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param  int  $id Id of the module instance
 * @return bool Success/Failure
 */
function etherpadlite_delete_instance($id) {
    global $DB;
    require_once(__DIR__ . '/locallib.php');

    if (!$etherpadlite = $DB->get_record('etherpadlite', ['id' => $id])) {
        return false;
    }

    $result = true;

    // Delete any dependent records here.

    $config = get_config('etherpadlite');
    try {
        $client = \mod_etherpadlite\api\client::get_instance($config->apikey, $config->url);

        $padid     = $etherpadlite->uri;
        $epgroupid = explode('$', $padid);
        $epgroupid = $epgroupid[0];

        // Delete pads for moodle groups and respective DB entry.
        mod_etherpadlite_delete_all_mgrouppads($id, $padid, $client);

        $client->delete_pad($padid);
        $client->delete_group($epgroupid);
    } catch (\mod_etherpadlite\api\api_exception $e) {
        \core\notification::add($e->getMessage(), \core\notification::ERROR);
    }

    if (!$DB->delete_records('etherpadlite', ['id' => $etherpadlite->id])) {
        $result = false;
    }

    return $result;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description.
 *
 * @param  \stdClass      $course
 * @param  \stdClass      $user
 * @param  \stdClass      $mod
 * @param  \stdClass      $etherpadlite
 * @return \stdClass|null
 */
function etherpadlite_user_outline($course, $user, $mod, $etherpadlite) {
    return null;
}

/**
 * Print a detailed representation.
 *
 * Print a reprensentation of what a user has done with a given particular instance of this module, for user activity reports.
 *
 * @param  \stdClass $course
 * @param  \stdClass $user
 * @param  \stdClass $mod
 * @param  \stdClass $etherpadlite
 * @return bool
 */
function etherpadlite_user_complete($course, $user, $mod, $etherpadlite) {
    return true;
}

/**
 * Return true if there was output, or false is there was none.
 *
 * Given a course and a time, this module should find recent activity
 * that has occurred in etherpadlite activities and print it out.
 *
 * @param  \stdClass $course
 * @param  bool      $isteacher
 * @param  int       $timestart
 * @return bool
 * @todo Finish documenting this function
 */
function etherpadlite_print_recent_activity($course, $isteacher, $timestart) {
    return false;  // True if anything was printed, otherwise false.
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return bool
 * @todo Finish documenting this function
 **/
function etherpadlite_cron() {
    return true;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of etherpadlite. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param  int   $etherpadliteid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function etherpadlite_get_participants($etherpadliteid) {
    return false;
}

/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9.
 *
 * @return bool true if success, false on error
 */
function etherpadlite_install() {
    return true;
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9.
 *
 * @return bool true if success, false on error
 */
function etherpadlite_uninstall() {
    return true;
}

/**
 * Checks whether or not a given feature is supported.
 *
 * @param  string $feature FEATURE_xx constant for requested feature
 * @return bool   True if module supports feature, null if doesn't know
 */
function etherpadlite_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_GROUPMEMBERSONLY:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_COLLABORATION;
        default:
            return false;
    }
}

/**
 * Optionally extend the module settings menu for teachers and managers:
 * add a button which copies the url of the current pad to the clipboard.
 *
 * @param  settings_navigation $settingsnav    The settings navigation object
 * @param  navigation_node     $navigationnode The node to add module settings to
 * @return bool                true if success, false on error
 */
function etherpadlite_extend_settings_navigation($settingsnav, $navigationnode) {
    global $USER, $PAGE;

    if (has_capability('mod/etherpadlite:addinstance', $PAGE->cm->context)) {
        $config = get_config('etherpadlite');

        // Check if getting the pad url via the menu is enabled in the plugin settings.
        if ($config->copylink) {
            // Create navigation item with pseudo link.
            // It's just used as a button which triggers some javascript to copy the
            // pad url to the clipboard.
            $url                   = new moodle_url('#');
            $copytoclipboardbutton = navigation_node::create(
                get_string('copylink', 'mod_etherpadlite'),
                $url,
                navigation_node::TYPE_SETTING,
                null,
                'testkey',
                new pix_icon('t/copy', '')
            );
            $copytoclipboardbutton->classes = ['copy_etherpadlink_to_clipboard_button'];

            // Add the copy to clipboard button to the module menu navigation.
            $navigationnode->add_node($copytoclipboardbutton);

            // Get the full etherpad url and pass it as a variable to the
            // javascript which handles the copying and the notification.
            global $DB;
            $paduri = $DB->get_record('etherpadlite', ['id' => $PAGE->cm->instance], 'uri', MUST_EXIST);
            $url    = trim($config->url, '/');
            $url    .= '/p/' . $paduri->uri;

            // Include the javascript file, which handles the copy-to-clipboard process.
            $PAGE->requires->js_call_amd(
                'mod_etherpadlite/copy_to_clipboard',
                'init',
                [$url]
            );
        }
    }
}

// Any other etherpadlite functions go here.  Each of them must have a name that
// starts with etherpadlite_
// Remember (see note in first lines) that, if this section grows, it's HIGHLY
// recommended to move all funcions below to a new "localib.php" file.

/**
 * A funtion to generate a random name if something doesn't already exist.
 *
 * @return string
 */
function etherpadlite_gen_random_string() {
    $length     = 5;
    $characters = '0123456789';
    $string     = '';
    for ($p = 0; $p < $length; ++$p) {
        $string .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $string;
}

/**
 * Check whether or not guests are allowed.
 *
 * @param  \stdClass $etherpadlite
 * @return void
 */
function etherpadlite_guestsallowed($etherpadlite) {
    global $CFG;

    if (get_config('etherpadlite', 'adminguests') == 1) {
        if ($etherpadlite->guestsallowed) {
            return true;
        }
    }

    return false;
}

/**
 * Add a get_coursemodule_info function in case any etherpad type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param  stdClass       $coursemodule the coursemodule object (record)
 * @return cached_cm_info an object on information that the courses
 *                        will know about (most noticeably, an icon)
 */
function etherpadlite_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields   = 'id, course, name, timeopen, timeclose';
    if (!$etherpad = $DB->get_record('etherpadlite', $dbparams)) {
        return false;
    }

    $result       = new cached_cm_info();
    $result->name = $etherpad->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('etherpadlite', $etherpad, $coursemodule->id, false);
    }

    // Populate some other values that can be used in calendar or on dashboard.
    if ($etherpad->timeopen) {
        $result->customdata['timeopen'] = $etherpad->timeopen;
    }
    if ($etherpad->timeclose) {
        $result->customdata['timeclose'] = $etherpad->timeclose;
    }

    return $result;
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will remove all data from the specified etherpadlite.
 *
 * @param  object $data the data submitted from the reset course
 * @return array  status array
 */
function etherpadlite_reset_userdata($data) {
    global $DB;
    $config = get_config('etherpadlite');

    $resetetherpadlites = [];
    $status             = [];
    $componentstr       = get_string('modulenameplural', 'etherpadlite');

    // Get the relevant entries from $data.
    foreach ($data as $key => $value) {
        switch (true) {
            case substr($key, 0, strlen(ETHERPADLITE_RESETFORM_RESET)) == ETHERPADLITE_RESETFORM_RESET:
                if ($value == 1) {
                    $templist = explode('_', $key);
                    if (isset($templist[3])) {
                        $resetetherpadlites[] = (int) $templist[3];
                    }
                }
                break;
        }
    }

    if (empty($resetetherpadlites)) {
        return $status;
    }

    try {
        $client = \mod_etherpadlite\api\client::get_instance($config->apikey, $config->url);
    } catch (\mod_etherpadlite\api\api_exception $e) {
        \core\notification::add($e->getMessage(), \core\notification::ERROR);

        return $status;
    }

    // Reset the selected etherpadlites.
    foreach ($resetetherpadlites as $id) {
        $etherpadlite = $DB->get_record('etherpadlite', ['id' => $id]);
        // Delete etherpad lite data.
        $result   = \mod_etherpadlite\util::reset_etherpad_content($etherpadlite, $client);
        $status[] = [
            'component' => $componentstr . ': ' . $etherpadlite->name,
            'item'      => get_string('resetting_data', 'etherpadlite'),
            'error'     => !$result,
        ];
    }

    // Updating dates - shift may be negative too.
    if ($data->timeshift) {
        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.
        $shifterror = !shift_course_mod_dates('etherpadlite', ['timeopen', 'timeclose'], $data->timeshift, $data->courseid);
        $status[]   = ['component' => $componentstr, 'item' => get_string('datechanged'), 'error' => $shifterror];
    }

    return $status;
}

/**
 * Called by course/reset.php.
 *
 * @param object $mform form passed by reference
 */
function etherpadlite_reset_course_form_definition(&$mform) {
    global $COURSE, $DB;

    $mform->addElement('header', 'etherpadliteheader', get_string('modulenameplural', 'etherpadlite'));

    if (!$etherpadlites = $DB->get_records('etherpadlite', ['course' => $COURSE->id], 'name')) {
        return;
    }

    $mform->addElement('static', 'hint', get_string('resetting_data', 'etherpadlite'));
    foreach ($etherpadlites as $etherpadlite) {
        $mform->addElement('checkbox', ETHERPADLITE_RESETFORM_RESET . $etherpadlite->id, $etherpadlite->name);
    }
}

/**
 * Course reset form defaults.
 *
 * @param object $course
 */
function etherpadlite_reset_course_form_defaults($course) {
    global $DB;

    $return = [];
    if (!$etherpadlites = $DB->get_records('etherpadlite', ['course' => $course->id], 'name')) {
        return;
    }
    foreach ($etherpadlites as $etherpadlite) {
        $return[ETHERPADLITE_RESETFORM_RESET . $etherpadlite->id] = true;
    }

    return $return;
}
