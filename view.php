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
 * Prints an instance of mod_enea.
 *
 * @package     mod_enea
 * @copyright   2017 Alexandru Elisei <alexandru.elisei@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$e  = optional_param('cmid', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('enea', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('enea', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($e) {
    $moduleinstance = $DB->get_record('enea', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('enea', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', mod_enea));
}

require_login($course, true, $cm);

$stage = optional_param('hidden', 0, PARAM_INT);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/enea/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

if ($id) {
    $customdata = array('id' => $id);
} else {
    $customdata = array('cmid' => $e);
}

$searchresults = '{"data":[{"title":"Lesson title 1","link":"lesson-title-1","similarityScore":0.41,"time":465,"prerequisites":["Pre1","Pre2"],"postrequisites":["Post1"]},{"title":"Lesson title 2","link":"lesson-title-2","similarityScore":0.42,"time":7200,"prerequisites":["Pre2"],"postrequisites":[]},{"title":"Pre1","link":"pre-lesson-title-1","similarityScore":0.32,"time":1345,"prerequisites":[],"postrequisites":[]},{"title":"Pre2","link":"pre-lesson-title-2","similarityScore":0.76,"time":1200,"prerequisites":["Pre1"],"postrequisites":[]},{"title":"Post1","link":"post-lesson-title-2","similarityScore":0.5,"time":50,"prerequisites":[],"postrequisites":[]}],"recommended":["Lesson title 1","Lesson title 2"],"success":true,"errorMsg":""}';
$searchresults = json_decode($searchresults, true);
if (!isset($searchresults['recommended'])) {
    // Throw error here.
}
if (!isset($searchresults['data'])) {
    // Throw error here.
}
if (!$searchresults['success']) {
    // Throw error here.
}

echo $OUTPUT->header();

if (isset($customdata['id'])) {
    $data->id = $customdata['id'];
} else {
    $data->cmid = $customdata['cmid'];
}
//print_r($_POST);
//print_r($_GET);
$results = new \mod_enea\output\results($searchresults);
echo $OUTPUT->render_from_template('mod_enea/search_results', $results->export_for_template($OUTPUT));

echo $OUTPUT->footer();
