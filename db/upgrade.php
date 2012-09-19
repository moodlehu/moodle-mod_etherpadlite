<?php  //$Id: upgrade.php,v 1.2 2007/08/08 22:36:54 stronk7 Exp $

/**
 * This file keeps track of upgrades to
 * the etherpadlite module
 *
 * @package    mod
 * @subpackage etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This file keeps track of upgrades to
// the etherpadlite module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_etherpadlite_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;
    
    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    
    $result = true;

/// And upgrade begins here. For each one, you'll need one
/// block of code similar to the next one. Please, delete
/// this comment lines once this file start handling proper
/// upgrade code.

/// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
///     $result = result of "/lib/ddllib.php" function calls
/// }

    return $result;
}

?>
