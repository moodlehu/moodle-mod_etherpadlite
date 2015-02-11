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
 * This page lists all the instances of etherpadlite in a particular course
 * 
 * @package    mod_etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

global $OUTPUT, $PAGE;

$id = required_param('id', PARAM_INT);   // course

$PAGE->set_url('/mod/etherpadlite/index.php', array('id'=>$id));

if (! $course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

require_course_login($course);

/// Get all required stringsetherpadlite

$stretherpadlites = get_string('modulenameplural', 'etherpadlite');
$stretherpadlite  = get_string('modulename', 'etherpadlite');

/// Get all the appropriate data

if (! $etherpadlites = get_all_instances_in_course('etherpadlite', $course)) {
    notice('There are no instances of etherpadlite', "../../course/view.php?id=$course->id");
    die;
}

/// Print the list of instances (your module will probably extend this)

$timenow  = time();
$strname  = get_string('name');
$strsummary = get_string('summary');
$strweek  = get_string('week');
$strtopic = get_string('topic');

$table = new html_table();

if ($course->format == 'weeks') {
    $table->head  = array ($strweek, $strname, $strsummary);
    $table->align = array ('center', 'left', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array ($strtopic, $strname, $strsummary);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strname, $strsummary);
    $table->align = array ('left', 'left', 'left');
}

foreach ($etherpadlites as $etherpadlite) {
    if (!$etherpadlite->visible) {
        //Show dimmed if the mod is hidden
        $link = '<a class="dimmed" href="view.php?id='.$etherpadlite->coursemodule.'">'.format_string($etherpadlite->name).'</a>';
    } else {
        //Show normal if the mod is visible
        $link = '<a href="view.php?id='.$etherpadlite->coursemodule.'">'.format_string($etherpadlite->name).'</a>';
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array ($etherpadlite->section, $link, format_text($etherpadlite->intro, FORMAT_MOODLE, 'para = false'));
    } else {
        $table->data[] = array ($link, format_text($etherpadlite->intro, FORMAT_MOODLE, 'para = false'));
    }
}

/// Output the page
$PAGE->navbar->add($stretherpadlites);
$PAGE->set_title("$course->shortname: $stretherpadlites");
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();

echo html_writer::table($table);

echo $OUTPUT->footer($course);

?>
