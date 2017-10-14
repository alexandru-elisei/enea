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
 * File containing the servery search results form.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/outputcomponents.php');

/**
 * Search results form.
 *
 * @package    mod_enea
 * @copyright  2017 Alexandru Elisei
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_enea_search_results_form extends moodleform {

    /**
     * The standard form definiton.
     */
    public function definition () {
        $mform = $this->_form;
        $customdata = $this->_customdata;
        $searchresults = $customdata['searchresults'];

        $mform->addElement('html', '</br>');
        $mform->addElement('html', '<h4 id="mod_enea_form_heading_2">'.get_string('searchresults', 'mod_enea').'</h4>');
        $mform->addElement('html', '</br>');

        if (!$searchresults['success']) {
            $mform->addElement('html', '</br>');
            $mform->addElement('html', '<h6 id="mod_enea_form_heading_2">'.get_string('searcherror', 'mod_enea').': '
                .$searchresults['errorMsg'].'</h6>');
            $mform->addElement('html', '</br>');
            goto exit_label;
        }

        $mform->addElement('html', '</br>');
        $mform->addElement('html', '<h6 id="mod_enea_form_heading_2">'.get_string('selectlessons', 'mod_enea').'.</h6>');
        $mform->addElement('html', '</br>');

        /*
        $table = new html_table();
        $table->head = array('Title', 'Time', 'Link');
        $table->id = 'selectcoursestable';
        $table->attributes['class'] = 'admintable';
        $table->width = 400;
        $table->data = array();

        $checkbox = html_writer::checkbox('name', 1, false, '&ensp;Title lesson 1');
        $time = new html_table_cell('3h20min');
        $link = new html_table_cell(html_writer::link('span', 'sourcetext'));
        $row = new html_table_row(array($checkbox, $time, $link));
        $table->data[] = $row;

        $checkbox = html_writer::checkbox('name', 1, false, '&ensp;Title lesson 2');
        $time = new html_table_cell('3h20min');
        $link = new html_table_cell(html_writer::link('span', 'sourcetext'));
        $row = new html_table_row(array($checkbox, $time, $link));
        $table->data[] = $row;

        $checkbox = html_writer::checkbox('name', 1, false, '&ensp;Title lesson 2');
        $time = new html_table_cell('3h20min');
        $link = new html_table_cell(html_writer::link('span', 'sourcetext'));
        $row = new html_table_row(array($checkbox, $time, $link));
        $table->data[] = $row;

        $mform->addElement('html', html_writer::start_tag('font', array('size' => '10')));
        $mform->addElement('html', html_writer::table($table));
        $mform->addElement('html', html_writer::end_tag('font'));
         */
        //$mform->addElement('html', html_writer::start_tag('div', array('class' => 'col-xs24')));
        //$objs = array();
        //$objs[] = $mform->createElement('html', '<div class="col-xs-10">Title</div>');
        //$objs[] = $mform->createElement('html', '<div class="col-xs-2">Time</div>');
        //$mform->addElement('group', 'typesthemesgroup', '', $objs, array(''), false);
        //$mform->addElement('html', html_writer::end_tag('div'));
        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', '<div class="col-md-2"><dt><u>Title</u></dt></div>');
        $mform->addElement('html', '<div class="col-md-2"><dt><u>Time</u></dt></div>');
        $mform->addElement('html', '</div>');

        $divstart = html_writer::start_tag('div', array('class' => 'col-md-2')).'<span class="text-left">';
        $divend = '</span>'.html_writer::end_tag('div');

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', $divstart);
        $mform->addElement('html', '<label style="margin-bottom:0" name="checkbox" id="checkbox"><input type="checkbox" value="0">&ensp;Option 1</label>');
        $mform->addElement('html', $divend);
        $mform->addElement('html', $divstart.'3h20min'.$divend);
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', $divstart);
        $mform->addElement('html', '<label style="margin-bottom:0" name="checkbox2" id="checkbox2"><input type="checkbox" value="0">&ensp;Option 2</label>');
        $mform->addElement('html', $divend);
        $mform->addElement('html', $divstart.'3h20min'.$divend);
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="row">');
        $mform->addElement('html', $divstart);
        $mform->addElement('html', '<label style="margin-bottom:0" name="checkbox3" id="checkbox3"><input type="checkbox" value="0">&ensp;Option 3</label>');
        $mform->addElement('html', $divend);
        $mform->addElement('html', $divstart.'3h20min'.$divend);
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</div>');

        exit_label:
        if ($customdata) {
            if (isset($customdata['id']) and $customdata['id']) {
                $mform->addElement('hidden', 'id', $customdata['id']);
                $mform->setType('id', PARAM_INT);
            } else if (isset($customdaa['cmid']) and $customdata['cmid']) {
                $mform->addElement('hidden', 'cmid', $customdata['cmid']);
                $mform->setType('cmid', PARAM_INT);
            }
        }

        $mform->addElement('hidden', 'stage', 1);
        $mform->setType('stage', PARAM_INT);
    }
}
