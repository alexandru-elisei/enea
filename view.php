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

const REFRESH_TIME = 30;
const DEBUG = 0;

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
$formdata = $_POST ? $_POST : $_GET;

$PAGE->set_url('/mod/enea/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

if (DEBUG == 1) {
    print('<br>');
    print('<br>');
    print('<br>');
}
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

if (DEBUG == 1) {
    print_deb('stage = '.$stage);
}

// Go back to the beginning.
if ($stage == stage::FINISHED) {
    $DB->set_field('enea_users', 'stage', stage::SELECT_KEYWORDS, array('userid' => $userid));
    $stage = stage::SELECT_KEYWORDS;
}

if ($stage == stage::SELECT_KEYWORDS) {

    if (!isset($formdata['searchbutton'])) {
        $data = $pagecontext;
        $renderer = new \mod_enea\output\select($data);
        $template = 'mod_enea/select';

    } else {
        $data = $formdata;
        $data = array_merge($formdata, $pagecontext);
        $select = new \mod_enea\output\select($data);

        $task = new \mod_enea\task\get_courses();
        $taskargs = array(
            'userid' => $userid,
            'keywords' => $select->export_for_server(),
        );

        if (DEBUG == 1) {
            print_deb(json_encode($taskargs['keywords']));
        }

        $task->set_custom_data($taskargs);
        $DB->set_field('enea_users', 'stage', stage::WAITING_FOR_RESULTS, array('userid' => $userid));

        \core\task\manager::queue_adhoc_task($task);

        $data = $pagecontext;
        $renderer = new \mod_enea\output\waiting($data);
        $template = 'mod_enea/waiting';
        $PAGE->set_periodic_refresh_delay(REFRESH_TIME);
    }

} else if ($stage == stage::WAITING_FOR_RESULTS) {
    $data = $pagecontext;
    $renderer = new \mod_enea\output\waiting($data);
    $template = 'mod_enea/waiting';

    $PAGE->set_periodic_refresh_delay(REFRESH_TIME);

} else if ($stage == stage::SELECT_COURSES) {

    if (isset($formdata['finishbutton'])) {
        $data = $pagecontext;
        $renderer = new \mod_enea\output\success($data);
        $template = 'mod_enea/success';

        // Course enrollment finished for this user.
        $DB->set_field('enea_users', 'stage', stage::FINISHED, array('userid' => $userid));

        goto render_page;
    }

    if (isset($formdata['backbutton'])) {
        $data = $pagecontext;
        $renderer = new \mod_enea\output\select($data);
        $template = 'mod_enea/select';

        // Clear search results.
        $DB->set_field('enea_users', 'searchresults', '', array('userid' => $userid));
        // Reset the process.
        $DB->set_field('enea_users', 'stage', stage::SELECT_KEYWORDS, array('userid' => $userid));
        $stage = stage::SELECT_KEYWORDS;

        goto render_page;
    }


    $searchresults = $DB->get_record('enea_users', array('userid' => $userid), 'searchresults', MUST_EXIST);
    if (DEBUG == 1) {
        print_deb($searchresults->searchresults);
    }

    //$searchresults->searchresults = '{"data":{"lessons":[{"id":"3.3.3","title":"Optional Components of Infant Formula","uri":"here1","time":10,"similarityScore":0.7264344035877423,"prerequisites":["3.3.1"],"postrequisites":["2.1.2"]},{"id":"2.1.2","title":"Composition of Human Milk","uri":"here2","time":15,"similarityScore":0.7229355471400114,"prerequisites":["2.1.1"],"postrequisites":[]},{"id":"3.3.2","title":"Major Components of Infant Formula","uri":"here3","time":15,"similarityScore":0.7116147619386389,"prerequisites":["3.3.1"],"postrequisites":["3.3.3"]},{"id":"3.4.2","title":"Correct Formula Preparation","uri":"here4","time":15,"similarityScore":0.6896124373988142,"prerequisites":["3.4.1"],"postrequisites":[]},{"id":"3.4.3","title":"Correct Bottle-Feeding Practice","uri":"here5","time":10,"similarityScore":0.7088258492646591,"prerequisites":[],"postrequisites":["3.4.4"]},{"id":"3.2.2","title":"Non-Medical Reasons for Formula Feeding","uri":"here6","time":15,"similarityScore":0.6753481420126302,"prerequisites":[],"postrequisites":["3.2.3"]},{"id":"3.1.2","title":"Case Study:Hypernatremia","uri":"","time":12,"similarityScore":0.9831887343729273,"prerequisites":[],"postrequisites":[]},{"id":"3.4.4","title":"Weaning From Bottle-Feeding","uri":"here7","time":10,"similarityScore":0.7632517493953542,"prerequisites":[],"postrequisites":["3.4.3"]},{"id":"3.4.1","title":"Preparation of Infant Formula","uri":"here8","time":12,"similarityScore":0.735385076632274,"prerequisites":["3.1.2"],"postrequisites":["3.4.2"]}],"recommended":["3.3.3","2.1.2","3.3.2","3.4.4","3.1.2","3.4.2","3.4.3","3.2.2","3.4.1"],"time":114,"cmePoints":1.9},"success":true,"errorMsg":""}';
    $searchresults = json_decode($searchresults->searchresults, true);

    if (!$searchresults['success']) {
        $data = array('errormsg' => $searchresults['errorMsg']);
        $data = array_merge($data, $pagecontext);
        $renderer = new \mod_enea\output\error($data);
        $template = 'mod_enea/error';

        // Reset the selection.
        $DB->set_field('enea_users', 'stage', stage::SELECT_KEYWORDS, array('userid' => $userid));

        goto render_page;
    }

    if (!isset($searchresults['data']) or empty($searchresults['data'])) {
        $data = array('errormsg' => get_string('missingdata', 'mod_enea'));
        $data = array_merge($data, $pagecontext);
        $renderer = new \mod_enea\output\error($data);
        $template = 'mod_enea/error';

        // Reset the selection.
        $DB->set_field('enea_users', 'stage', stage::SELECT_KEYWORDS, array('userid' => $userid));

        goto render_page;
    }

    if (!isset($searchresults['data']['recommended']) or empty($searchresults['data']['recommended'])) {
        $data = array('errormsg' => get_string('missingrecommended', 'mod_enea'));
        $data = array_merge($data, $pagecontext);
        $renderer = new \mod_enea\output\error($data);
        $template = 'mod_enea/error';

        // Reset the selection.
        $DB->set_field('enea_users', 'stage', stage::SELECT_KEYWORDS, array('userid' => $userid));

        goto render_page;
    }

    $data = array_merge($searchresults, $pagecontext);
    $renderer = new \mod_enea\output\results($data);
    $template = 'mod_enea/search_results';
}

render_page:
echo $OUTPUT->header();

if ($mform) {
    $mform->display();
} else {
    echo $OUTPUT->render_from_template($template, $renderer->export_for_template($OUTPUT));
}

echo $OUTPUT->footer();
