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
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @copyright  2019 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_etherpadlite\output;

class renderer extends \plugin_renderer_base {

    public function render_etherpad($etherpadlite, $cm, $frameurl, $activitymenu) {
        $config = get_config('etherpadlite');

        $summary = format_module_intro('etherpadlite', $etherpadlite, $cm->id);
        $summaryguest = '';
        if (isguestuser() && !etherpadlite_guestsallowed($etherpadlite)) {
            $summaryguest = get_string('summaryguest', 'etherpadlite');
        }

        $content = new \stdClass();
        $content->id = $cm->id;
        $content->name = $etherpadlite->name;
        $content->summary = $summary;
        $content->summaryguest = $summaryguest;
        $content->frameurl = $frameurl;
        $content->minwidth = (empty($config->minwidth) ? 10 : $config->minwidth).'px';
        $content->responsiveiframe = !empty($config->responsiveiframe);
        if (!$this->is_boost_based()) {
            $content->legacy = true;
        }

            // Populate some other values that can be used in calendar or on dashboard.
        if (!empty($etherpadlite->timeopen)) {
            $content->timeopen = userdate($etherpadlite->timeopen);
        }
        if (!empty($etherpadlite->timeclose)) {
            $content->timeclose = userdate($etherpadlite->timeclose);
        }

        $content->activitymenu = $activitymenu;

        return $this->render_from_template('mod_etherpadlite/content', $content);
    }

    public function is_boost_based() {
        if (strcmp($this->page->theme->name, 'boost') === 0) {
            return true;
        } else if (!empty($this->page->theme->parents)) {
            if (in_array('boost', $this->page->theme->parents) === true) {
                return true;
            }
        }
        return false;
    }
}
