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
 * @package    mod_etherpadlite
 *
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @copyright  2019 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_etherpadlite\output\component;

class urlsettingsnote implements \renderable, \templatable {
    protected $data;
    public const MSGTYPE_INFO = 'warning';
    public const MSGTYPE_WARNING = 'warning';
    public const MSGTYPE_DANGER = 'danger';
    protected $msgtypes = array(
        self::MSGTYPE_INFO => 'info-circle',
        self::MSGTYPE_WARNING => 'exclamation-triangle',
        self::MSGTYPE_DANGER => 'exclamation-triangle',
    );

    /**
     * Constructor
     *
     * @param string $msg The main message.
     * @param string $msginfo The additional message which will be displayed depended on the msgtype value.
     * @param string $msgtype The message type can be "info", "warning", "danger" or empty.
     */
    public function __construct(string $msg, string $msginfo = '', string $msgtype = '') {
        $this->data = array();
        $this->data['msg'] = $msg;
        if (!empty($msginfo)) {
            $this->data['msginfo'] = $msginfo;
            if (isset($this->msgtypes[$msgtype])) {
                $this->data['msgtype'] = $msgtype;
                $this->data['icon'] = $this->msgtypes[$msgtype];
            }
        }
    }

    public function export_for_template(\renderer_base $output) {
        return $this->data;
    }
}
