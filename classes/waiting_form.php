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
 * File containing the waiting form.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Waiting for server response form.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_enea_waiting_form extends moodleform {

    /**
     * The standard form definiton.
     */
    public function definition () {
        $mform = $this->_form;

        $mform->addElement('html', '</br>');
        $mform->addElement('html', '<h5 id="mod_enea_form_heading_0">' .get_string('waitingforresponse', 'mod_enea').'</h5>');
        $mform->addElement('html', '</br>');

        $customdata = $this->_customdata;
        if ($customdata) {
            if (isset($customdata['id']) and $customdata['id']) {
                $mform->addElement('hidden', 'id', $customdata['id']);
                $mform->setType('id', PARAM_INT);
            } else if (isset($customdaa['cmid']) and $customdata['cmid']) {
                $mform->addElement('hidden', 'cmid', $customdata['cmid']);
                $mform->setType('cmid', PARAM_INT);
            }
        }
        $mform->addElement('hidden', 'stage', 1);
        $mform->setType('stage', PARAM_INT);
    }
}
