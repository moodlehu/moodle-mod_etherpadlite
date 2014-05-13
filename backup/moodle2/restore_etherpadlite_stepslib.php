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


/**
 * Structure step to restore one etherpadlite activity
 */
class restore_etherpadlite_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('etherpadlite', '/activity/etherpadlite');

        if($userinfo) {
            $paths[] = new restore_path_element('etherpadlite_content', '/activity/etherpadlite/content');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_etherpadlite($data) {
        global $DB;
        $config = get_config("etherpadlite");
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

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
            echo "\n\ncreateGroup Failed with message ". $e->getMessage();
        }

        try {
            $newpad = $instance->createGroupPad($groupID, $config->padname);
            $padID = $newpad->padID;
            //echo "Created new pad with padID: $padID\n\n";
        } catch (Exception $e) {
            // the pad already exists or something else went wrong
            echo "\n\ncreateGroupPad Failed with message ". $e->getMessage();
        }

        // seperator.output wieder zur�cksetzen
        ini_set('arg_separator.output', $separator);

        $data->uri = $padID;

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the etherpadlite record
        $newitemid = $DB->insert_record('etherpadlite', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_etherpadlite_content($data) {
        global $DB;
        $config = get_config("etherpadlite");
        $data = (object)$data;
        $instance = new EtherpadLiteClient($config->apikey,$config->url.'api');

        $newid = $this->get_new_parentid('etherpadlite');
        $etherpadlite = $DB->get_record('etherpadlite', array('id'=>$newid));
        $padID = $etherpadlite->uri;


        // php.ini separator.output auf '&' setzen
        $separator = ini_get('arg_separator.output');
        ini_set('arg_separator.output', '&');

        $instance = new EtherpadLiteClient($config->apikey,$config->url.'api');

        try {
            $instance->setHTML($padID, '<html>'.$data->html.'</html>');
        } catch (Exception $e) {
            // something went wrong
            echo "\n\nsetHTML Failed with message ". $e->getMessage();
        }

        // seperator.output wieder zur�cksetzen
        ini_set('arg_separator.output', $separator);
    }

    protected function after_execute() {
        // Add etherpadlite related files, no need to match by itemname (just internally handled context)
        global $DB;
        //$this->add_related_files('mod_etherpadlite', 'intro', null);

    }
}