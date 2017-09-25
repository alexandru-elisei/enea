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
class mod_enea_selection_form extends moodleform {

    /**
     * The standard form definiton.
     */
    public function definition () {
        $mform = $this->_form;

        $mform->addElement('html', '</br>');
        $mform->addElement('html', '<h5 id="mod_enea_enrol_form_heading_0">' . get_string('chooseoptions', 'mod_enea') . '</h5>');
        $mform->addElement('html', '</br>');

        $objs = array();
        $objs[0] = $mform->createElement('advcheckbox', 'medicine', '', get_string('medicine', 'mod_enea'), array(), array(0, 1));
        $objs[1] = $mform->createElement('advcheckbox', 'pediatrician', '', get_string('pediatrician', 'mod_enea'), array(), array(0, 1));
        $objs[2] = $mform->createElement('advcheckbox', 'nursing', '', get_string('nursing', 'mod_enea'), array(), array(0, 1));
        $objs[3] = $mform->createElement('advcheckbox', 'gynecologist', '', get_string('gynecologist', 'mod_enea'), array(), array(0, 1));
        $objs[4] = $mform->createElement('advcheckbox', 'nutrition', '', get_string('nutrition', 'mod_enea'), array(), array(0, 1));
        $objs[5] = $mform->createElement('advcheckbox', 'gp', '', get_string('gp', 'mod_enea'), array(), array(0, 1));
        $objs[6] = $mform->createElement('html', '&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;');
        $objs[7] = $mform->createElement('advcheckbox', 'other', '', get_string('other', 'mod_enea'), array(), array(0, 1));
        $group = $mform->addElement('group', 'fieldofexpertisegroup', get_string('fieldofexpertise', 'mod_enea'), $objs, array('&emsp;&emsp;', '<br/>'), false);

        $mform->disabledIf('pediatrician', 'medicine', 'noteq', 1);
        $mform->disabledIf('gynecologist', 'medicine', 'noteq', 1);
        $mform->disabledIf('gp', 'medicine', 'noteq', 1);
        $mform->disabledIf('other', 'medicine', 'noteq', 1);
    }
}
