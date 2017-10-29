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

    public function __construct($formdata, $stage) {
        $data = $formdata;
        $data['stage'] = $stage;
        $this->data = $data;
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
