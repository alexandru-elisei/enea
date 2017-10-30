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

use \mod_enea\local\stage;
use \mod_enea\local\helper;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

const REFRESH_TIME = 3600;

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
    print_error(get_string('missingidandcmid', 'mod_enea'));
}

require_login($course, true, $cm);

$userid = $USER->id;
$pagecontext = $id ? array('id' => $id) : array('cmid' => $e);
$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/enea/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

print('<br>');
print('<br>');
print('<br>');
function print_deb($stuff) {
    print_r($stuff);
    print('<br>');
}

$mform = null;

$stage = $DB->get_record('enea_users', array('userid' => $userid), 'stage', IGNORE_MISSING);
if (!$stage) {
    $record = new stdClass();
    $record->userid = $userid;
    $record->stage = stage::SELECT_COURSES;
    $DB->insert_record('enea_users', $record, false);
    $stage = stage::SELECT_COURSES;
} else {
    $stage = $stage->stage;
}

print_deb('stage = '.$stage);

$reset = false;
if ($reset) {
    $DB->set_field('enea_users', 'stage', stage::SELECT_KEYWORDS, array('userid' => $userid));
    $stage = stage::SELECT_KEYWORDS;
}

if ($stage == stage::SELECT_KEYWORDS) {
    $formdata = $_POST ? $_POST : $_GET;
    if (!isset($formdata['searchbutton'])) {
        $mform = new mod_enea_selection_form(null, $pagecontext);
    } else {
        $data = $formdata;
        $data = array_merge($formdata, $pagecontext);
        $select = new \mod_enea\output\select($data, stage::SELECT_KEYWORDS);

        $task = new \mod_enea\task\get_courses();
        $taskargs = array(
            'userid' => $userid,
            'keywords' => $select->export_for_server(),
        );
        $task->set_custom_data($taskargs);
        $DB->set_field('enea_users', 'stage', stage::WAITING_FOR_RESULTS, array('userid' => $userid));

        \core\task\manager::queue_adhoc_task($task);

        $data = $pagecontext;
        $renderer = new \mod_enea\output\waiting($data, stage::WAITING_FOR_RESULTS);
        $template = 'mod_enea/waiting';
        $PAGE->set_periodic_refresh_delay(REFRESH_TIME);
    }

} else if ($stage == stage::WAITING_FOR_RESULTS) {
    $data = $pagecontext;
    $renderer = new \mod_enea\output\waiting($data, stage::WAITING_FOR_RESULTS);
    $template = 'mod_enea/waiting';

    $PAGE->set_periodic_refresh_delay(REFRESH_TIME);

} else if ($stage == stage::SELECT_COURSES) {
    $searchresults = $DB->get_record('enea_users', array('userid' => $userid), 'searchresults', MUST_EXIST);
    $searchresults = json_decode($searchresults->searchresults, true);

    if (!isset($searchresults['recommended'])) {
        $data = new stdClass();
        $data->errormsg = get_string('missingrecommended', 'mod_enea');
        $data = array_merge($data, $pagecontext);
        $renderer = new \mod_enea\output\error($data);
        $template = 'mod_enea/error';

        // Reset the selection.
        $DB->set_field('enea_users', 'stage', stage::SELECT_KEYWORDS, array('userid' => $userid));

        goto render;
    }

    if (!isset($searchresults['data'])) {
        $data = new stdClass();
        $data->errormsg = get_string('missingdata', 'mod_enea');
        $data = array_merge($data, $pagecontext);
        $renderer = new \mod_enea\output\error($data);
        $template = 'mod_enea/error';

        // Reset the selection.
        $DB->set_field('enea_users', 'stage', stage::SELECT_KEYWORDS, array('userid' => $userid));

        goto render;
    }

    if (!$searchresults['success']) {
        $data = new stdClass();
        $data->errormsg = $searchresults['errorMsg'];
        $data = array_merge($data, $pagecontext);
        $renderer = new \mod_enea\output\error($data);
        $template = 'mod_enea/error';

        // Reset the selection.
        $DB->set_field('enea_users', 'stage', stage::SELECT_KEYWORDS, array('userid' => $userid));

        goto render;
    }

    $data = array_merge($searchresults, $pagecontext);
    $renderer = new \mod_enea\output\results($data);
    $template = 'mod_enea/search_results';
}

echo $OUTPUT->header();

render:
if ($mform) {
    $mform->display();
} else {
    echo $OUTPUT->render_from_template($template, $renderer->export_for_template($OUTPUT));
}

echo $OUTPUT->footer();
