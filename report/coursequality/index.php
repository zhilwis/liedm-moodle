<?php

/**
 * Displays different views of the logs.
 *
 * @package report
 * @copyright  
 * @license 
 * @subpackage coursequality  
 */


//REQUIRE SOME LIBRERIES

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('lib/chart/DefaultColor.php');
require_once('lib/data/DefaultDataSet.php');
require_once('lib/domain/Department.php');
require_once('lib/domain/Course.php');
require_once('lib/chart/DepartmentChartBuilder.php');
require_once('lib/chart/CourseChartBuilder.php');
//require_once($CFG->dirroot.'/course/lib.php');
//require_once($CFG->dirroot.'/report/log/locallib.php');

//GET PARAMETRS FROM URL
$categoryid = optional_param('category', 0, PARAM_INT);// Course ID

//CHECK IF USER IS LOGGED
require_login();
//require_capability('report/coursequality:view', $context);

if ($categoryid == 0){
	$categories = $DB->get_records('course_categories');
}else{
	$categories[] = $DB->get_record('course_categories', array('id' => $categoryid));
	$PAGE->set_heading($categories[0]->name);
	$PAGE->navbar->add($categories[0]->name);
}	


//PRINT HEADER
$reportlink = '/report/coursequality/index.php';
$PAGE->set_url($reportlink);
$PAGE->set_title("Cource Quality");
$PAGE->set_pagelayout('report');
admin_externalpage_setup('reportcoursequality');


echo $OUTPUT->header();
echo $OUTPUT->box_start();


$colorFactory = new DefaultColor();
if (count($categories)>1){
	//CHART
	$chartdata = array(
		new DefaultDataSet("[0-1)"),
		new DefaultDataSet("[1-2)"),
		new DefaultDataSet("[2-3)"),
		new DefaultDataSet("[3-4)"),
		new DefaultDataSet("[4-5]")
	);

	//TABLE
	$table = new html_table();
	$table->head = array(
		get_string("category"),
		$chartdata[0]->getTitle(),
		$chartdata[1]->getTitle(),
		$chartdata[2]->getTitle(),
		$chartdata[3]->getTitle(),
		$chartdata[4]->getTitle()
	);
	$table->attributes = array('style' => 'width: 100%;');
	$table->data = array();
	echo $OUTPUT->heading("All categories quality data");
	
	
	
	
	foreach ($categories as $category){
		//CHART
		$departmentcolor = $colorFactory->next();
		$departments = array();
		//TABLE
		$cells = array();
		$url = new moodle_url($reportlink, array('category'=>$category->id));
		$cells[] = new html_table_cell($OUTPUT->action_link($url, $category->name));
				
		for($i=0;$i<5;$i++){
			$departments[$i] = new Department($category->name, $departmentcolor);
		}
		$courses = $DB->get_records('course', array('category' => $category->id), 'sortorder ASC', 'id,fullname,shortname');
		foreach ($courses as $course){
			$number = rand(0,50)/10;
			if($number<1)
				$departments [0]->add(new Course($course->fullname, $course->shortname,null, $number));
			elseif ($number>=1&&$number<2)
				$departments [1]->add(new Course($course->fullname, $course->shortname,null, $number));
			elseif ($number>=2&&$number<3)
				$departments [2]->add(new Course($course->fullname, $course->shortname,null, $number));
			elseif ($number>=3&&$number<4)
				$departments [3]->add(new Course($course->fullname, $course->shortname,null, $number));
			else 
				$departments [4]->add(new Course($course->fullname, $course->shortname,null, $number));
		}
		
		
		foreach ($departments as $i => $department) {
			//CHART
			$chartdata[$i]->add($department);
			//TABLE
			$cells [] = $department->size();
		}	
		$table->data[] = new html_table_row($cells);
	}
	
	$_SESSION["QUALITY_CHART_BUILDER"] = new DepartmentChartBuilder($chartdata);
	
	echo html_writer::table($table);
	echo $OUTPUT->heading("All categories quality chart");
}else{
	$coursedata = array();
	$courses = $DB->get_records('course', array('category' => $categories[0]->id), 'sortorder ASC', 'id,fullname,shortname');
	foreach ($courses as $course){
		$number = rand(0,50)/10;
		$coursedata [] = new Course($course->fullname, $course->shortname,$colorFactory->next(), $number);
	}
	
	$_SESSION["QUALITY_CHART_BUILDER"] = new CourseChartBuilder($coursedata);
	
	
	echo $OUTPUT->container_start('info');
	$url = new moodle_url($reportlink);
	echo $OUTPUT->action_link($url, get_string("showallcourses"));
	echo $OUTPUT->container_end();
	echo $OUTPUT->heading("'".$categories[0]->name."' category chart");
	
}



/*if (empty($table->data)) {
	$cell = new html_table_cell($OUTPUT->notification(get_string('nocourses')));
	$cell->colspan = 5;
	$table->data[] = new html_table_row(array($cell));
}*/




echo "<div align=\"center\"><img src=\"qualitychart.php\" ></div>";



//PRINT FOOTER
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

