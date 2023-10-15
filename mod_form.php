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
 * This file defines the main etherpadlite configuration form.
 *
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 *
 * http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * The form must provide support for, at least these fields:
 *   - name: text element of 64cc max
 *
 * Also, it's usual to use these fields:
 *   - intro: one htmlarea element to describe the activity
 *            (will be showed in the list of activities of
 *             etherpadlite type (index.php) and in the header
 *             of the etherpadlite main page (view.php).
 *   - introformat: The format used to write the contents
 *             of the intro field. It automatically defaults
 *             to HTML when the htmleditor is used and can be
 *             manually selected if the htmleditor is not used
 *             (standard formats are: MOODLE, HTML, PLAIN, MARKDOWN)
 *             See lib/weblib.php Constants and the format_text()
 *             function for more info
 *
 * @package    mod_etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Etherpadlite configuration form.
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_etherpadlite_mod_form extends moodleform_mod {
    /**
     * Defines the mform elements.
     *
     * @return void
     */
    public function definition() {
        global $COURSE, $CFG;
        $mform  = $this->_form;
        $config = get_config('etherpadlite');

        try {
            $client = \mod_etherpadlite\api\client::get_instance($config->apikey, $config->url);
        } catch (\mod_etherpadlite\api\api_exception $e) {
            \core\notification::add($e->getMessage(), \core\notification::ERROR);
            $url = course_get_url($COURSE->id);
            redirect($url);
        }

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('etherpadlitename', 'etherpadlite'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 150), 'maxlength', 150, 'client');

        // Adding the required "intro" field to hold the description of the instance.
        $this->standard_intro_elements(get_string('etherpadliteintro', 'mod_etherpadlite'));

        // Is writing for guests allowed?
        if (get_config('etherpadlite', 'adminguests') == 1) {
            $mform->addElement('checkbox', 'guestsallowed', get_string('guestsallowed', 'etherpadlite'));
            $mform->addHelpButton('guestsallowed', 'guestsallowed', 'etherpadlite');
        }

        $mform->addElement('header', 'availabilityhdr', get_string('availability'));
        $mform->addElement('date_time_selector', 'timeopen', get_string('activityopen', 'etherpadlite'),
            ['optional' => true]);
        $mform->addHelpButton('timeopen', 'activityopenclose', 'etherpadlite');
        $mform->addElement('date_time_selector', 'timeclose', get_string('activityclose', 'etherpadlite'),
            ['optional' => true]);

        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}
