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
        $searchresults = $searchresults;
        $recommendedtitles = array();
        foreach ($searchresults['data']['recommended'] as $id) {
            $recommendedtitles[$id] = true;
        }

        /*
         * Eliminate dependencies that aren't present in the reply - temporary
         * measure because we don't have all the course definitions.
         */
        $exists = array();
        foreach ($searchresults['data']['lessons'] as $course) {
            $exists[$course['id']] = true;
        }

        // Remove any recommended course that don't exist in the reply.
        foreach ($exists as $id => $_) {
            if (!isset($recommendedtitles[$id])) {
                unset($recommendedtitles[$id]);
            }
        }

        // Remove any pre- and post- courses that don't exist in the reply.
        foreach ($searchresults['data']['lessons'] as $key => $course) {
            $post = array();
            foreach ($course['postrequisites'] as $id) {
                if (isset($exists[$id]))  {
                    $post[] = $id;
                }
            }
            $searchresults['data']['lessons'][$key]['postrequisites'] = $post;

            $pre = array();
            foreach ($course['prerequisites'] as $id) {
                if (isset($exists[$id]))  {
                    $pre[] = $id;
                }
            }
            $searchresults['data']['lessons'][$key]['prerequisites'] = $pre;
        }

        $prereqtitles = array();
        $postreqtitles = array();
        $dependencies = array();
        $coursedeps = array();
        foreach($searchresults['data']['lessons'] as $key => $course) {

            $searchresults['data']['lessons'][$key]['time'] = $this->timestr($course['time']);
            $searchresults['data']['lessons'][$key]['title'] = $course['id'].' '.$course['title'];

            if (!empty($course['prerequisites'])) {
                // Mark the dependency.
                if (!isset($coursedeps[$course['id']])) {
                    $coursedeps[$course['id']] = array();
                }
                $coursedeps[$course['id']] = array_merge(
                    $coursedeps[$course['id']],
                    $course['prerequisites']
                );

                foreach ($course['prerequisites'] as $prereqid) {
                    if (!isset($recommendedtitles[$prereqid])) {
                        $prereqtitles[$prereqid] = true;
                    }
                }
            }

            foreach ($course['postrequisites'] as $postreqid) {
                // The postrequisite course will depend on the current course.
                if (!isset($coursedeps[$postreqid])) {
                    $coursedeps[$postreqid] = array();
                }
                $coursedeps[$postreqid][] = $course['id'];
                $postreqtitles[$postreqid] = true;
            }
        }

        $data = array(
            'recommended'   => array(),
            'prereq'        => array(),
            'postreq'       => array()
        );

        foreach ($searchresults['data']['lessons'] as $course) {
            if (isset($recommendedtitles[$course['id']])) {
                $data['recommended'][] = $course;
            } else if (isset($prereqtitles[$course['id']])) {
                $data['prereq'][] = $course;
            } else if (isset($postreqtitles[$course['id']])) {
                $data['postreq'][] = $course;
            }
        }

        // Create the list of all direct dependencies.
        $data['directdeps'] = array();
        foreach ($coursedeps as $id => $_) {
            $visited = array();
            $data['directdeps'][$id] = $this->get_all_deps($id, $coursedeps, $visited);
        }

        // Create the list of all reverse dependencies.
        $data['reversedeps'] = array();
        foreach ($data['directdeps'] as $id => $deps) {
            foreach ($deps as $depid) {
                if (!isset($data['reversedeps'][$depid])) {
                    $data['reversedeps'][$depid] = array();
                }
                $data['reversedeps'][$depid][] = $id;
            }
        }

        $data['directdeps'] = json_encode($data['directdeps']);
        $data['reversedeps'] = json_encode($data['reversedeps']);

        if (!empty($data['prereq'])) {
            $data['has_prereq'] = true;
        }
        if (!empty($data['postreq'])) {
            $data['has_postreq'] = true;
        }

        if (isset($searchresults['id'])) {
            $data['id'] = $searchresults['id'];
        } else {
            $data['cmid'] = $searchresults['cmid'];
        }

        $data['time'] = $this->timestr($searchresults['data']['time']);
        if ($searchresults['data']['cmePoints']) {
            $data['cmepoints'] = round($searchresults['data']['cmePoints'], 2);
        }

        $this->data = $data;
    }

    /**
     * Get all the dependencies, iregardless of depth, for a course.
     *
     * @param str $id The course id.
     * @param array $coursedeps All the dependencies.
     * @param array $visited List of the visited courses in the tranversal.
     * @return array Numerically indexed list of all the dependencies.
     */
    protected function get_all_deps($id, $coursedeps, $visited) {
        $ret = array();
        if (isset($coursedeps[$id])) {
            foreach ($coursedeps[$id] as $depid) {
                if (!isset($visited[$depid])) {
                    $visited[$depid] = true;
                    $ret[] = $depid;
                    $ret = array_merge($ret, $this->get_all_deps($depid, $coursedeps, $visited));
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
        return (object)$this->data;
    }

    /**
     * Convert the time into a human-readable format.
     *
     * @param int $time The duration, in seconds.
     * @return str The human-readable time.
     */
    protected function timestr($time) {
        $ret = '';
        $days = floor($time / (60*24));
        if ($days > 0) {
            $ret = $ret.$days.'d';
        }

        $time = $time % (60*24);
        $hours = floor($time / 60);
        if ($hours or $ret) {
            $ret = $ret.$hours.'h';
        }

        $minutes = $time % 60;
        $ret = $ret.$minutes.'m';

        return $ret;
    }
}
