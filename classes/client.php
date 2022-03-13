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

namespace mod_etherpadlite;

class client {
    const API_VERSION = '1.1';

    const CODE_OK = 0;
    const CODE_INVALID_PARAMETERS = 1;
    const CODE_INTERNAL_ERROR = 2;
    const CODE_INVALID_FUNCTION = 3;
    const CODE_INVALID_API_KEY = 4;

    protected $apikey = '';
    protected $baseurl = 'http://localhost:9001/api';
    protected $curl; // Use the moodle curl class.
    protected $config;

    public function __construct($apikey, $baseurl = null) {
        $this->config = get_config('etherpadlite');
        if (strlen($apikey) < 1) {
            throw new \InvalidArgumentException("[{$apikey}] is not a valid API key");
        }
        $this->apikey = $apikey;

        if (isset($baseurl)) {
            $this->baseurl = $baseurl;
        }
        if (!filter_var($this->baseurl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("[{$this->baseurl}] is not a valid URL");
        }

        // Sometimes the etherpad host is located on an internal network like 127.0.0.1 or 10.0.0.0/8.
        // Since Moodle 4.0 this kind of host are blocked by default.
        $settings = array();
        if (!empty($this->config->ignoresecurity)) {
            $settings['ignoresecurity'] = true;
        }
        $this->curl = new \curl($settings);
    }

    protected function get($function, array $arguments = []) {
        return $this->call($function, $arguments, 'GET');
    }

    protected function post($function, array $arguments = []) {
        return $this->call($function, $arguments, 'POST');
    }

    protected function call($function, array $arguments = [], $method = 'GET') {
        $arguments['apikey'] = $this->apikey;
        $url = $this->baseurl.'/'.self::API_VERSION.'/'.$function;

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

    // GROUPS
    // Pads can belong to a group.
    // There will always be public pads that doesnt belong to a group (or we give this group the id 0).

    // Creates a new group.
    public function create_group() {
        $group = $this->post('createGroup');
        if ($group) {
            return $group->groupID;
        }
        return false;
    }

    // This functions helps you to map your application group ids to etherpad lite group ids.
    public function create_group_if_not_exists_for($groupmapper) {
        $group = $this->post('createGroupIfNotExistsFor', [
            'groupMapper' => $groupmapper
        ]);
        if ($group) {
            return $group->groupID;
        }
        return false;
    }

    // Deletes a group.
    public function delete_group($groupid) {
        return $this->post('deleteGroup', [
            'groupID' => $groupid
        ]);
    }

    // Returns all pads of this group.
    public function list_pads($groupid) {
        return $this->get('listPads', [
            'groupID' => $groupid
        ]);
    }

    // Creates a new pad in this group.
    public function create_group_pad($groupid, $padname, $text = null) {
        $pad = $this->post('createGroupPad', [
            'groupID' => $groupid,
            'padName' => $padname,
            'text' => $text
        ]);
        if ($pad) {
            return $pad->padID;
        }
        return false;
    }

    // List all groups.
    public function list_all_groups() {
        return $this->get('listAllGroups');
    }

    // AUTHORS
    // Theses authors are bind to the attributes the users choose (color and name).

    // Creates a new author.
    public function create_author($name) {
        $author = $this->post('createAuthor', [
            'name' => $name
        ]);
        if ($author) {
            return $author->authorID;
        }
        return false;
    }

    // This functions helps you to map your application author ids to etherpad lite author ids.
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

    // Returns the ids of all pads this author as edited.
    public function list_pads_of_author($authorid) {
        return $this->get('listPadsOfAuthor', [
            'authorID' => $authorid
        ]);
    }

    // Gets an author's name.
    public function get_author_name($authorid) {
        return $this->get('getAuthorName', [
            'authorID' => $authorid
        ]);
    }

    // SESSIONS
    // Sessions can be created between a group and a author. This allows
    // an author to access more than one group. The sessionID will be set as
    // a cookie to the client and is valid until a certian date.

    // Creates a new session.
    public function create_session($groupid, $authorid, $validuntil) {
        $session = $this->post('createSession', [
            'groupID' => $groupid,
            'authorID' => $authorid,
            'validUntil' => $validuntil
        ]);
        if ($session) {
            return $session->sessionID;
        }
        return false;
    }

    // Deletes a session.
    public function delete_session($sessionid) {
        return $this->post('deleteSession', [
            'sessionID' => $sessionid
        ]);
    }

    // Returns informations about a session.
    public function get_session_info($sessionid) {
        return $this->get('getSessionInfo', [
            'sessionID' => $sessionid
        ]);
    }

    // Returns all sessions of a group.
    public function list_sessions_of_group($groupid) {
        return $this->get('listSessionsOfGroup', [
            'groupID' => $groupid
        ]);
    }

    // Returns all sessions of an author.
    public function list_sessions_of_author($authorid) {
        return $this->get('listSessionsOfAuthor', [
            'authorID' => $authorid
        ]);
    }

    // PAD CONTENT
    // Pad content can be updated and retrieved through the API.

    // Returns the text of a pad.
    public function get_text($padid, $rev = null) {
        $params = ['padID' => $padid];
        if (isset($rev)) {
            $params['rev'] = $rev;
        }
        return $this->get('getText', $params);
    }

    // Returns the text of a pad as html.
    public function get_html($padid, $rev = null) {
        $params = ['padID' => $padid];
        if (isset($rev)) {
            $params['rev'] = $rev;
        }
        return $this->get('getHTML', $params);
    }

    // Sets the text of a pad.
    public function set_text($padid, $text) {
        return $this->post('setText', [
            'padID' => $padid,
            'text' => $text
        ]);
    }

    // Sets the html text of a pad.
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

    // Creates a new pad.
    public function create_pad($padid, $text) {
        return $this->post('createPad', [
            'padID' => $padid,
            'text' => $text
        ], 'POST');
    }

    // Returns the number of revisions of this pad.
    public function get_revisions_count($padid) {
        return $this->get('getRevisionsCount', [
            'padID' => $padid
        ]);
    }

    // Returns the number of users currently editing this pad.
    public function pad_users_count($padid) {
        return $this->get('padUsersCount', [
            'padID' => $padid
        ]);
    }

    // Return the time the pad was last edited as a Unix timestamp.
    public function get_last_edited($padid) {
        return $this->get('getLastEdited', [
            'padID' => $padid
        ]);
    }

    // Deletes a pad.
    public function delete_pad($padid) {
        return $this->post('deletePad', [
            'padID' => $padid
        ]);
    }

    // Returns the read only link of a pad.
    public function get_readonly_id($padid) {
        $id = $this->get('getReadOnlyID', [
            'padID' => $padid
        ]);
        if ($id) {
            return $id->readOnlyID;
        }
        return false;
    }

    // Returns the ids of all authors who've edited this pad.
    public function list_authors_of_pad($padid) {
        return $this->get('listAuthorsOfPad', [
            'padID' => $padid
        ]);
    }

    // Sets a boolean for the public status of a pad.
    public function set_public_status($padid, $publicstatus) {
        if (is_bool($publicstatus)) {
            $publicstatus = $publicstatus ? 'true' : 'false';
        }
        return $this->post('setPublicStatus', [
            'padID' => $padid,
            'publicStatus' => $publicstatus
        ]);
    }

    // Return true of false.
    public function get_public_status($padid) {
        return $this->get('getPublicStatus', [
            'padID' => $padid
        ]);
    }

    // Returns ok or a error message.
    public function set_password($padid, $password) {
        return $this->post('setPassword', [
            'padID' => $padid,
            'password' => $password
        ]);
    }

    // Returns true or false.
    public function is_password_protected($padid) {
        return $this->get('isPasswordProtected', [
            'padID' => $padid
        ]);
    }

    // Get pad users.
    public function pad_users($padid) {
        return $this->get('padUsers', [
            'padID' => $padid
        ]);
    }

    // Send all clients a message.
    public function send_clients_message($padid, $msg) {
        return $this->post('sendClientsMessage', [
            'padID' => $padid,
            'msg' => $msg
        ]);
    }

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
}
