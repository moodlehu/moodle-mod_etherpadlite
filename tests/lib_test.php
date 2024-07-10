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
 * Unit tests for general etherpadlite features.
 *
 * @package     mod_etherpadlite
 * @category    test
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_etherpadlite;

/**
 * Unit tests for general etherpadlite features.
 *
 * @package     mod_etherpadlite
 * @category    test
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_test extends \advanced_testcase {
    /**
     * Test create an instance.
     *
     * @covers ::etherpadlite_add_instance()
     * @return void
     */
    public function test_etherpadlite_initialise(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        // First create the course.
        $course = $this->getDataGenerator()->create_course();

        // Now create a etherpadlite instance.
        $params['course']    = $course->id;
        $params['timeopen']  = 0;
        $params['timeclose'] = 0;
        $params['name']      = 'testpad1';
        $params['intro']     = 'Intro to Testpad1';
        $etherpadlite        = $this->getDataGenerator()->create_module('etherpadlite', $params);

        // Test different ways to construct the structure object.
        $pseudocm = get_coursemodule_from_instance('etherpadlite', $etherpadlite->id); // Object similar to cm_info.
        $cm       = get_fast_modinfo($course)->instances['etherpadlite'][$etherpadlite->id]; // Instance of cm_info.

        $this->assertTrue($cm->instance == $etherpadlite->id);
        $this->assertTrue($DB->count_records('etherpadlite', null) == 1);
        $this->assertNotEmpty($etherpadlite->uri);
    }

    /**
     * Try to get an existing instance.
     *
     * @covers \mod_etherpadlite\util::get_coursemodule()
     * @return void
     */
    public function test_etherpadlite_get_instance(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        // First create the course.
        $coursenew = $this->getDataGenerator()->create_course();

        // Now create a new etherpadlite instance.
        $params['course']    = $coursenew->id;
        $params['timeopen']  = 0;
        $params['timeclose'] = 0;
        $params['name']      = 'testpad1';
        $params['intro']     = 'Intro to Testpad1';
        $etherpadlitenew     = $this->getDataGenerator()->create_module('etherpadlite', $params);
        $cmnew               = get_coursemodule_from_instance('etherpadlite', $etherpadlitenew->id);

        $course                           = $cm = $etherpadlite = null;
        list($course, $cm, $etherpadlite) = \mod_etherpadlite\util::get_coursemodule($cmnew->id, 0);
        $this->assertIsObject($course);
        $this->assertIsObject($cm);
        $this->assertIsObject($etherpadlite);

        $course                           = $cm = $etherpadlite = null;
        list($course, $cm, $etherpadlite) = \mod_etherpadlite\util::get_coursemodule(0, $etherpadlitenew->id);
        $this->assertIsObject($course);
        $this->assertIsObject($cm);
        $this->assertIsObject($etherpadlite);
    }

    /**
     * Create a new course group and check whether or not a new group pad is created.
     *
     * @covers \mod_etherpadlite\observer\group::group_created()
     * @return void
     */
    public function test_etherpadlite_add_group(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        // First create the course.
        $course = $this->getDataGenerator()->create_course();

        // Now create the first etherpadlite instance.
        $params['course']    = $course->id;
        $params['timeopen']  = 0;
        $params['timeclose'] = 0;
        $params['name']      = 'testpad1';
        $params['intro']     = 'Intro to Testpad1';
        $params['groupmode'] = 2;
        $etherpadlite1       = $this->getDataGenerator()->create_module('etherpadlite', $params);

        // Now create the second etherpadlite instance.
        $params['course']    = $course->id;
        $params['timeopen']  = 0;
        $params['timeclose'] = 0;
        $params['name']      = 'testpad2';
        $params['intro']     = 'Intro to Testpad2';
        $params['groupmode'] = 2;
        $etherpadlite2       = $this->getDataGenerator()->create_module('etherpadlite', $params);

        $this->assertFalse($DB->record_exists('etherpadlite_mgroups', ['padid' => $etherpadlite1->id]));
        $this->assertFalse($DB->record_exists('etherpadlite_mgroups', ['padid' => $etherpadlite2->id]));

        // We create a group and after that there should be a group pad for each pad.
        $group1 = $this->getDataGenerator()->create_group(
            [
                'courseid' => $course->id,
                'name'     => 'testgroup-1',
            ]
        );
        $this->assertTrue($DB->count_records('etherpadlite_mgroups', ['padid' => $etherpadlite1->id]) == 1);
        $this->assertTrue($DB->count_records('etherpadlite_mgroups', ['padid' => $etherpadlite2->id]) == 1);

        // We create a group and after that there should be a group pad for each pad.
        $group2 = $this->getDataGenerator()->create_group(
            [
                'courseid' => $course->id,
                'name'     => 'testgroup-2',
            ]
        );
        $this->assertTrue($DB->count_records('etherpadlite_mgroups', ['padid' => $etherpadlite1->id]) == 2);
        $this->assertTrue($DB->count_records('etherpadlite_mgroups', ['padid' => $etherpadlite2->id]) == 2);

        // Now create the third etherpadlite instance afterwards and check the group pad is created too.
        $params['course']    = $course->id;
        $params['timeopen']  = 0;
        $params['timeclose'] = 0;
        $params['name']      = 'testpad3';
        $params['intro']     = 'Intro to Testpad3';
        $params['groupmode'] = 2;
        $etherpadlite3       = $this->getDataGenerator()->create_module('etherpadlite', $params);
        $this->assertTrue($DB->count_records('etherpadlite_mgroups', ['padid' => $etherpadlite3->id]) == 2);
    }
}
