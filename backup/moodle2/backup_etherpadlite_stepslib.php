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
 * Define the complete etherpadlite structure for backup, with file and id annotations.
 *
 * @package    mod_etherpadlite
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_etherpadlite_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the plugin structure for backup.
     *
     * @return \backup_nested_element
     */
    protected function define_structure() {
        global $DB;

        $config = get_config('etherpadlite');

        try {
            $client = \mod_etherpadlite\api\client::get_instance($config->apikey, $config->url);
        } catch (\mod_etherpadlite\api\api_exception $e) {
            \core\notification::add($e->getMessage(), \core\notification::ERROR);
        }

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $eplite = new backup_nested_element(
            'etherpadlite',
            ['id'],
            [
                'name',
                'intro',
                'introformat',
                'guestsallowed',
                'timecreated',
                'timemodified',
            ]
        );

        // Define elements of the content structure.
        // The elements "html" and "text" get the content of the main etherpad.
        // The element grouppaddata get the serialized data as array with objects (->text and ->html).
        $content = new backup_nested_element('content', null, ['html', 'text', 'grouppaddata']);

        // Build the tree.
        $eplite->add_child($content);

        // Define sources.
        $eplite->set_source_table('etherpadlite', ['id' => backup::VAR_ACTIVITYID]);
        $eplite->annotate_files('mod_etherpadlite', 'intro', null); // This file area hasn't an itemid.

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $modid = $this->task->get_activityid();
            if (!empty($client)) {
                if ($etherpadlite = $DB->get_record('etherpadlite', ['id' => $modid])) {
                    $padid = $etherpadlite->uri;
                    // Get all groups.
                    $groups       = groups_get_all_groups($etherpadlite->course);
                    $grouppaddata = [];
                    // Get group pads if exist.
                    $cm = get_coursemodule_from_instance('etherpadlite', $modid);
                    if ($cm->groupmode != 0 && $groups) {
                        // Get the HTML content of the group pads.
                        foreach ($groups as $group) {
                            $grouppadid               = $padid . $group->id;
                            $html                     = $client->get_html($grouppadid);
                            $text                     = $client->get_text($grouppadid);
                            $groupcontent             = new \stdClass();
                            $groupcontent->html       = $html->html;
                            $groupcontent->text       = $text->text;
                            $grouppaddata[$group->id] = $groupcontent;
                        }
                    }

                    $data               = new \stdClass();
                    $data->grouppaddata = serialize($grouppaddata);
                    // The HTML content of the main pad.
                    $html       = $client->get_html($padid);
                    $text       = $client->get_text($padid);
                    $data->html = $html->html;
                    $data->text = $text->text;
                    $content->set_source_array([$data]);
                }
            }
        }

        // Define id annotations.
        // We have none.

        // Define file annotations.

        // Return the root element (etherpadlite), wrapped into standard activity structure.
        return $this->prepare_activity_structure($eplite);
    }
}
