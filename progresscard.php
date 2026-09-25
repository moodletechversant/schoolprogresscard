<?php
require(__DIR__.'/../../config.php');
require_once($CFG->libdir . '/mustache/src/Mustache/Autoloader.php');
Mustache_Autoloader::register();

global $CFG,$USER;

$context = context_system::instance();
require_login();
$template = file_get_contents($CFG->dirroot . '/local/progresscard/template/progresscard.mustache');
$linkurl = new moodle_url('/local/subject/progresscard.php'); 
// Print the page header.
$css_link = new moodle_url('/local/css/style.css');
$PAGE->set_context($context);
$PAGE->set_url($linkurl);
// $PAGE->set_pagelayout('admin');
$PAGE->set_title($linktext);
$PAGE->navbar->add('progresscard', new moodle_url($CFG->wwwroot.'/local/progresscard/progresscard.php'));
$user_id=optional_param('id', 0, PARAM_INT);
echo $OUTPUT->header();
$student_details=array();
$student_details=$DB->get_record_sql("SELECT user_id,s_ftname,s_mlname,s_lsname FROM {student} WHERE user_id=$user_id");
//print_r($student_details);exit();

    $sname = $student_details->s_ftname;
    $smname = $student_details->s_mlname;
    $slame = $student_details->s_lsname;
    $fname=$sname." ".$smname." ".$slame; 
    $val1 =$student_details->user_id;

    // print_r($val1);exit();

$rec1 = $DB->get_record_sql("SELECT d.div_class,d.id, d.div_name, d.div_teacherid, t.t_fname, c.academic_id, c.class_name
    FROM {student_assign} sa
    INNER JOIN {division} d ON sa.s_division = d.id
    INNER JOIN {teacher} t ON d.div_teacherid = t.user_id
    INNER JOIN {class} c ON d.div_class = c.id
    WHERE sa.user_id = $val1");
//    print_r($rec1);exit();
if(!empty($rec1)){
    $is_assigned_student=true;

    $classname=$rec1->class_name;
    $division=$rec1->div_name;
    $division_id=$rec1->id;
    $classteacher=$rec1->t_fname;
    $academic_id=$rec1->academic_id;

    $data= $DB->get_record_sql("SELECT * FROM {academic_year} WHERE id=$academic_id"); 
    $startyear=$data->start_year;
    $endyear=$data->end_year; 
    $startyear1=date("d-m-Y", $startyear);
    $endyear1=date("d-m-Y", $endyear);
    $academic_year=$startyear1."  to  ".$endyear1;
  

    $typeofexam = $DB->get_records('type_of_exam');
   
    $options1 = array();
    $options1[] = array('value' => '', 'label' => '---- Select Type of Exam ----');
    foreach ($typeofexam as $nameofexam){
        $type_of_exam = $nameofexam->typeofexam;
        $typeid = $nameofexam->id;
        // print_r($typeid);exit();
        $c_id = $nameofexam->course_id;      
        $options1[] = array('value' => $typeid, 'label' => $type_of_exam);
    }
    //  print_r($typeid);exit();
    $templateData = array(
        'startYearOptions' => $options1
    );
}

     $mustache = new Mustache_Engine();
    $output = $mustache->render($template, ['is_assigned_student'=>$is_assigned_student,'division_id'=>$division_id,'childid'=>$user_id,'css_link'=>$css_link,'templateData'=>$templateData,'academic_year' => $academic_year,'fname'=>$fname,'classname' => $classname,'division'=>$division,'classteacher'=>$classteacher,'academic_year'=>$academic_year]);
    echo $output;

 echo $OUTPUT->footer();
 ?>