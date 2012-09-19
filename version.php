<?php // $Id: version.php,v 1.5.2.2 2009/03/19 12:23:11 mudrd8mz Exp $

/**
 * Code fragment to define the version of etherpadlite
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @package    mod
 * @subpackage etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$module->version  = 2012032100;  // The current module version (Date: YYYYMMDDXX)
$module->cron     = 0;           // Period for cron to check this module (secs)
$module->requires = 2010112400;

?>
