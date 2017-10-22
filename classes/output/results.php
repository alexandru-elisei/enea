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
 * Renderable for displaying the search results page.
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
 * Search results renderable.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class results implements templatable, renderable {

    protected $data;

    public function __construct($searchresults) {
        $recommendedtitles = array();
        foreach ($searchresults['recommended'] as $coursetitle) {
            $recommendedtitles[$coursetitle] = true;
        }

        $prereqtitles = array();
        $postreqtitles = array();
        $dependencies = array();
        foreach($searchresults['data'] as $course) {
            if (isset($recomendedtitles[$course['title']])) {
                foreach ($course['prerequisites'] as $prereqtitle) {
                    $prereqtitles[$prereqtitle] = true;
                }
                foreach ($course['postrequisites'] as $postreqtitle) {
                    $postreqtitles[$postreqtitle] = true;
                }
            } else {
                foreach ($course['prerequisites'] as $prereqtitle) {
                    if (!isset($recommendedtitles[$prereqtitle])) {
                        // Prerequisite for a postrequisite.
                        $prereqtitles[$prereqtitle] = true;
                    }
                }
                foreach ($course['postrequisites'] as $postreqtitle) {
                    if (!isset($recommendedtitles[$postreqtitle])) {
                        // Postrequisite for a prerequisite.
                        $postreqtitles[$postreqtitle] = true;
                    }
                }
            }
        }

        $data = new stdClass();
        $data->recommended = array();
        $data->prereq = array();
        $data->postreq = array();
        $data->dependencies = array();
        foreach ($searchresults['data'] as $course) {
            if (isset($recomendedtitles[$course['title']])) {
                $data->recommended[] = $course;
            }
        }
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
}
