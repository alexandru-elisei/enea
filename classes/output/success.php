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
 * Renderable for displaying the success page.
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
 * Success renderable.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class success implements templatable, renderable {

    protected $data;

    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Export this class data as a flat list for rendering in a template.
     *
     * @param bootstrap_renderer $output The current page renderer.
     * @return stdClass Flat list of exported data.
     */
    public function export_for_template(renderer_base $output) {
        return (object)$this->data;
    }
}
