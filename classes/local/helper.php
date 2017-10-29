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
 * File containing helper functions.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_enea\local;

defined('MOODLE_INTERNAL') || die();

class helper {

    /**
     * Convert the formdata into the format expected by the courses server.
     *
     * @param array $formdata The keywords form data.
     * @return array The keywords in the format expected by the server.
     */
    static public function export_to_server($formdata) {
        $request = array();

        $formdata = (array)$formdata;
        $request['cme'] = isset($formdata['cme']) and $formdata['cme'];

        $expertise = array();
        $medicine = array();
        if (isset($formdata['medicine']) and $formdata['medicine']) {
            foreach ($this->medicinerevdeps as $keyword) {
                if (isset($formdata[$keyword]) and $formdata[$keyword]) {
                    $medicine[] = $keyword;
                }
            }
        }
        if (!empty($medicine)) {
            $expertise[] = array('medicine' => $medicine);
        }
        if (isset($formdata['nursing']) and $formdata['nursing']) {
            $expertise[] = 'nursing';
        }
        if (isset($formdata['nutrition']) and $formdata['nutrition']) {
            $expertise[] = 'nutrition';
        }
        if (!empty($expertise)) {
            $request['expertise'] = $expertise;
        }

        $topics = array();
        $breastfeeding = array();
        if (isset($formdata['breastfeeding']) and $formdata['breastfeeding']) {
            foreach ($this->bfrevdeps as $keyword) {
                if (isset($formdata['bf'.$keyword]) and $formdata['bf'.$keyword]) {
                    $breastfeeding[] = $keyword;
                }
            }
        }
        if (!empty($breastfeeding)) {
            $topics[] = array('breastfeeding' => $breastfeeding);
        }

        $breastmilksubst = array();
        if (isset($formdata['breastmilksubst']) and $formdata['breastmilksubst']) {
            foreach ($this->bmsrevdeps as $keyword) {
                if (isset($formdata['bms'.$keyword]) and $formdata['bms'.$keyword]) {
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

        if (isset($formdata['text']) and $formdata['text']) {
            $request['text'] = $formdata['text'];
        }

        $themes = array();
        foreach ($this->typesthemes as $theme) {
            if (isset($formdata['typesthemes'.$theme]) and $formdata['typesthemes'.$theme]) {
                $themes[] = $theme;
            }
        }
        if (!empty($themes)) {
            $request['themes'] = $themes;
        }

        return $request;
    }
}
