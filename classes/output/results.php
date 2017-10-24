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
        $searchresults = (array)$searchresults;
        $recommendedtitles = array();
        foreach ($searchresults['recommended'] as $coursetitle) {
            $recommendedtitles[$coursetitle] = true;
        }

        $prereqtitles = array();
        $postreqtitles = array();
        $dependencies = array();
        $coursedeps = array();
        foreach($searchresults['data'] as $key => $course) {

            // The 'name' field will identify the checkbox associated with the
            // course.
            $name = str_replace(' ', '_', $course['title']);
            $searchresults['data'][$key]['name'] = $name;

            $searchresults['data'][$key]['time'] = $this->timestr($course['time']);

            if (!empty($course['prerequisites'])) {
                // Mark the dependency.
                if (!isset($coursedeps[$course['title']])) {
                    $coursedeps[$course['title']] = array();
                }
                $coursedeps[$course['title']] = array_merge(
                    $coursedeps[$course['title']],
                    $course['prerequisites']
                );

                foreach ($course['prerequisites'] as $prereqtitle) {
                    if (!isset($recommendedtitles[$prereqtitle])) {
                        $prereqtitles[$prereqtitle] = true;
                    }
                }
            }

            foreach ($course['postrequisites'] as $postreqtitle) {
                // The postrequisite course will depend on the current course.
                if (!isset($coursedeps[$postreqtitle])) {
                    $coursedeps[$postreqtitle] = array();
                }
                $coursedeps[$postreqtitle][] = $course['title'];

                $postreqtitles[$postreqtitle] = true;
            }
        }

        $data = new stdClass();
        $data->recommended = array();
        $data->prereq = array();
        $data->postreq = array();
        foreach ($searchresults['data'] as $course) {
            if (isset($recommendedtitles[$course['title']])) {
                $data->recommended[] = $course;
            } else if (isset($prereqtitles[$course['title']])) {
                $data->prereq[] = $course;
            } else if (isset($postreqtitles[$course['title']])) {
                $data->postreq[] = $course;
            }
        }

        $data->dependencies = array();
        foreach ($coursedeps as $title => $_) {
            $visited = array();
            $data->dependencies[] = array(
                'depname' => str_replace(' ', '_', $title),
                'deps' => $this->get_all_deps($title, $coursedeps, $visited)
            );
        }

        $data->dependencies = json_encode($data->dependencies);

        $this->data = $data;

        print('<br>');
        print_r($data->dependencies);
        print('<br>');
    }

    /**
     * Get all the dependencies, iregardless of depth, for a course.
     *
     * @param str $title The course title.
     * @param array $coursedeps All the dependencies.
     * @param array $visited List of the visited courses in the tranversal.
     * @return array Numerically indexed list of all the dependencies.
     */
    protected function get_all_deps($title, $coursedeps, $visited) {
        $ret = array();
        if (isset($coursedeps[$title])) {
            foreach ($coursedeps[$title] as $deptitle) {
                if (!isset($visited[$deptitle])) {
                    $visited[$deptitle] = true;
                    $ret[] = str_replace(' ', '_', $deptitle);
                    $ret = array_merge($ret, $this->get_all_deps($deptitle, $coursedeps, $visited));
                }
            }
        }

        return $ret;
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

    /**
     * Convert the time into a human-readable format.
     *
     * @param int $time The duration, in seconds.
     * @return str The human-readable time.
     */
    protected function timestr($time) {
        $ret = '';
        $days = floor($time / 86400);
        if ($days > 0) {
            $ret = $ret.$days.'d';
        }

        $time = $time % 86400;
        $hours = floor($time / 3600);
        if ($hours) {
            $ret = $ret.$hours.'h';
        }

        $time = $time % 3600;
        $minutes = floor($time / 60);
        if ($minutes) {
            $ret = $ret.$minutes.'m';
        }

        $seconds = $time % 60;
        if ($seconds or !$ret) {
            $ret = $ret.$seconds.'s';
        }

        return $ret;
    }
}
