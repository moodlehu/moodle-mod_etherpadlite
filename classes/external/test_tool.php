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

namespace mod_etherpadlite\external;

use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_api;
use core_external\external_value;

/**
 * Implementation of web service mod_etherpadlite_test_tool
 *
 * @package    mod_etherpadlite
 * @copyright  2025 André Menrath <andre.menrath@uni-graz.at>, University of Graz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_tool extends external_api {

    /**
     * Describes the parameters for mod_etherpadlite_test_tool
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'url' => new external_value(PARAM_URL, 'The Etherpad Lite API URL'),
            'apikey' => new external_value(PARAM_TEXT, 'The Etherpad Lite API Key'),
        ]);
    }

    /**
     * Implementation of web service mod_etherpadlite_test_tool
     *
     * @param string $url
     * @param string $apikey
     */
    public static function execute($url, $apikey) {
         // Parameter validation.
         ['url' => $url, 'apikey' => $apikey] = self::validate_parameters(
            self::execute_parameters(),
            ['url' => $url, 'apikey' => $apikey]
        );

        // Verify context and capability.
        $context = \context_system::instance();
        self::validate_context($context);

        \require_admin();

        // Is the current host blocked?
        $blockedhost    = \mod_etherpadlite\api\client::is_url_blocked($url);
        $ignoresecurity = !empty(get_config('mod_etherpadlite', 'ignoresecurity'));

        $connected = false;
        $infotext  = '';

        if (!$blockedhost || $ignoresecurity) {
            // Try to establish a connection.
            try {
                $client = \mod_etherpadlite\api\client::get_instance($apikey, $url);
                $connected = true;
                unset($client);
            } catch (\mod_etherpadlite\api\api_exception $e) {
                $infotext = $e->getMessage();
            }
        }

        // Prepare blocked host information.
        if ($blockedhost) {
            $blockedhostinfo = $ignoresecurity
                ? get_string('urlisblocked_but_ignored', 'etherpadlite', $blockedhost)
                : get_string('urlisblocked', 'etherpadlite', $blockedhost);
        } else {
            $blockedhostinfo = '';
        }

        $result = [
            'connection' => [
                'success'     => $connected,
                'info'        => $infotext ?? '',
                'blockedinfo' => $blockedhostinfo,
            ],
        ];

        return $result;
    }

    /**
     * Describe the return structure for mod_etherpadlite_test_tool
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'connection' => new external_single_structure([
                'success'     => new external_value(PARAM_BOOL, 'The connection result'),
                'info'        => new external_value(PARAM_RAW, 'Any warning or info message from the connection'),
                'blockedinfo' => new external_value(PARAM_RAW, 'Any warning or info message from the blocked check'),
            ]),
        ]);
    }
}
