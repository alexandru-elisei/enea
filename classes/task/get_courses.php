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
 * File containing the course selection form.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_enea\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Get courses task class.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_courses extends \core\task\adhoc_task {

    public function execute() {
        global $DB;

        $data = (array)$this->get_custom_data();
        $userid = $data['userid'];
        $keywords = json_encode($data['keywords']);

        //$searchresults = '{"data":[{"title":"Lesson title 1","link":"lesson-title-1","similarityScore":0.41,"time":465,"prerequisites":["Pre1","Pre2"],"postrequisites":["Post1"]},{"title":"Lesson title 2","link":"lesson-title-2","similarityScore":0.42,"time":7200,"prerequisites":["Pre2"],"postrequisites":[]},{"title":"Pre1","link":"pre-lesson-title-1","similarityScore":0.32,"time":1345,"prerequisites":[],"postrequisites":[]},{"title":"Pre2","link":"pre-lesson-title-2","similarityScore":0.76,"time":1200,"prerequisites":["Pre1"],"postrequisites":[]},{"title":"Post1","link":"post-lesson-title-2","similarityScore":0.5,"time":50,"prerequisites":[],"postrequisites":[]}],"recommended":["Lesson title 1","Lesson title 2"],"success":true,"errorMsg":""}';

        $url = 'http://readerbench.com/api/mass-customisation';
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $keywords);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $error = curl_error($ch);
        if ($error) {
            $searchresults = array(
                'success' => false,
                'errorMsg' => $error,
            );
            $searchresults = json_encode($searchresults);
        } else {
            $searchresults = curl_exec($ch);
            if (!$searchresults) {
                $searchresults = array(
                    'success' => false,
                    'errorMsg' => 'Empty response received from server',
                );
                $searchresults = json_encode($searchresults);
            }
        }
        curl_close($ch);

        $DB->set_field('enea_users', 'searchresults', $searchresults, array('userid' => $userid));
        $DB->set_field('enea_users', 'stage', 2, array('userid' => $userid));
    }
}
