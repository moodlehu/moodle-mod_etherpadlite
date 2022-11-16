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
 * @module     mod_etherpadlite/modal_iframe
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @copyright  2019 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    return {
        'init': function(frameurl, id) {
            $("#etherpadmodal_" + id).on("show.bs.modal", function() {
                $("#etherpadiframe_" + id).attr("src", "about:blank");
                $("#etherpadiframe2_" + id).attr("src", frameurl);
                $("body").addClass("modal-open");
            });
            $("#etherpadmodal_" + id).on("hide.bs.modal", function() {
                $("#etherpadiframe2_" + id).attr("src", "about:blank");
                $("#etherpadiframe_" + id).attr("src", frameurl);
                $("body").removeClass("modal-open");
            });

            $("#etherpadiframe_" + id).attr("src", frameurl);
        }
    };

});
