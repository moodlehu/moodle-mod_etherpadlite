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


// Global variable which stores the url to be copied.
var etherpadliteFullPadURL = null;


/**
 * This function gets called by the lib script and gets the etherpad url.
 * Be aware the navigator.clipboard object is only available in https.
 *
 * @module     mod_etherpadlite
 * @copyright  2022 André Menrath <andre.menrath@uni-graz.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function copyToClipboard() {
    navigator.clipboard.writeText(etherpadliteFullPadURL);
    require(['core/str', 'core/notification'], function(str, notification) {
        str.get_strings([
                {'key': 'link_copied', component: 'mod_etherpadlite'},
                {'key': 'etherpadlite_link_copied_to_clipboard', component: 'mod_etherpadlite'},
            ]).done(function(strings) {
                notification.alert(strings[0], strings[1]);
            }
        );
    });
}


/**
 * This function gets called by the lib script and gets the etherpad url.
 *
 * @module     mod_etherpadlite
 * @copyright  2022 André Menrath <andre.menrath@uni-graz.at>
 * @param      {string} url the full link to the etherpadlite url.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export const init = (url) => {
    etherpadliteFullPadURL = url;
    let button = document.getElementsByClassName('copy_etherpadlink_to_clipboard_button').item(0);
    button.addEventListener('click', copyToClipboard);
};
