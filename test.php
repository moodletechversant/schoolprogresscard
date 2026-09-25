<?php
require(__DIR__.'/../../config.php');
global $DB,$CFG;
// $template = file_get_contents($CFG->dirroot . '/local/class/template/demo.mustache');
//$context = context_system::instance();
require_login();

// $mustache = new Mustache_Engine();
if(isset($_POST['exam_id'])){
    $exam_id= $_POST['exam_id'];
    $idofchild=$_POST['idofchild'];
    $division_id=$_POST['division_id'];

    $subject=$DB->get_records_sql("SELECT course_id FROM {subject} WHERE sub_division=$division_id");
    foreach($subject as $sub){
     $course_id=$sub->course_id;
     $coursename=$DB->get_records_sql("SELECT * FROM {course} WHERE id=$course_id");
     $quiz[]=$DB->get_records_sql("SELECT id FROM {quiz} WHERE course IN ($course_id)");
    }
   $quiz_ids = array();
   foreach ($quiz as $records) {
      foreach ($records as $record) {
        $quiz_ids[] = $record->id; // Add the 'id' to the $quiz_ids array
      }
    }
    //  print_r($quiz_ids);exit();
   $typeofexamid[]=$DB->get_records_sql("SELECT quiz_id FROM {custom_quiz} WHERE type_id=$exam_id");
   $type = array();
   foreach ($typeofexamid as $records) {
      foreach ($records as $record) {
       $type[] = $record->quiz_id; // Add the 'id' to the $quiz_ids array
      }
    }
$commonElements = array_intersect($quiz_ids, $type);

if(!empty($commonElements)){
$var.='
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
  
foreach($commonElements as $elements){
 $idofquiz=$elements;
 $quizdetails = $DB->get_record_sql("SELECT course FROM {quiz} WHERE id=$idofquiz");
 $course_name = $DB->get_record_sql("SELECT fullname FROM {course} WHERE id=$quizdetails->course");
 $namecourse=$course_name->fullname;

$finalquiz = $DB->get_record_sql("SELECT q.sumgrades, q.grade, qa.sumgrades AS qa_sumgrades,
qg.grade AS qg_grade FROM {quiz} AS q
INNER JOIN {quiz_attempts} AS qa ON q.id = qa.quiz 
INNER JOIN {quiz_grades} AS qg ON q.id = qg.quiz 
WHERE q.id=$idofquiz AND qa.userid=$idofchild AND qg.userid=$idofchild");

$var.='<tbody>
<tr>
  <td>'.$namecourse.'</td>
  <td>'.$finalquiz->qa_sumgrades.'</td>
  <td>'.$finalquiz->sumgrades.'</td>
  <td>'.$finalquiz->qg_grade.'</td>
  <td>'.$finalquiz->grade.'</td>
</tr>             
</tbody>';

}
$var.='</table>
</div>';
}
echo $var;
}

?>