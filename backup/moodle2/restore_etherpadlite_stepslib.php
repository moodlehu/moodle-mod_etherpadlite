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
 * Structure step to restore one etherpadlite activity.
 *
 * @package    mod_etherpadlite
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_etherpadlite_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define the restore structure for the etherpadlite plugin.
     *
     * @return array
     */
    protected function define_structure() {
        $paths    = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('etherpadlite', '/activity/etherpadlite');

        if ($userinfo) {
            $paths[] = new restore_path_element('etherpadlite_content', '/activity/etherpadlite/content');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process the restore.
     *
     * @param  \stdClass|array $data
     * @return void
     */
    protected function process_etherpadlite($data) {
        global $DB;
        $config       = get_config('etherpadlite');
        $data         = (object) $data;
        $oldid        = $data->id;
        $data->course = $this->get_courseid();

        try {
            $client = \mod_etherpadlite\api\client::get_instance($config->apikey, $config->url);
        } catch (\mod_etherpadlite\api\api_exception $e) {
            \core\notification::add($e->getMessage(), \core\notification::ERROR);

            return;
        }

        if (!empty($client)) {
            $epgroupid = $client->create_group();
        }

        if (!$epgroupid) {
            \core\notification::add('Could not create etherpad group', \core\notification::ERROR);

            return;
        }
        $padid = $client->create_group_pad($epgroupid, $config->padname);

        if (!$padid) {
            \core\notification::add('Could not create etherpad group pad', \core\notification::ERROR);

            return;
        }

        $data->uri = $padid;

        $data->timecreated  = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the etherpadlite record.
        $newitemid = $DB->insert_record('etherpadlite', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);

        $cm = get_coursemodule_from_instance('etherpadlite', $newitemid);
        // Get all groups.
        $groups = groups_get_all_groups($data->course, 0, $cm->groupingid);

        if ($cm->groupmode != 0 && $groups) {
            $mgroupdb = [];
            foreach ($groups as $group) {
                $mgroup            = [];
                $mgroup['padid']   = $newitemid;
                $mgroup['groupid'] = $group->id;
                $mgroupdb[]        = $mgroup;

                try {
                    $padid = $client->create_group_pad($epgroupid, $config->padname . $group->id);
                } catch (Exception $e) {
                    continue;
                }
            }
            $DB->insert_records('etherpadlite_mgroups', $mgroupdb);
        }
    }

    /**
     * Restore the etherpadlite content.
     *
     * @param  \stdClass|array $data
     * @return void
     */
    protected function process_etherpadlite_content($data) {
        global $DB;
        $config = get_config('etherpadlite');
        $data   = (object) $data;

        try {
            $client = \mod_etherpadlite\api\client::get_instance($config->apikey, $config->url);
        } catch (\mod_etherpadlite\api\api_exception $e) {
            \core\notification::add($e->getMessage(), \core\notification::ERROR);

            return;
        }

        $newid        = $this->get_new_parentid('etherpadlite');
        $etherpadlite = $DB->get_record('etherpadlite', ['id' => $newid]);
        $padid        = $etherpadlite->uri;

        // Set the content for the main pad.
        try {
            $client->set_text($padid, $data->text);
            $client->set_html($padid, $data->html);
        } catch (Exception $e) {
            // Something went wrong.
            echo "\n\nsetHTML Failed with message " . $e->getMessage();
        }

        if (!empty($data->grouppaddata)) {
            $grouppaddata = unserialize($data->grouppaddata);
            foreach ($grouppaddata as $oldgroupid => $grouppadcontent) {
                $newgroupid = $this->get_mappingid('group', $oldgroupid);
                $grouppadid = $padid . $newgroupid;
                try {
                    $client->set_text($grouppadid, $grouppadcontent->text);
                    $client->set_html($grouppadid, $grouppadcontent->html);
                } catch (Exception $e) {
                    // Something went wrong.
                    echo "\n\nsetHTML Failed with message " . $e->getMessage();
                }
            }
        }
    }

    /**
     * Add files after the database structure is restored.
     *
     * @return void
     */
    protected function after_execute() {
        $this->add_related_files('mod_etherpadlite', 'intro', null);
    }
}
