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
 * This is a helper class to communicate with the etherpadlite server
 *
 * @package    mod_etherpadlite
 *
 * @author     Timo Welde <tjwelde@gmail.com>
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @link       https://github.com/TomNomNom/etherpad-lite-client
 * @copyright  Tomnomnom <mail@tomnomnom.com>
 * @license    Apache License
 */
class client {
    /** The default api version if none is set in configuration */
    const DEFAULT_API_VERSION = '1.2';

    /** Return value for success */
    const CODE_OK = 0;
    /** Return value for invalid parameters */
    const CODE_INVALID_PARAMETERS = 1;
    /** Return value for internal error */
    const CODE_INTERNAL_ERROR = 2;
    /** Return value for invalid function */
    const CODE_INVALID_FUNCTION = 3;
    /** Return value for invalid api key */
    const CODE_INVALID_API_KEY = 4;

    /** @var string */
    protected $apikey = '';
    /** @var string */
    protected $baseurl = 'http://localhost:9001/api';
    /** @var \curl */
    protected $curl; // Use the moodle curl class.
    /** @var \stdClass */
    protected $config;

    /**
     * Constructor
     *
     * @param string $apikey
     * @param string $baseurl
     */
    protected function __construct($apikey, $baseurl) {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');

        $this->config = get_config('etherpadlite');

        if (strlen($apikey) < 1) {
            throw new \InvalidArgumentException('Config has no API key');
        }
        $this->apikey = $apikey;

        $this->baseurl = trim($baseurl, '/');
        $this->baseurl .= '/api';

        if (!filter_var($this->baseurl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Config has no valid baseurl');
        }

        // Sometimes the etherpad host is located on an internal network like 127.0.0.1 or 10.0.0.0/8.
        // Since Moodle 4.0 this kind of host are blocked by default.
        $settings = array();
        if (!empty($this->config->ignoresecurity)) {
            $settings['ignoresecurity'] = true;
        }
        $this->curl = new \curl($settings);

        if (empty($this->config->apiversion)) {
            $this->config->apiversion = self::DEFAULT_API_VERSION;
        }
        if (!$this->check_version($this->config->apiversion)) {
            throw new \InvalidArgumentException('Config has wrong api version');
        }

        if ($this->check_version('1.2', $this->config->apiversion)) {
            if (!$this->check_token()) {
                throw new \InvalidArgumentException('Config has an invalid API key');
            }
        }
    }

    /**
     * Start a get request
     *
     * @param string $function
     * @param array $arguments
     * @return \stdClass|boolean The returned data or false
     */
    protected function get($function, array $arguments = []) {
        return $this->call($function, $arguments, 'GET');
    }

    /**
     * Start a post request
     *
     * @param string $function
     * @param array $arguments
     * @return \stdClass|boolean The returned data or false
     */
    protected function post($function, array $arguments = []) {
        return $this->call($function, $arguments, 'POST');
    }

    /**
     * Start a request
     *
     * @param string $function
     * @param array $arguments
     * @param string $method
     * @return \stdClass|boolean The returned data or false
     */
    protected function call($function, array $arguments = [], $method = 'GET') {
        $arguments['apikey'] = $this->apikey;
        $url = $this->baseurl.'/'.$this->config->apiversion.'/'.$function;

        // All posts and gets use the moodle curl class.
        $options = [];
        // Should the certificate be verified.
        if (empty($this->config->check_ssl)) {
            $options = [
                'CURLOPT_SSL_VERIFYHOST' => 0,
                'CURLOPT_SSL_VERIFYPEER' => 0,
            ];
        }
        if ($method === 'POST') {
            $result = $this->curl->post($url, $arguments, $options);
        } else {
            $result = $this->curl->get($url, $arguments, $options);
        }

        if (!$result) {
            return false;
        }

        $result = json_decode($result);
        if ($result === null) {
            return false;
        }
        return $this->handle_result($result);
    }

    /**
     * Checks the result by looking at $result->code
     * If the code is ok the data are returned.
     *
     * @param \stdClass|array|null $result
     * @return \stdClass|array|null|boolean
     */
    protected function handle_result($result) {
        if (!isset($result->code)) {
            return false;
        }
        if (!isset($result->message)) {
            return false;
        }
        if (!isset($result->data)) {
            $result->data = null;
        }

        switch ($result->code) {
            case self::CODE_OK:
                return $result->data;
            case self::CODE_INVALID_PARAMETERS:
            case self::CODE_INVALID_API_KEY:
                return false;
            case self::CODE_INTERNAL_ERROR:
                return false;
            case self::CODE_INVALID_FUNCTION:
                return false;
            default:
                return false;
        }
    }

    /**
     * Get the api version from the etherpadlite server
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function get_version() {
        $url = $this->baseurl;
        $options = [];
        // Should the certificate be verified.
        if (empty($this->config->check_ssl)) {
            $options = [
                'CURLOPT_SSL_VERIFYHOST' => 0,
                'CURLOPT_SSL_VERIFYPEER' => 0,
            ];
        }
        $result = $this->curl->get($url, array(), $options);

        $result = json_decode($result);
        if (!empty($result->currentVersion)) {
            return $result->currentVersion;
        }
        throw new \InvalidArgumentException('Could not get api version');
    }

    /**
     * Check the needed api version
     *
     * @param string $neededversion
     * @param string $usedversion
     * @return boolean
     */
    public function check_version($neededversion, $usedversion = null) {
        if (is_null($usedversion)) {
            $currentversion = $this->get_version();
        } else {
            $currentversion = $usedversion;
        }
        return version_compare($currentversion, $neededversion, '>=');
    }

    /**
     * Check the API key on the etherpadlite server
     *
     * @return boolean
     */
    public function check_token() {
        return ($this->get('checkToken') !== false);
    }

    // GROUPS
    // Pads can belong to a group.
    // There will always be public pads that doesnt belong to a group (or we give this group the id 0).

    /**
     * Create a new group
     *
     * @return string|boolean The new group id or false
     */
    public function create_group() {
        $group = $this->post('createGroup');
        if ($group) {
            return $group->groupID;
        }
        return false;
    }

    /**
     * This functions helps you to map your application group ids to etherpad lite group ids.
     *
     * @param string $groupmapper
     * @return string|boolean The new group id or false
     */
    public function create_group_if_not_exists_for($groupmapper) {
        $group = $this->post('createGroupIfNotExistsFor', [
            'groupMapper' => $groupmapper
        ]);
        if ($group) {
            return $group->groupID;
        }
        return false;
    }

    /**
     * Deletes a group
     *
     * @param string $epgroupid
     * @return boolean
     */
    public function delete_group($epgroupid) {
        return $this->post('deleteGroup', [
            'groupID' => $epgroupid
        ]);
    }

    /**
     * Returns all pads of this group.
     *
     * @param string $epgroupid
     * @return array
     */
    public function list_pads($epgroupid) {
        return $this->get('listPads', [
            'groupID' => $epgroupid
        ]);
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
        $pad = $this->post('createGroupPad', [
            'groupID' => $epgroupid,
            'padName' => $padname,
            'text' => $text
        ]);
        if ($pad) {
            return $pad->padID;
        }
        return false;
    }

    /**
     * List all groups
     *
     * @return array
     */
    public function list_all_groups() {
        return $this->get('listAllGroups');
    }

    // AUTHORS
    // Theses authors are bind to the attributes the users choose (color and name).

    /**
     * Create a new author.
     *
     * @param string $name
     * @return string|boolean The new author id or false
     */
    public function create_author($name) {
        $author = $this->post('createAuthor', [
            'name' => $name
        ]);
        if ($author) {
            return $author->authorID;
        }
        return false;
    }

    /**
     * This functions helps you to map your application author ids to etherpad lite author ids.
     *
     * @param string $authormapper
     * @param string $name
     * @return string|boolean the new author id or false
     */
    public function create_author_if_not_exists_for($authormapper, $name) {
        $author = $this->post('createAuthorIfNotExistsFor', [
            'authorMapper' => $authormapper,
            'name' => $name
        ]);
        if ($author) {
            return $author->authorID;
        }
        return false;
    }

    /**
     * Returns the ids of all pads this author has edited.
     *
     * @param string $authorid
     * @return array
     */
    public function list_pads_of_author($authorid) {
        return $this->get('listPadsOfAuthor', [
            'authorID' => $authorid
        ]);
    }

    /**
     * Gets an author's name.
     *
     * @param string $authorid
     * @return string
     */
    public function get_author_name($authorid) {
        return $this->get('getAuthorName', [
            'authorID' => $authorid
        ]);
    }

    // SESSIONS
    // Sessions can be created between a group and a author. This allows
    // an author to access more than one group. The sessionID will be set as
    // a cookie to the client and is valid until a certian date.

    /**
     * Creates a new session
     *
     * @param string $epgroupid
     * @param string $authorid
     * @return string|boolean the new session id or false
     */
    public function create_session($epgroupid, $authorid) {
        $validuntil = time() + $this->config->cookietime;
        $session = $this->post('createSession', [
            'groupID' => $epgroupid,
            'authorID' => $authorid,
            'validUntil' => $validuntil
        ]);

        if ($session) {
            // If we reach the etherpadlite server over https, then the cookie should only be delivered over ssl.
            $ssl = (stripos($this->config->url, 'https://') === 0) ? true : false;
            setcookie('sessionID', $session->sessionID, $validuntil, '/', $this->config->cookiedomain, $ssl); // Set a cookie.

            return true;
        }
        return false;
    }

    /**
     * Deletes a session.
     *
     * @param string $sessionid
     * @return boolean
     */
    public function delete_session($sessionid) {
        return $this->post('deleteSession', [
            'sessionID' => $sessionid
        ]);
    }

    /**
     * Returns informations about a session.
     *
     * @param string $sessionid
     * @return array|\stdClass
     */
    public function get_session_info($sessionid) {
        return $this->get('getSessionInfo', [
            'sessionID' => $sessionid
        ]);
    }

    /**
     * Returns all sessions of a group.
     *
     * @param string $epgroupid
     * @return array
     */
    public function list_sessions_of_group($epgroupid) {
        return $this->get('listSessionsOfGroup', [
            'groupID' => $epgroupid
        ]);
    }

    /**
     * Returns all sessions of an author.
     *
     * @param string $authorid
     * @return array
     */
    public function list_sessions_of_author($authorid) {
        return $this->get('listSessionsOfAuthor', [
            'authorID' => $authorid
        ]);
    }

    // PAD CONTENT
    // Pad content can be updated and retrieved through the API.

    /**
     * Returns the text of a pad.
     *
     * @param string $padid
     * @param string $rev
     * @return \stdClass The text is defined in $obj->text.
     */
    public function get_text($padid, $rev = null) {
        $params = ['padID' => $padid];
        if (isset($rev)) {
            $params['rev'] = $rev;
        }
        return $this->get('getText', $params);
    }

    /**
     * Returns the text of a pad as html.
     *
     * @param string $padid
     * @param string $rev
     * @return \stdClass The html is defined in $obj->html.
     */
    public function get_html($padid, $rev = null) {
        $params = ['padID' => $padid];
        if (isset($rev)) {
            $params['rev'] = $rev;
        }
        return $this->get('getHTML', $params);
    }

    /**
     * Sets the text for a pad.
     *
     * @param string $padid
     * @param string $text
     * @return boolean
     */
    public function set_text($padid, $text) {
        return $this->post('setText', [
            'padID' => $padid,
            'text' => $text
        ]);
    }

    /**
     * Sets the html text of a pad.
     *
     * @param string $padid
     * @param string $html
     * @return boolean
     */
    public function set_html($padid, $html) {
        return $this->post('setHTML', [
            'padID' => $padid,
            'html' => $html
        ]);
    }

    // PAD
    // Group pads are normal pads, but with the name schema
    // GROUPID$padname. A security manager controls access of them and its
    // forbidden for normal pads to include a $ in the name.

    /**
     * Create a new pad.
     *
     * @param string $padid
     * @param string $text
     * @return boolean
     */
    public function create_pad($padid, $text) {
        return $this->post('createPad', [
            'padID' => $padid,
            'text' => $text
        ], 'POST');
    }

    /**
     * Returns the number of revisions of this pad.
     *
     * @param string $padid
     * @return int
     */
    public function get_revisions_count($padid) {
        return $this->get('getRevisionsCount', [
            'padID' => $padid
        ]);
    }

    /**
     * Returns the number of users currently editing this pad.
     *
     * @param string $padid
     * @return int
     */
    public function pad_users_count($padid) {
        return $this->get('padUsersCount', [
            'padID' => $padid
        ]);
    }

    /**
     * Return the time the pad was last edited as a Unix timestamp.
     *
     * @param string $padid
     * @return int
     */
    public function get_last_edited($padid) {
        return $this->get('getLastEdited', [
            'padID' => $padid
        ]);
    }

    /**
     * Deletes a pad.
     *
     * @param string $padid
     * @return boolean
     */
    public function delete_pad($padid) {
        return $this->post('deletePad', [
            'padID' => $padid
        ]);
    }

    /**
     * Returns the read only link of a pad.
     *
     * @param string $padid
     * @return string|boolean The readonly id or false
     */
    public function get_readonly_id($padid) {
        $id = $this->get('getReadOnlyID', [
            'padID' => $padid
        ]);
        if ($id) {
            return $id->readOnlyID;
        }
        return false;
    }

    /**
     * Returns the ids of all authors who've edited this pad.
     *
     * @param string $padid
     * @return array
     */
    public function list_authors_of_pad($padid) {
        return $this->get('listAuthorsOfPad', [
            'padID' => $padid
        ]);
    }

    /**
     * Sets a boolean for the public status of a pad.
     *
     * @param string $padid
     * @param boolean $publicstatus
     * @return boolean
     */
    public function set_public_status($padid, $publicstatus) {
        if (is_bool($publicstatus)) {
            $publicstatus = $publicstatus ? 'true' : 'false';
        }
        return $this->post('setPublicStatus', [
            'padID' => $padid,
            'publicStatus' => $publicstatus
        ]);
    }

    /**
     * Get the public status
     *
     * @param string $padid
     * @return boolean
     */
    public function get_public_status($padid) {
        return $this->get('getPublicStatus', [
            'padID' => $padid
        ]);
    }

    /**
     * Set a password for a pad
     *
     * @param string $padid
     * @param string $password
     * @return string|boolean
     */
    public function set_password($padid, $password) {
        return $this->post('setPassword', [
            'padID' => $padid,
            'password' => $password
        ]);
    }

    /**
     * Check whether or not a pad is protected by a password
     *
     * @param string $padid
     * @return boolean
     */
    public function is_password_protected($padid) {
        return $this->get('isPasswordProtected', [
            'padID' => $padid
        ]);
    }

    /**
     * Get all pad users
     *
     * @param string $padid
     * @return array
     */
    public function pad_users($padid) {
        return $this->get('padUsers', [
            'padID' => $padid
        ]);
    }

    /**
     * Send all clients a message.
     *
     * @param string $padid
     * @param string $msg
     * @return boolean
     */
    public function send_clients_message($padid, $msg) {
        return $this->post('sendClientsMessage', [
            'padID' => $padid,
            'msg' => $msg
        ]);
    }

    /**
     * Checks whether or not the server url is blocked by moodle settings
     *
     * @param string $urlstring
     * @return boolean
     */
    public static function is_url_blocked($urlstring) {
        $curl = new \curl(array('ignoresecurity' => true));
        $url = new \moodle_url($urlstring);
        if ($curl->get_security()->url_is_blocked($url)) {
            $ipstring = '';
            if ($ips = gethostbynamel($url->get_host())) {
                $ipstring = implode(', ', $ips);
            }
            return $url->get_host().'('.$ipstring.')';
        }

        return false;
    }

    /**
     * Get an instance of the client communicating with the etherpad server
     * If the site is in testing mode (behat or unit test) a dummy client is created, which only pretend to communicate.
     *
     * @param string $apikey
     * @param string $baseurl
     * @return static
     */
    public static function get_instance($apikey, $baseurl = null) {
        static $client;
        if (empty($client)) {
            if (static::is_testing()) {
                $client = new dummy_client($apikey, $baseurl);
            } else {
                $client = new static($apikey, $baseurl);
            }
        }
        return $client;
    }

    /**
     * Checks whether or not the current site is running a test (behat or unit test).
     *
     * @return boolean
     */
    public static function is_testing() {
        $mycfg = get_config('etherpadlite');

        if (empty($mycfg->url)) {
            return true;
        }

        if (defined('BEHAT_SITE_RUNNING')) {
            return true;
        }
        if ((defined('PHPUNIT_TEST') && PHPUNIT_TEST)) {
            return true;
        }
        return false;
    }
}
