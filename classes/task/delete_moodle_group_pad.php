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
 * This file defines an adhoc task to send notifications.
 *
 * @package    mod_etherpadlite
 * @copyright  2022 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_etherpadlite\task;

/**
 * Adhoc task to delete moodle group mode pads.
 *
 * @package    mod_etherpadlite
 * @copyright  2022 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_moodle_group_pad extends \core\task\adhoc_task {
    public function execute() {
        global $DB;

        $data = $this->get_custom_data();
        $etherpad = $DB->get_record('etherpadlite', ['id' => $data->etherpadliteid]);
        list($course, $cm) = get_course_and_cm_from_instance($data->etherpadliteid, 'etherpadlite');

        if ($cm->groupmode == 1 || $cm->groupmode == 2) {
            $mgroups = groups_get_all_groups($etherpad->course, 0, $cm->groupingid);
            if (in_array($data->mgroupid, array_keys($mgroups))) {
                return;
            }
        }
        $config = get_config("etherpadlite");
        $instance = new \mod_etherpadlite\client($config->apikey, $config->url.'api');
        $instance->delete_pad($data->paduri);

        $DB->delete_records('etherpadlite_mgroups', ['id' => $data->mrouppadid]);
    }
}
