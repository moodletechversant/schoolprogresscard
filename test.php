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
 * AJAX endpoint of the progress card: returns the marks table of a student for one type of exam.
 *
 * @package     local_progresscard
 * @copyright   2022 Your Name <you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
global $DB, $CFG;
require_login();

if (isset($_POST['exam_id'])) {
    $examid = $_POST['exam_id'];
    $idofchild = $_POST['idofchild'];
    $divisionid = $_POST['division_id'];

    $subject = $DB->get_records_sql("SELECT course_id FROM {subject} WHERE sub_division=$divisionid");
    foreach ($subject as $sub) {
        $courseid = $sub->course_id;
        $coursename = $DB->get_records_sql("SELECT * FROM {course} WHERE id=$courseid");
        $quiz[] = $DB->get_records_sql("SELECT id FROM {quiz} WHERE course IN ($courseid)");
    }
    $quizids = [];
    foreach ($quiz as $records) {
        foreach ($records as $record) {
            $quizids[] = $record->id; // Add the 'id' to the $quizids array.
        }
    }
    $typeofexamid[] = $DB->get_records_sql("SELECT quiz_id FROM {custom_quiz} WHERE type_id=$examid");
    $type = [];
    foreach ($typeofexamid as $records) {
        foreach ($records as $record) {
            $type[] = $record->quiz_id; // Add the 'quiz_id' to the $type array.
        }
    }
    $commonelements = array_intersect($quizids, $type);

    if (!empty($commonelements)) {
        $var .= '
<div class="row" style="    padding: 10px;
margin-top: 30px;">
    <table class="table table-striped" style="border: 1px solid #b5b5b5;">
        <thead>
          <tr>
            <th scope="col">Subject</th>
            <th scope="col">Mark</th>
            <th scope="col">Total Marks</th>
            <th scope="col">Grade</th>
            <th scope="col">Total Grade</th>
          </tr>
        </thead>';

        foreach ($commonelements as $elements) {
            $idofquiz = $elements;
            $quizdetails = $DB->get_record_sql("SELECT course FROM {quiz} WHERE id=$idofquiz");
            $coursenamerecord = $DB->get_record_sql("SELECT fullname FROM {course} WHERE id=$quizdetails->course");
            $namecourse = $coursenamerecord->fullname;

            $finalquiz = $DB->get_record_sql("SELECT q.sumgrades, q.grade, qa.sumgrades AS qa_sumgrades,
qg.grade AS qg_grade FROM {quiz} AS q
INNER JOIN {quiz_attempts} AS qa ON q.id = qa.quiz
INNER JOIN {quiz_grades} AS qg ON q.id = qg.quiz
WHERE q.id=$idofquiz AND qa.userid=$idofchild AND qg.userid=$idofchild");

            $var .= '<tbody>
<tr>
  <td>' . $namecourse . '</td>
  <td>' . $finalquiz->qa_sumgrades . '</td>
  <td>' . $finalquiz->sumgrades . '</td>
  <td>' . $finalquiz->qg_grade . '</td>
  <td>' . $finalquiz->grade . '</td>
</tr>
</tbody>';
        }
        $var .= '</table>
</div>';
    }
    echo $var;
}
