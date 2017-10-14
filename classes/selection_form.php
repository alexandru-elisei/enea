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
 * File containing the course selection form.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Course selection form.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_enea_selection_form extends moodleform {

    /**
     * Keywords that depend on the 'medicine' keyword.
     */
    protected $medicinerevdeps = array(
        'pediatrician', 'gynecologist', 'gp', 'other'
    );

    /**
     * Keywords that depend on the 'breastmilk substitute' keyword.
     */
    protected $bmsrevdeps = array(
        'nutrition', 'composition', 'practice', 'healtheffects', 'obesity',
        'diabetes', 'gastrointestinalinfections', 'hypertension', 'allergy',
        'respiratoryinfections', 'cognition', 'disease', 'maternalhealth',
        'statistics', 'contraindications', 'support', 'interventions'
    );

    /**
     * Keywords that depend on the 'breastfeeding' keyword.
     */
    protected $bfrevdeps = array(
        'nutrition', 'composition', 'practice', 'healtheffects', 'obesity',
        'diabetes', 'gastrointestinalinfections', 'hypertension', 'allergy',
        'respiratoryinfections', 'cognition', 'disease', 'maternalhealth',
        'statistics', 'contraindications', 'support', 'interventions'
    );

    /**
     * Types and themes.
     */
    protected $typesthemes = array(
        'science', 'guidelines', 'practice'
    );

    /**
     * The standard form definiton.
     */
    public function definition () {
        $mform = $this->_form;

        $mform->addElement('html', '</br>');
        $mform->addElement('html', '<h5 id="mod_enea_form_heading_0">'.get_string('chooseoptions', 'mod_enea').'</h5>');
        $mform->addElement('html', '</br>');

        $objs = array();
        $objs[] = $mform->createElement('advcheckbox', 'medicine', '', get_string('medicine', 'mod_enea'), array(), array(0, 1));
        foreach ($this->medicinerevdeps as $keyword) {
            $objs[] = $mform->createElement('advcheckbox', $keyword, '', get_string($keyword, 'mod_enea'), array(), array(0, 1));
        }
        $medicinegroup = $mform->createElement('group', 'medicinegroup', '', $objs, array('<br/>'.str_repeat('&emsp;', 3)), false);

        $objs = array();
        $objs[] = $medicinegroup;
        $objs[] = $mform->createElement('advcheckbox', 'nursing', '', get_string('nursing', 'mod_enea'), array(), array(0, 1));
        $objs[] = $mform->createElement('advcheckbox', 'nutrition', '', get_string('nutrition', 'mod_enea'), array(), array(0, 1));
        $mform->addElement('group', 'foegroup', get_string('fieldofexpertise', 'mod_enea'), $objs, array('<br/>'), false);
        $mform->addHelpButton('foegroup', 'foehelp', 'mod_enea');

        $mform->disabledIf('pediatrician', 'medicine', 'noteq', 1);
        $mform->disabledIf('gynecologist', 'medicine', 'noteq', 1);
        $mform->disabledIf('gp', 'medicine', 'noteq', 1);
        $mform->disabledIf('other', 'medicine', 'noteq', 1);

        $mform->addElement('html', '<br/>');

        $objs = array();
        $objs[] = $mform->createElement('advcheckbox', 'breastfeeding', '', get_string('breastfeeding', 'mod_enea'), array(), array(0, 1));
        foreach ($this->bfrevdeps as $keyword) {
            $objs[] = $mform->createElement('advcheckbox', 'bf'.$keyword, '', get_string($keyword, 'mod_enea'), array(), array(0, 1));
            $mform->disabledIf('bf'.$keyword, 'breastfeeding', 'noteq', 1);
        }
        $breastfeedinggroup = $mform->createElement('group', 'breastfeedinggroup', '', $objs, array('<br/>'.str_repeat('&emsp;', 3)), false);

        $objs = array();
        $objs[] = $mform->createElement('advcheckbox', 'breastmilksubst', '',
                                        get_string('breastmilksubst', 'mod_enea'), array(), array(0, 1));
        foreach ($this->bmsrevdeps as $key => $name) {
            $objs[] = $mform->createElement('advcheckbox', 'bms'.$name, '', get_string($name, 'mod_enea'), array(), array(0, 1));
            $mform->disabledIf('bms'.$name, 'breastmilksubst', 'noteq', 1);
        }
        $breastmilksubstgroup = $mform->createElement('group', 'breastmilksubstgroup', '',
                                                      $objs, array('<br/>'.str_repeat('&emsp;', 3)), false);

        $objs = array();
        $objs[] = $breastfeedinggroup;
        $objs[] = $breastmilksubstgroup;
        $mform->addElement('group', 'topicsgroup', get_string('topics', 'mod_enea'), $objs, array('<br/>'), false);
        $mform->addHelpButton('topicsgroup', 'topicshelp', 'mod_enea');

        $mform->addElement('html', '<br/>');

        $mform->addElement('text', 'text', get_string('othertopics', 'mod_enea'), 'size="20"');
        $mform->setType('text', PARAM_NOTAGS);

        $mform->addElement('html', '<br/>');

        $objs = array();
        foreach ($this->typesthemes as $name) {
            $objs[] = $mform->createElement('advcheckbox', 'typesthemes'.$name, '', get_string($name, 'mod_enea'), array(), array(0, 1));
        }
        $mform->addElement('group', 'typesthemesgroup', get_string('typesthemes', 'mod_enea'), $objs, array('<br/>'), false);

        $mform->addElement('html', '<br/>');

        $objs = array();
        $objs[] = $mform->createElement('advcheckbox', 'cme', get_string('yes', 'mod_enea'), array(), array(0, 1));
        $mform->addElement('group', 'cmepointsgroup', get_string('cmepoints', 'mod_enea'), $objs, array('<br/>'), false);

        $mform->addElement('html', '<br/>');

        $btnarr= array();
        $clearattrs = array('class' => 'btn btn-secondary', 'type' => 'button');
        $btnarr[] = $mform->createElement('reset', 'clearbutton', get_string('clear', 'mod_enea'), $clearattrs);
        $btnarr[] = $mform->createElement('submit', 'searchbutton', get_string('search', 'mod_enea'));
        $mform->addGroup($btnarr, 'buttongroup', '', ' ', false);

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

        $mform->addElement('hidden', 'stage', 0);
        $mform->setType('stage', PARAM_INT);
    }

    public function get_selection($formdata) {
        $request = array();

        $formdata = (array)$formdata;
        $request['cme'] = $this->is_checked($formdata, 'cme');

        $expertise = array();
        $medicine = array();
        if ($this->is_checked($formdata, 'medicine')) {
            foreach ($this->medicinerevdeps as $keyword) {
                if ($this->is_checked($formdata, $keyword)) {
                    $medicine[] = $keyword;
                }
            }
        }
        if (!empty($medicine)) {
            $expertise[] = array('medicine' => $medicine);
        }
        if ($this->is_checked($formdata, 'nursing')) {
            $expertise[] = 'nursing';
        }
        if ($this->is_checked($formdata, 'nutrition')) {
            $expertise[] = 'nutrition';
        }
        if (!empty($expertise)) {
            $request['expertise'] = $expertise;
        }

        $topics = array();
        $breastfeeding = array();
        if ($this->is_checked($formdata, 'breastfeeding')) {
            foreach ($this->bfrevdeps as $keyword) {
                if ($this->is_checked($formdata, 'bf'.$keyword)) {
                    $breastfeeding[] = $keyword;
                }
            }
        }
        if (!empty($breastfeeding)) {
            $topics[] = array('breastfeeding' => $breastfeeding);
        }

        $breastmilksubst = array();
        if ($this->is_checked($formdata, 'breastmilksubst')) {
            foreach ($this->bmsrevdeps as $keyword) {
                if ($this->is_checked($formdata, 'bms'.$keyword)) {
                    $breastmilksubst[] = $keyword;
                }
            }
        }
        if (!empty($breastmilksubst)) {
            $topics[] = array('breastmilksubst' => $breastmilksubst);
        }
        if (!empty($topics)) {
            $request['topics'] = $topics;
        }

        if ($this->is_checked($formdata, 'text')) {
            $request['text'] = $formdata['text'];
        }

        $themes = array();
        foreach ($this->typesthemes as $theme) {
            if ($this->is_checked($formdata, 'typesthemes'.$theme)) {
                $themes[] = $theme;
            }
        }
        if (!empty($themes)) {
            $request['themes'] = $themes;
        }

        return $request;
    }

    protected function is_checked($formdata, $field) {
        return (isset($formdata[$field]) and $formdata[$field]);
    }
}
