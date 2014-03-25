<?php
require_once 'AbstractChartBuilder.php';
class CourseChartBuilder extends AbstractChartBuilder {
	private $courseData;
	public function CourseChartBuilder($courseData) {
		$this->courseData = $courseData;
	}
	public function toString(){
		return "COURSE ".count($this->courseData);
	}
	
}