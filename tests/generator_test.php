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
 * Unit tests for generating instances.
 *
 * @package     mod_etherpadlite
 * @category    test
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_etherpadlite;

/**
 * Unit tests for generating instances.
 *
 * @package     mod_etherpadlite
 * @category    test
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class generator_test extends \advanced_testcase {
    /**
     * Test create an instance.
     *
     * @covers ::etherpadlite_add_instance()
     * @return void
     */
    public function test_create_instance(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('etherpadlite', ['course' => $course->id]));
        $etherpadlite = $this->getDataGenerator()->create_module(
            'etherpadlite',
            [
                'course'        => $course->id,
                'idnumber'      => 'mh1',
                'name'          => 'testpad1',
                'intro'         => 'Intro to Testpad1',
                'guestsallowed' => false,
                'timeopen'      => 0,
                'timeclose'     => 0,
            ]
        );
        $records = $DB->get_records('etherpadlite', ['course' => $course->id], 'id');
        $this->assertEquals(1, count($records));
        $this->assertTrue(array_key_exists($etherpadlite->id, $records));

        $params = [
            'course'        => $course->id,
            'idnumber'      => 'mh2',
            'name'          => 'testpad2',
            'intro'         => 'Intro to Testpad2',
            'guestsallowed' => false,
            'timeopen'      => 0,
            'timeclose'     => 0,
        ];
        $etherpadlite = $this->getDataGenerator()->create_module('etherpadlite', $params);
        $records      = $DB->get_records('etherpadlite', ['course' => $course->id], 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals('testpad2', $records[$etherpadlite->id]->name);
    }
}
