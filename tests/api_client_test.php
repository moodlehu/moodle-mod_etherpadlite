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
 * Unit tests for the API client.
 *
 * @package     mod_etherpadlite
 * @category    test
 * @author      Daniil Fajnberg <d.fajnberg@tu-berlin.de>
 * @copyright   2023 Daniil Fajnberg
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_etherpadlite;

use advanced_testcase;
use curl;
use mod_etherpadlite\api\api_exception;
use mod_etherpadlite\api\client;
use mod_etherpadlite\api\test_client;

/**
 * Unit tests for the API client.
 *
 * @package     mod_etherpadlite
 * @category    test
 * @author      Daniil Fajnberg <d.fajnberg@tu-berlin.de>
 */
class api_client_test extends advanced_testcase {
    /**
     * Test constructing a client instance.
     *
     * @covers \mod_etherpadlite\api\client::__construct()
     * @return void
     */
    public function test_construct() {
        $apikey = 'foo';
        $baseurl = 'https://example.com';
        // Purposefully add a trailing slash to the URL.
        $test_client = new test_client($apikey, $baseurl . '/');
        $this->assertEquals($baseurl, $test_client->baseurl);
        // Ensure the `apiurl` path was properly concatenated (with a single slash).
        $this->assertEquals($baseurl . '/api', $test_client->apiurl);
        // Check the rest of the relevant property assignments.
        $this->assertEquals($apikey, $test_client->apikey);
        $this->assertInstanceOf(curl::class, $test_client->curl);
        $this->assertEquals(
            client::DEFAULT_CONNECTTIMEOUT,
            $test_client->curloptions['CURLOPT_CONNECTTIMEOUT'],
        );
        $this->assertEquals(
            client::DEFAULT_TIMEOUT,
            $test_client->curloptions['CURLOPT_TIMEOUT'],
        );
        $this->assertEquals(
            client::DEFAULT_API_VERSION,
            $test_client->config->apiversion,
        );
    }

    /**
     * Test successfully validating a client configuration.
     *
     * @covers \mod_etherpadlite\api\client::validate()
     * @return void
     * @throws api_exception
     */
    public function test_validate_passes() {
        $test_client = $this->getMockBuilder(test_client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['check_version', 'check_token'])
            ->getMock();
        $test_client->apikey = 'not empty';
        $test_client->apiurl = 'https://some.valid.url';
        $version = 'something';
        $test_client->config = (object)['apiversion' => $version];
        $test_client->expects($this->exactly(2))
            ->method('check_version')
            ->withConsecutive([$version], ['1.2', $version])
            ->willReturnCallback(fn($_, $usedversion=null) => is_null($usedversion));
        $test_client->expects($this->never())->method('check_token');
        $test_client->validate();
    }

    /**
     * Test client config validation error due to the API token not passing the check.
     *
     * @covers \mod_etherpadlite\api\client::validate()
     * @return void
     */
    public function test_validate_invalid_key() {
        $test_client = $this->getMockBuilder(test_client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['check_version', 'check_token'])
            ->getMock();
        $test_client->apikey = 'not empty';
        $test_client->apiurl = 'https://some.valid.url';
        $version = 'something';
        $test_client->config = (object)['apiversion' => $version];
        $test_client->expects($this->exactly(2))
            ->method('check_version')
            ->withConsecutive([$version], ['1.2', $version])
            ->willReturn(true);
        $test_client->expects($this->once())
            ->method('check_token')
            ->willReturn(false);
        $this->expectException(api_exception::class);
        $test_client->validate();
    }

    /**
     * Test client config validation error due to wrong API version.
     *
     * @covers \mod_etherpadlite\api\client::validate()
     * @return void
     */
    public function test_validate_wrong_version() {
        $test_client = $this->getMockBuilder(test_client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['check_version', 'check_token'])
            ->getMock();
        $test_client->apikey = 'not empty';
        $test_client->apiurl = 'https://some.valid.url';
        $version = 'something';
        $test_client->config = (object)['apiversion' => $version];
        $test_client->expects($this->once())
            ->method('check_version')
            ->with($version)
            ->willReturn(false);
        $test_client->expects($this->never())->method('check_token');
        $this->expectException(api_exception::class);
        $test_client->validate();
    }

    /**
     * Test client config validation error due to an invalid API url or missing key.
     *
     * @covers \mod_etherpadlite\api\client::validate()
     * @return void
     */
    public function test_validate_invalid_url_or_missing_key() {
        $test_client = $this->getMockBuilder(test_client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['check_version', 'check_token'])
            ->getMock();
        $test_client->apikey = 'not empty';
        $test_client->apiurl = 'https://not a valid.url';
        $test_client->expects($this->never())->method('check_version');
        $test_client->expects($this->never())->method('check_token');
        $this->expectException(api_exception::class);
        $test_client->validate();

        $test_client->apikey = '';
        $test_client->apiurl = 'https://some.valid.url';
        $test_client->validate();
    }
}
