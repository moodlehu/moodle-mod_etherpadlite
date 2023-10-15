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
 * etherpadlite module data generator class.
 *
 * @package     mod_etherpadlite
 * @category    test
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * etherpadlite module data generator class.
 *
 * @package     mod_etherpadlite
 * @category    test
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_etherpadlite_generator extends testing_module_generator {
    /**
     * Create a new instance of the etherpadlite activity.
     *
     * @param  array|stdClass|null $record
     * @param  array|null          $options
     * @return stdClass
     */
    public function create_instance($record = null, ?array $options = null) {
        $record = (object) (array) $record;

        return parent::create_instance($record, (array) $options);
    }
}
