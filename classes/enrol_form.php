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
 * File containing the enrol form.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Enrol form.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_enea_enrol_form extends moodleform {

    /**
     * The standard form definiton.
     */
    public function definition () {
        $mform = $this->_form;

        $mform->addElement('header', 'generalhdr', get_string('modulename', 'mod_enea'));
        $mform->setExpanded('generalhdr', true);

        $mform->addElement('html', '</br>');
        $mform->addElement('html', '<h5 id="mod_enea_enrol_form_heading_0">Choose from the options listed below to generate a module you would like to take.</h5>');
        $mform->addElement('html', '</br>');

        $objs = array();
        $objs[0] = $mform->createElement('advcheckbox', 'medicine', '', get_string('medicine', 'mod_enea'), array(), array(false, true));
        $objs[1] = $mform->createElement('advcheckbox', 'nursing', '', get_string('nursing', 'mod_enea'), array(), array(false, true));
        $group = $mform->addElement('group', 'childrengroup', 'a) Field of Expertise:', $objs, array('                               ', '<br/>'), false);

        $mform->disabledIf('nursing', 'medicine', 'noteq', true);
    }
}
