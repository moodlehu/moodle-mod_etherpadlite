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

namespace mod_etherpadlite\output\component;

/**
 * Output component to render a notification.
 *
 * @package    mod_etherpadlite
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @copyright  2019 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class urlsettingsnote implements \renderable, \templatable {
    /** @var array */
    protected $data = [];

    /**
     * Constructor.
     *
     * @param string $msg     the main message
     * @param string $msginfo the additional message which will be displayed depended on the msgtype value
     * @param string $msgtype the message type can be "info", "warning", "danger" or empty
     */
    public function __construct(\stdClass $config) {
        if (!empty($config->url)) {
            // Is the current host blocked?
            $blockedhost = \mod_etherpadlite\api\client::is_url_blocked($config->url);
            if ($blockedhost && empty($config->ignoresecurity)) {
                $connected = false;
            } else {
                // Check the connection with the current config, but only if the host is not blocked.
                try {
                    $client    = \mod_etherpadlite\api\client::get_instance($config->apikey, $config->url);
                    $connected = true;
                } catch (\mod_etherpadlite\api\api_exception $e) {
                    $connected = false;
                    $infotext = $e->getMessage();
                }
            }

            if ($connected) {
                $connectiontext = get_string('connected', 'etherpadlite');
                $connectiontextclass = 'success';
                $connectionicon = 'fa-check-square-o';
            } else {
                $connectiontext = get_string('not_connected', 'etherpadlite');
                $connectiontextreason = $infotext ?? '';
                $connectiontextclass = 'danger';
                $connectionicon = 'fa-times-circle-o';
            }

            $blockedhostinfo = '';
            if ($blockedhost) {
                $blockingicon = 'fa-exclamation-triangle';
                if (empty($config->ignoresecurity)) {
                    $blockedhostinfo = get_string('urlisblocked', 'etherpadlite', $blockedhost);
                    $blockingtextclass = 'danger';
                } else {
                    $blockedhostinfo = get_string('urlisblocked_but_ignored', 'etherpadlite', $blockedhost);
                    $blockingtextclass = 'warning';
                }
            }

            $this->data['urlinfo'] = get_string('urldesc', 'etherpadlite');
            $this->data['blockingmsg'] = $blockedhostinfo ?? '';
            $this->data['blockingicon'] = $blockingicon ?? '';
            $this->data['blockingtextclass'] = $blockingtextclass ?? '';
            $this->data['connectiontext'] = $connectiontext;
            $this->data['connectiontextreason'] = $connectiontextreason ?? '';
            $this->data['connectionicon'] = $connectionicon;
            $this->data['connectiontextclass'] = $connectiontextclass;

        }
    }

    /**
     * Get the mustache context data.
     *
     * @param  \renderer_base  $output
     * @return \stdClass|array
     */
    public function export_for_template(\renderer_base $output) {
        return $this->data;
    }
}
