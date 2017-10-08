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

if ($stage == 0) {
    $waiting = $DB->get_record('enea', array('id' => $cm->instance), 'waitingresponse', MUST_EXIST);
    $waiting = $waiting->waitingresponse;
    if ($waiting) {
        $mform = new mod_enea_waiting_form(null, $customdata);
        $PAGE->set_periodic_refresh_delay(2);
    } else {
        $mform = new mod_enea_selection_form(null, $customdata);
        $formdata = $mform->get_data();
        if (!empty($formdata)) {
            $selection = $mform->get_selection($formdata);

            $task = new \mod_enea\task\get_courses();
            $taskargs = array();
            $taskargs['id'] = $cm->instance;
            $taskargs['selection'] = $selection;
            $task->set_custom_data($taskargs);
            $DB->set_field('enea', 'waitingresponse', 1, array('id' => $cm->instance));
            \core\task\manager::queue_adhoc_task($task);

            $mform = new mod_enea_waiting_form(null, $customdata);
            $PAGE->set_periodic_refresh_delay(2);
        }
    }
} else if ($stage == 1) {
    $waiting = $DB->get_record('enea', array('id' => $cm->instance), 'waitingresponse', MUST_EXIST);
    $waiting = $waiting->waitingresponse;
    if (!$waiting) {
        $PAGE->set_periodic_refresh_delay(0);
        $courses = $DB->get_record('enea', array('id' => $cm->instance), 'selectedcourses', MUST_EXIST);
        $DB->set_field('enea', 'selectedcourses', '', array('id' => $cm->instance));
        $mform = new mod_enea_selection_form(null, $customdata);

        for ($i = 0; $i < 10; $i++) {
            print('<br/>');
        }
        print($courses);

    } else {
        $mform = new mod_enea_waiting_form(null, $customdata);
        $PAGE->set_periodic_refresh_delay(2);
    }
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
