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
 * This is a dummy class for testing purpose to simulate the communicate with the etherpadlite server
 *
 * @package    mod_etherpadlite
 *
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @link       https://github.com/TomNomNom/etherpad-lite-client
 * @license    Apache License
 */
class dummy_client extends client {

    /**
     * Constructor
     *
     * @param string $apikey
     * @param string $baseurl
     */
    protected function __construct($apikey, $baseurl = null) {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');

        $this->config = get_config('etherpadlite');

        if (strlen($apikey) < 1) {
            throw new \InvalidArgumentException('Config has no API key');
        }
        $this->apikey = $apikey;

        if (isset($baseurl)) {
            $this->baseurl = $baseurl;
        }
        if (!filter_var($this->baseurl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Config has no valid baseurl');
        }

        // Sometimes the etherpad host is located on an internal network like 127.0.0.1 or 10.0.0.0/8.
        // Since Moodle 4.0 this kind of host are blocked by default.
        $settings = array();
        if (!empty($this->config->ignoresecurity)) {
            $settings['ignoresecurity'] = true;
        }

        if (empty($this->config->apiversion)) {
            $this->config->apiversion = self::DEFAULT_API_VERSION;
        }
    }

    /**
     * Creates a new session
     *
     * @param string $epgroupid
     * @param string $authorid
     * @return string|boolean the new session id or false
     */
    public function create_session($epgroupid, $authorid) {
        return true;
    }

    /**
     * Create a new group
     *
     * @return string|boolean The new group id or false
     */
    public function create_group() {
        return random_string(20);
    }

    /**
     * Creates a new pad in this group.
     *
     * @param string $epgroupid
     * @param string $padname
     * @param string $text
     * @return string|boolean The new pad id or false
     */
    public function create_group_pad($epgroupid, $padname, $text = null) {
        return 'g.' . random_string(20).'$'.$padname;
    }

    /**
     * Deletes a group
     *
     * @param string $epgroupid
     * @return boolean
     */
    public function delete_group($epgroupid) {
        return true;
    }

    /**
     * Deletes a pad.
     *
     * @param string $padid
     * @return boolean
     */
    public function delete_pad($padid) {
        return true;
    }

    /**
     * Returns the read only link of a pad.
     *
     * @param string $padid
     * @return string|boolean The readonly id or false
     */
    public function get_readonly_id($padid) {
        return random_string(20);
    }

    /**
     * Create a new author.
     *
     * @param string $name
     * @return string|boolean The new author id or false
     */
    public function create_author($name) {
        return random_string(20);
    }

    /**
     * This functions helps you to map your application author ids to etherpad lite author ids.
     *
     * @param string $authormapper
     * @param string $name
     * @return string|boolean the new author id or false
     */
    public function create_author_if_not_exists_for($authormapper, $name) {
        return $this->create_author($name);
    }

    /**
     * Returns the text of a pad.
     *
     * @param string $padid
     * @param string $rev
     * @return string
     */
    public function get_text($padid, $rev = null) {
        return html_to_text($this->get_html($padid, $rev));
    }

    /**
     * Returns the text of a pad as html.
     *
     * @param string $padid
     * @param string $rev
     * @return string
     */
    public function get_html($padid, $rev = null) {
        return '<div>something <b>formatted</b></div>';
    }

    /**
     * Sets the text for a pad.
     *
     * @param string $padid
     * @param string $text
     * @return boolean
     */
    public function set_text($padid, $text) {
        return true;
    }

    /**
     * Sets the html text of a pad.
     *
     * @param string $padid
     * @param string $html
     * @return boolean
     */
    public function set_html($padid, $html) {
        return true;
    }

}
