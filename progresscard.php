<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Progress card of a student: class details and the marks per type of exam.
 *
 * @package     local_progresscard
 * @copyright   2022 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/mustache/src/Mustache/Autoloader.php');
Mustache_Autoloader::register();

global $CFG, $USER;

$context = context_system::instance();
require_login();
$template = file_get_contents($CFG->dirroot . '/local/progresscard/template/progresscard.mustache');
$linkurl = new moodle_url('/local/subject/progresscard.php');
// Print the page header.
$csslink = new moodle_url('/local/css/style.css');
$PAGE->set_context($context);
$PAGE->set_url($linkurl);
$PAGE->set_title($linktext);
$PAGE->navbar->add('progresscard', new moodle_url($CFG->wwwroot . '/local/progresscard/progresscard.php'));
$userid = optional_param('id', 0, PARAM_INT);
echo $OUTPUT->header();
$studentdetails = [];
$studentdetails = $DB->get_record_sql("SELECT user_id,s_ftname,s_mlname,s_lsname FROM {student} WHERE user_id=$userid");

$sname = $studentdetails->s_ftname;
$smname = $studentdetails->s_mlname;
$slame = $studentdetails->s_lsname;
$fname = $sname . " " . $smname . " " . $slame;
$val1 = $studentdetails->user_id;

$rec1 = $DB->get_record_sql("SELECT d.div_class,d.id, d.div_name, d.div_teacherid, t.t_fname, c.academic_id, c.class_name
    FROM {student_assign} sa
    INNER JOIN {division} d ON sa.s_division = d.id
    INNER JOIN {teacher} t ON d.div_teacherid = t.user_id
    INNER JOIN {class} c ON d.div_class = c.id
    WHERE sa.user_id = $val1");
if (!empty($rec1)) {
    $isassignedstudent = true;

    $classname = $rec1->class_name;
    $division = $rec1->div_name;
    $divisionid = $rec1->id;
    $classteacher = $rec1->t_fname;
    $academicid = $rec1->academic_id;

    $data = $DB->get_record_sql("SELECT * FROM {academic_year} WHERE id=$academicid");
    $startyear = $data->start_year;
    $endyear = $data->end_year;
    $startyear1 = date("d-m-Y", $startyear);
    $endyear1 = date("d-m-Y", $endyear);
    $academicyear = $startyear1 . "  to  " . $endyear1;

    $typeofexam = $DB->get_records('type_of_exam');

    $options1 = [];
    $options1[] = ['value' => '', 'label' => '---- Select Type of Exam ----'];
    foreach ($typeofexam as $nameofexam) {
        $examname = $nameofexam->typeofexam;
        $typeid = $nameofexam->id;
        $cid = $nameofexam->course_id;
        $options1[] = ['value' => $typeid, 'label' => $examname];
    }
    $templatedata = [
        'startYearOptions' => $options1,
    ];
}

$mustache = new Mustache_Engine();
$output = $mustache->render($template, [
    'is_assigned_student' => $isassignedstudent,
    'division_id' => $divisionid,
    'childid' => $userid,
    'css_link' => $csslink,
    'templateData' => $templatedata,
    'academic_year' => $academicyear,
    'fname' => $fname,
    'classname' => $classname,
    'division' => $division,
    'classteacher' => $classteacher,
]);
echo $output;

echo $OUTPUT->footer();
