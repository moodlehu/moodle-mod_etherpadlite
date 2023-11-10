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

namespace mod_etherpadlite\api;

/**
 * Client subclass exposing some protected members to make it testable.
 *
 * @package     mod_etherpadlite
 * @author      Daniil Fajnberg <d.fajnberg@tu-berlin.de>
 * @copyright   2023 Daniil Fajnberg
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_client extends client {
    /** @var string */
    public $apikey = '';
    /** @var string */
    public $baseurl = '';
    /** @var string */
    public $apiurl = '';
    /** @var \curl */
    public $curl;
    /** @var \stdClass */
    public $config;
    /** @var array */
    public $curloptions;

    /**
     * Constructor made public.
     *
     * @param string $apikey
     * @param string $baseurl
     */
    public function __construct($apikey, $baseurl) {
        parent::__construct($apikey, $baseurl);
    }
}
