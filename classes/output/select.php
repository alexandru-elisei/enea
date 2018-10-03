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
 * Renderable for displaying the keyword selection page.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_enea\output;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use renderable;
use templatable;
use stdClass;

/**
 * Keyword selection renderable.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class select implements templatable, renderable {

    protected $data;

    /**
     * Keywords that depend on the 'medicine' keyword.
     */
    protected $medrevdeps = array(
        'paediatrics', 'obstetrics_gynocology',
        'generalpractice_familymedicine', 'specialistotherareas'
    );

    /**
     * Keywords that depend on the 'pregnancy' keyword.
     */
    protected $pregnancyrevdeps = array(
        'physiology', 'gestationalweightgain', 'fetalgrowthdevelopment',
        'fetusplacenta', 'preconception', 'nutrition', 'micronutrients',
        'foodborneinfection', 'physicalactivity', 'alcoholsmokingcaffeine',
        'stress', 'medicationenvironment', 'obesity',
        'developmentalprogramming', 'interventions', 'diabetes', 'vomiting',
        'eatingdisorders',

    );

    /**
     * Keywords that depend on the 'breastfeeding' keyword.
     */
    protected $bfrevdeps = array(
        'maternalnutrition', 'composition', 'practice', 'healtheffects', 'obesity',
        'diabetes', 'diabetesmellitus', 'gastrointestinalinfections', 'cognition',
        'otherchildhealtheffects', 'maternalhealtheffects', 'epidemology',
        'barriers', 'psychosocialfactors', 'publichealthinterventions'
    );

    /**
     * Keywords that depend on the 'breastmilk substitute' keyword.
     */
    protected $bmsrevdeps = array(
        'introduction', 'hypernatremia', 'marketing', 'medicalreasons',
        'psychosocialfactors', 'standardcomponents', 'macronutrients',
        'optionalcomponents', 'qualityandsafety', 'hygiene', 'preparation',
        'bottlefeeding', 'bottleweaning',
    );

    /**
     * Keywords that depend on the 'preterm' keyword.
     */
    protected $pretermrevdeps = array(
        'epidemiology', 'benefits', 'family', 'needs', 'macronutrients',
        'electrolytes', 'micronutrients', 'standardisingcare', 'management',
        'growth', 'specificmanagement', 'lowresource', 'postdischarge',
    );


    protected $themes = array(
        'background', 'practicecounselling', 'guidelines',
    );

    public function __construct($formdata) {
        $this->data = (object)$formdata;

        $medrevdeps = array();
        foreach ($this->medrevdeps as $name) {
            $medrevdeps[] = array(
                'name' => 'med'.$name,
                'text' => get_string($name, 'mod_enea'),
            );
        }
        $this->data->medrevdeps = $medrevdeps;

        $pregnancyrevdeps = array();
        foreach ($this->pregnancyrevdeps as $name) {
            $pregnancyrevdeps[] = array(
                'name' => 'preg'.$name,
                'text' => get_string($name, 'mod_enea'),
            );
        }
        $this->data->pregnancyrevdeps = $pregnancyrevdeps;

        $bfrevdeps = array();
        foreach ($this->bfrevdeps as $name) {
            $bfrevdeps[] = array(
                'name' => 'bf'.$name,
                'text' => get_string($name, 'mod_enea'),
            );
        }
        $this->data->bfrevdeps = $bfrevdeps;

        $bmsrevdeps = array();
        foreach ($this->bmsrevdeps as $name) {
            $bmsrevdeps[] = array(
                'name' => 'bms'.$name,
                'text' => get_string($name, 'mod_enea'),
            );
        }
        $this->data->bmsrevdeps = $bmsrevdeps;

        $pretermrevdeps = array();
        foreach ($this->pretermrevdeps as $name) {
            $pretermrevdeps[] = array(
                'name' => 'preterm'.$name,
                'text' => get_string($name, 'mod_enea'),
            );
        }
        $this->data->pretermrevdeps = $pretermrevdeps;

    }

    /**
     * Export this class data as a flat list for rendering in a template.
     *
     * @param renderer_base $output The current page renderer.
     * @return stdClass - Flat list of exported data.
     */
    public function export_for_template(renderer_base $output) {
        return $this->data;
    }

    public function export_for_server() {
        $request = array();

        $formdata = (array)$this->data;
        $request['cme'] = $this->is_checked($formdata, 'cme');

        $expertise = array();
        $medicine = array();
        if ($this->is_checked($formdata, 'medicine')) {
            foreach ($this->medrevdeps as $keyword) {
                if ($this->is_checked($formdata, 'med'.$keyword)) {
                    $medicine[] = $this->get_server_id($keyword);
                }
            }
        }
        if (!empty($medicine)) {
            $expertise[] = array('medicine' => $medicine);
        }
        if ($this->is_checked($formdata, 'nursing_midwifery')) {
            $expertise[] = $this->get_server_id('nursing_midwifery');
        }
        if ($this->is_checked($formdata, 'nutrition_dietetics')) {
            $expertise[] = $this->get_server_id('nutrition_dietetics');
        }
        if ($this->is_checked($formdata, 'otherhealthcareworkers')) {
            $expertise[] = $this->get_server_id('otherhealthcareworkers');
        }
        if ($this->is_checked($formdata, 'student_trainee')) {
            $expertise[] = $this->get_server_id('student_trainee');
        }
        $request['expertise'] = $expertise;

        $topics = array();
        $pregnancy = array();
        if ($this->is_checked($formdata, 'pregnancy')) {
            foreach ($this->pregnancyrevdeps as $keyword) {
                if ($this->is_checked($formdata, 'preg'.$keyword)) {
                    $pregnancy[] = $this->get_server_id($keyword);
                }
            }
        }
        if (!empty($pregnancy)) {
            $topics[] = array('pregnancy' => $pregnancy);
        }
        $breastfeeding = array();
        if ($this->is_checked($formdata, 'breastfeeding')) {
            foreach ($this->bfrevdeps as $keyword) {
                if ($this->is_checked($formdata, 'bf'.$keyword)) {
                    $breastfeeding[] = $this->get_server_id($keyword);
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
                    $breastmilksubst[] = $this->get_server_id($keyword);
                }
            }
        }
        if (!empty($breastmilksubst)) {
            $topics[] = array('breastmilk substitutes' => $breastmilksubst);
        }
        $preterm = array();
        if ($this->is_checked($formdata, 'preterm')) {
            foreach ($this->pretermrevdeps as $keyword) {
                if ($this->is_checked($formdata, 'preterm'.$keyword)) {
                    $preterm[] = $this->get_server_id($keyword);
                }
            }
        }
        if (!empty($preterm)) {
            $topics[] = array('preterm' => $preterm);
        }
        $request['topics'] = $topics;

        if ($this->is_checked($formdata, 'text')) {
            $request['text'] = $formdata['text'];
        } else {
            $request['text'] = '';
        }

        $themes = array();
        foreach ($this->themes as $theme) {
            if ($this->is_checked($formdata, $theme)) {
                $themes[] = $this->get_server_id($theme);
            }
        }
        $request['themes'] = $themes;

        if ($this->is_checked($formdata, 'cme')) {
            $request['cme'] = true;
        } else {
            $request['cme'] = false;
        }

        return $request;
    }

    protected function is_checked($formdata, $field) {
        return (isset($formdata[$field]) and $formdata[$field]);
    }

    protected function get_server_id($keyword) {
        // Edge cases because of server requirements.
        switch ($keyword) {
        case 'practicecounselling':
            return 'practice';
        case 'background':
            return 'science';
        case 'paediatrics':
            return 'paediatrician';
        case 'obstetrics_gynocology':
            return 'gynocologist';
        case 'generalpractice_familymedicine':
            return 'gp';
        case 'specialistotherareas':
            return 'other';
        }

        $id = get_string($keyword, 'mod_enea');
        $pieces = preg_split('/[ ,&\/]+/', $id);
        $id = implode(' ', $pieces);
        $id = strtolower($id);

        return $id;
    }
}
