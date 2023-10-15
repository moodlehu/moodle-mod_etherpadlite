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
 * Etherpadlite locallib.
 *
 * @package mod_etherpadlite
 * @copyright  20222 University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @param mixed $padid
 * @param mixed $paduri
 * @param mixed $client
 */

/**
 * Delete all grouppads.
 *
 * @param  string                       $padid
 * @param  string                       $paduri
 * @param  \mod_etherpadlite\api\client $client
 * @return void
 */
function mod_etherpadlite_delete_all_mgrouppads($padid, $paduri, $client) {
    global $DB;

    $mgrouppads = $DB->get_records('etherpadlite_mgroups', ['padid' => $padid]);
    if ($mgrouppads) {
        foreach ($mgrouppads as $mgrouppad) {
            $client->delete_pad($paduri . $mgrouppad->groupid);
        }
        $DB->delete_records('etherpadlite_mgroups', ['padid' => $padid]);
    }
}

/**
 * Add an etherpadlite grouppad.
 *
 * @param  \stdClass                    $formdata
 * @param  string                       $mpadid
 * @param  string                       $paduri
 * @param  \mod_etherpadlite\api\client $client
 * @return void
 */
function mod_etherpadlite_add_mgrouppads($formdata, $mpadid, $paduri, $client) {
    global $DB;

    $config = get_config('etherpadlite');
    $groups = groups_get_all_groups($formdata->course, 0, $formdata->groupingid);

    $epgroupid = explode('$', $paduri);
    $epgroupid = $epgroupid[0];

    $mgroupdb = [];
    foreach ($groups as $group) {
        $mgroup = new stdClass();
        if (!$DB->record_exists('etherpadlite_mgroups', ['padid' => $mpadid, 'groupid' => $group->id])) {
            $mgroup->padid   = $mpadid;
            $mgroup->groupid = $group->id;
            $mgroupdb[]      = $mgroup;
            try {
                $padid = $client->create_group_pad($epgroupid, $config->padname . $group->id);
            } catch (Exception $e) {
                continue;
            }
        }
    }
    $DB->insert_records('etherpadlite_mgroups', $mgroupdb);
}
