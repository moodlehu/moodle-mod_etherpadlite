<?php // $Id: index.php,v 1.7.2.3 2009/08/31 22:00:00 mudrd8mz Exp $

/**
 * This page lists all the instances of etherpadlite in a particular course
 * 
 * @package    mod
 * @subpackage etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // course

if (! $course = $DB->get_record('course', 'id', $id)) {
    error('Course ID is incorrect');
}

require_course_login($course);

add_to_log($course->id, 'etherpadlite', 'view all', "index.php?id=$course->id", '');


/// Get all required stringsetherpadlite

$stretherpadlites = get_string('modulenameplural', 'etherpadlite');
$stretherpadlite  = get_string('modulename', 'etherpadlite');


/// Print the header

$navlinks = array();
$navlinks[] = array('name' => $stretherpadlites, 'link' => '', 'type' => 'activity');
$navigation = build_navigation($navlinks);

print_header_simple($stretherpadlites, '', $navigation, '', '', true, '', navmenu($course));

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

print_heading($stretherpadlites);
print_table($table);

/// Finish the page

print_footer($course);

?>
