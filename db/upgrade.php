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
 * This file keeps track of upgrades to
 * the etherpadlite module
 *
 * @package    mod_etherpadlite
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
// using the functions defined in lib/ddllib.php.

function xmldb_etherpadlite_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    $result = true;

    if ($oldversion < 2013042901) {
        set_config("url", $CFG->etherpadlite_url, "etherpadlite");
        set_config("apikey", $CFG->etherpadlite_apikey, "etherpadlite");
        set_config("padname", $CFG->etherpadlite_padname, "etherpadlite");
        set_config("cookiedomain", $CFG->etherpadlite_cookiedomain, "etherpadlite");
        set_config("cookietime", $CFG->etherpadlite_cookietime, "etherpadlite");
        set_config("ssl", $CFG->etherpadlite_ssl, "etherpadlite");
        set_config("check_ssl", $CFG->etherpadlite_check_ssl, "etherpadlite");
        set_config("adminguests", $CFG->etherpadlite_adminguests, "etherpadlite");

        $DB->delete_records_select("config", "name LIKE 'etherpadlite_%'");

        upgrade_mod_savepoint(true, 2013042901, "etherpadlite");
    }

    if ($oldversion < 2022041100) {

        // Define table etherpad_mgroups to be created.
        $table = new xmldb_table('etherpad_mgroups');

        // Adding fields to table etherpad_mgroups.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('padid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table etherpad_mgroups.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for etherpad_mgroups.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Etherpadlite savepoint reached.
        upgrade_mod_savepoint(true, 2022041100, 'etherpadlite');
    }

    if ($oldversion < 2022041100) {

        // Define table etherpadlite_mgroups to be renamed to NEWNAMEGOESHERE.
        $table = new xmldb_table('etherpad_mgroups');

        // Launch rename table for etherpadlite_mgroups.
        $dbman->rename_table($table, 'etherpadlite_mgroups');

        // Etherpadlite savepoint reached.
        upgrade_mod_savepoint(true, 2022041100, 'etherpadlite');
    }
    if ($oldversion < 2022041101) {

        // Define key groupdids (unique) to be added to etherpadlite_mgroups.
        $table = new xmldb_table('etherpadlite_mgroups');
        $key = new xmldb_key('groupdids', XMLDB_KEY_UNIQUE, ['padid', 'groupid']);

        // Launch add key groupdids.
        $dbman->add_key($table, $key);

        // Etherpadlite savepoint reached.
        upgrade_mod_savepoint(true, 2022041101, 'etherpadlite');
    }

    return $result;
}
