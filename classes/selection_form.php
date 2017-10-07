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
        $mform->addElement('html', '<h5 id="mod_enea_form_heading_0">'
            .get_string('chooseoptions', 'mod_enea').'</h5>');
        $mform->addElement('html', '</br>');

        $objs = array();
        $objs[0] = $mform->createElement('advcheckbox', 'medicine', '',
            get_string('medicine', 'mod_enea'), array(), array(0, 1));
        $objs[1] = $mform->createElement('advcheckbox', 'pediatrician', '',
            get_string('pediatrician', 'mod_enea'), array(), array(0, 1));
        $objs[2] = $mform->createElement('advcheckbox', 'gynecologist', '',
            get_string('gynecologist', 'mod_enea'), array(), array(0, 1));
        $objs[3] = $mform->createElement('advcheckbox', 'gp', '',
            get_string('gp', 'mod_enea'), array(), array(0, 1));
        $objs[4] = $mform->createElement('advcheckbox', 'other', '',
            get_string('other', 'mod_enea'), array(), array(0, 1));
        $medicinegroup = $mform->createElement('group', 'medicinegroup', '',
            $objs, array('<br/>'.str_repeat('&emsp;', 3)), false);

        $objs = array();
        $objs[0] = $medicinegroup;
        $objs[1] = $mform->createElement('advcheckbox', 'nursing', '',
            get_string('nursing', 'mod_enea'), array(), array(0, 1));
        $objs[2] = $mform->createElement('advcheckbox', 'nutrition', '',
            get_string('nutrition', 'mod_enea'), array(), array(0, 1));
        $mform->addElement('group', 'foegroup', get_string('fieldofexpertise', 'mod_enea'),
            $objs, array('<br/>'), false);
        $mform->addHelpButton('foegroup', 'foehelp', 'mod_enea');

        $mform->disabledIf('pediatrician', 'medicine', 'noteq', 1);
        $mform->disabledIf('gynecologist', 'medicine', 'noteq', 1);
        $mform->disabledIf('gp', 'medicine', 'noteq', 1);
        $mform->disabledIf('other', 'medicine', 'noteq', 1);

        $mform->addElement('html', '<br/>');

        $bfrevdeps = array(
            'nutrition', 'composition', 'practice', 'healtheffects', 'obesity',
            'diabetes', 'gastrointestinalinfections', 'hypertension', 'allergy',
            'respiratoryinfections', 'cognition', 'disease', 'maternalhealth',
            'statistics', 'contraindications', 'support', 'interventions'
        );
        $objs = array();
        $objs[0] = $mform->createElement('advcheckbox', 'breastfeeding', '',
            get_string('breastfeeding', 'mod_enea'), array(), array(0, 1));
        $off = count($objs);
        foreach ($bfrevdeps as $key => $name) {
            $objs[$key+$off] = $mform->createElement('advcheckbox', 'bf'.$name,
                '', get_string($name, 'mod_enea'), array(), array(0, 1));
            $mform->disabledIf('bf'.$name, 'breastfeeding', 'noteq', 1);
        }
        $breastfeedinggroup = $mform->createElement('group', 'breastfeedinggroup', '',
            $objs, array('<br/>'.str_repeat('&emsp;', 3)), false);

        $bmsrevdeps = array(
            'definition', 'hypernatremia', 'history', 'marketing',
            'contraindications', 'decisionmaking', 'psychology', 'components',
            'composition', 'bioactivecompounds', 'qualitysafety', 'hygiene',
            'preparation', 'bottlefeeding', 'weaning'
        );
        $objs = array();
        $objs[0] = $mform->createElement('advcheckbox', 'breastmilksubst', '',
            get_string('breastmilksubst', 'mod_enea'), array(), array(0, 1));
        $off = count($objs);
        foreach ($bmsrevdeps as $key => $name) {
            $objs[$key+$off] = $mform->createElement('advcheckbox', 'bms'.$name,
                '', get_string($name, 'mod_enea'), array(), array(0, 1));
            $mform->disabledIf('bms'.$name, 'breastmilksubst', 'noteq', 1);
        }
        $breastmilksubstgroup = $mform->createElement('group', 'breastmilksubstgroup', '',
            $objs, array('<br/>'.str_repeat('&emsp;', 3)), false);

        $objs = array();
        $objs[0] = $breastfeedinggroup;
        $objs[1] = $breastmilksubstgroup;
        $mform->addElement('group', 'topicsgroup', get_string('topics', 'mod_enea'),
            $objs, array('<br/>'), false);
        $mform->addHelpButton('topicsgroup', 'topicshelp', 'mod_enea');

        $btnarr= array();
        $clearattrs = array('class' => 'btn btn-secondary', 'type' => 'button');
        $btnarr[] = $mform->createElement('reset', 'clearbutton',
            get_string('clear', 'mod_enea'), $clearattrs);
        $btnarr[] = $mform->createElement('submit', 'searchbutton',
            get_string('search', 'mod_enea'));
        $mform->addGroup($btnarr, 'buttongroup', '', ' ', false);
    }
}
