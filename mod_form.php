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
 * This file defines the main etherpadlite configuration form
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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_etherpadlite_mod_form extends moodleform_mod {

    function definition() {

        global $COURSE, $CFG;
        $mform = $this->_form;

//-------------------------------------------------------------------------------
    /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

    /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('etherpadlitename', 'etherpadlite'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 150), 'maxlength', 150, 'client');

    /// Adding the required "intro" field to hold the description of the instance
        //$mform->addElement('htmleditor', 'intro', get_string('etherpadliteintro', 'etherpadlite'));
        //$mform->setType('intro', PARAM_RAW);
        //$mform->addRule('intro', get_string('required'), 'required', null, 'client');
        //$mform->setHelpButton('intro', array('writing', 'richtext'), false, 'editorhelpbutton');
    /// Adding "introformat" field
        //$mform->addElement('format', 'introformat', get_string('format'));

        // Above deprecated this line using instead:
        $this->add_intro_editor(false);

        // Is writing for guests allowed?
        if(get_config("etherpadlite", "adminguests") == 1) {
        	$mform->addElement('checkbox', 'guestsallowed', get_string('guestsallowed', 'etherpadlite'));
        	$mform->addHelpButton('guestsallowed', 'guestsallowed', 'etherpadlite');
        }

        // remove coursemodule elements
        $this->_features->groups = false;
        $this->_features->groupings = false;
        $this->_features->groupmembersonly = false;
        $this->_features->gradecat = false;
        $this->_features->idnumber = false;
        $this->standard_coursemodule_elements();

        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }
}