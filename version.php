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
 * Code fragment to define the version of etherpadlite
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @package    mod_etheradlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-UniversitÃ¤t zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version    = 2022022503;  // Don't go higher than 202203xxyy because there starts the version for 4.0.
$plugin->release    = 'v3.11-r2 (2022-04-14)';
$plugin->requires   = 2018050800;
$plugin->cron       = 0;           // Period for cron to check this module (secs).
$plugin->component  = 'mod_etherpadlite';
$plugin->maturity   = MATURITY_STABLE;
