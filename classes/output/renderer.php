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

namespace mod_etherpadlite\output;

/**
 * Renderer class for this plugin.
 *
 * @package    mod_etherpadlite
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @copyright  2019 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {
    /**
     * Renders the etherpad frame.
     *
     * @param  \stdClass $etherpadlite
     * @param  \stdClass $cm
     * @param  string    $frameurl
     * @return string    The rendered html output
     */
    public function render_etherpad($etherpadlite, $cm, $frameurl) {
        $config = get_config('etherpadlite');

        $summaryguest = '';
        if (isguestuser() && !etherpadlite_guestsallowed($etherpadlite)) {
            $summaryguest = get_string('summaryguest', 'etherpadlite');
        }

        $content                   = new \stdClass();
        $content->id               = $cm->id;
        $content->name             = $etherpadlite->name;
        $content->summaryguest     = $summaryguest;
        $content->frameurl         = $frameurl;
        $content->emptyurl         = new \moodle_url('/mod/etherpadlite/empty.html');
        $content->minwidth         = (empty($config->minwidth) ? 10 : $config->minwidth) . 'px';
        $content->courseurl        = new \moodle_url('/course/view.php', ['id' => $etherpadlite->course]);

        // Add a warning notice.
        if (\mod_etherpadlite\api\client::is_testing()) {
            $content->hasnotice  = true;
            $content->noticetype = \core\notification::WARNING;
            $content->notice     = get_string('urlnotset', 'mod_etherpadlite');
        }

        return $this->render_from_template('mod_etherpadlite/content', $content);
    }

    /**
     * Checks whether or not the current theme is based on boost.
     *
     * @return bool
     */
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
