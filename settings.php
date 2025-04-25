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
 * These are the settings for this module.
 *
 * @package    mod_etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 *             Andre Menrath <andre.menrath@uni-graz.at>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig && $ADMIN->fulltree) {
    /** @var \admin_settingpage $settings */
    $settings->add(new admin_setting_configtext('etherpadlite/url', get_string('url', 'etherpadlite'),
        get_string('urldesc', 'etherpadlite'), '', PARAM_URL, 40));

    $settings->add(new admin_setting_configcheckbox('etherpadlite/ignoresecurity', get_string('ignoresecurity', 'etherpadlite'),
        get_string('ignoresecuritydesc', 'etherpadlite'), false));

    $settings->add(new admin_setting_configtext('etherpadlite/apikey', get_string('apikey', 'etherpadlite'),
        get_string('apikeydesc', 'etherpadlite'), 'Enter your API Key', PARAM_RAW, 40));

    // Connection and Credential Test Tool.
    $attributes = [
        'class' => 'btn btn-secondary disabled',
        'disabled' => 'disabled',
        'data-action' => 'mod-etherpadlite-test-tool',
        'title' => get_string('testtooldisabledbuttontitle', 'etherpadlite'),
    ];
    $connectiontoolbutton = \html_writer::tag(
        'button',
        get_string('testtoolbutton', 'etherpadlite'),
        $attributes
    );
    $settings->add(new \admin_setting_description(
        'etherpadlite/testtool',
        get_string('testtoolheader', 'etherpadlite'),
        get_string('testtoolheaderdesc', 'etherpadlite', $connectiontoolbutton)
    ));
    $PAGE->requires->js_call_amd('mod_etherpadlite/test_tool', 'init');

    $settings->add(new admin_setting_configselect('etherpadlite/apiversion', get_string('apiversion', 'etherpadlite'),
        get_string('apiversiondesc', 'etherpadlite'), '1.2', ['1.1' => '1.1', '1.2' => '1.2']));

    $settings->add(new admin_setting_configtext('etherpadlite/connecttimeout', get_string('connecttimeout', 'etherpadlite'),
        get_string('connecttimeoutdesc', 'etherpadlite'), 300, PARAM_INT));

    $settings->add(new admin_setting_configtext('etherpadlite/timeout', get_string('timeout', 'etherpadlite'),
        get_string('timeoutdesc', 'etherpadlite'), 0, PARAM_INT));

    $settings->add(new admin_setting_configtext('etherpadlite/padname', get_string('padname', 'etherpadlite'),
        get_string('padnamedesc', 'etherpadlite'), 'mymoodle2'));

    $settings->add(new admin_setting_configtext('etherpadlite/cookiedomain', get_string('cookiedomain', 'etherpadlite'),
        get_string('cookiedomaindesc', 'etherpadlite'), '.mydomain.local'));
    $settings->add(new admin_setting_configtext('etherpadlite/cookietime', get_string('cookietime', 'etherpadlite'),
        get_string('cookietimedesc', 'etherpadlite'), '10800', PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('etherpadlite/ssl', get_string('ssl', 'etherpadlite'),
        get_string('ssldesc', 'etherpadlite'), '0'));
    $settings->add(new admin_setting_configcheckbox('etherpadlite/check_ssl', get_string('checkssl', 'etherpadlite'),
        get_string('checkssldesc', 'etherpadlite'), 0));

    $settings->add(new admin_setting_configcheckbox('etherpadlite/adminguests', get_string('adminguests', 'etherpadlite'),
        get_string('adminguestsdesc', 'etherpadlite'), '0'));

    $settings->add(new admin_setting_configtext('etherpadlite/minwidth', get_string('minwidth', 'etherpadlite'),
        get_string('minwidthdesc', 'etherpadlite'), '400', PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('etherpadlite/copylink', get_string('copylink', 'etherpadlite'),
        get_string('copylinkdesc', 'etherpadlite'), '0'));
    $options    = [];
    $options[0] = get_string('donotdelete', 'etherpadlite');
    $options[1] = get_string('deletenow', 'etherpadlite');
    $options[2] = get_string('deleteinonehour', 'etherpadlite');
    $options[3] = get_string('deleteintwelvehours', 'etherpadlite');
    $options[4] = get_string('deletein24hours', 'etherpadlite');

    $settings->add(new admin_setting_configselect('etherpadlite/deletemgrouppad', get_string('deletemgroupads', 'etherpadlite'),
        get_string('deletemgroupadsdesc', 'etherpadlite'), 2, $options));
}
