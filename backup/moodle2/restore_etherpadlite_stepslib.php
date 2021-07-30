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
 * @package    mod_etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Structure step to restore one etherpadlite activity
 */
class restore_etherpadlite_activity_structure_step extends restore_activity_structure_step {
    protected function define_structure() {
        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('etherpadlite', '/activity/etherpadlite');

        if ($userinfo) {
            $paths[] = new restore_path_element('etherpadlite_content', '/activity/etherpadlite/content');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_etherpadlite($data) {
        global $DB;
        $config = get_config('etherpadlite');
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $instance = new \mod_etherpadlite\client($config->apikey, $config->url.'api');

        try {
            $groupid = $instance->create_group();
        } catch (Exception $e) {
            // The group already exists or something else went wrong.
            echo "\n\ncreateGroup Failed with message ". $e->getMessage();
        }

        try {
            $padid = $instance->create_group_pad($groupid, $config->padname);
        } catch (Exception $e) {
            // The pad already exists or something else went wrong.
            echo "\n\ncreateGroupPad Failed with message ". $e->getMessage();
        }

        $data->uri = $padid;

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the etherpadlite record.
        $newitemid = $DB->insert_record('etherpadlite', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    protected function process_etherpadlite_content($data) {
        global $DB;
        $config = get_config('etherpadlite');
        $data = (object)$data;
        $instance = new \mod_etherpadlite\client($config->apikey, $config->url.'api');

        $newid = $this->get_new_parentid('etherpadlite');
        $etherpadlite = $DB->get_record('etherpadlite', ['id' => $newid]);
        $padid = $etherpadlite->uri;

        $instance = new \mod_etherpadlite\client($config->apikey, $config->url.'api');

        try {
            $instance->set_text($padid, $data->text);
            $instance->set_html($padid, $data->html);
        } catch (Exception $e) {
            // Something went wrong.
            echo "\n\nsetHTML Failed with message ". $e->getMessage();
        }
    }

    protected function after_execute() {
        $this->add_related_files('mod_etherpadlite', 'intro', null);
    }
}
