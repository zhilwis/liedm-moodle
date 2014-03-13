<?php
require_once 'AbstractChartBuilder.php';
class DepartmentChartBuilder extends AbstractChartBuilder{
	private $departmentData;
	public function CourseChartBuilder($departmentData) {
		$this->departmentData = $departmentData;
	}
	
	public function toString(){
		return "DEPARTMENT ".count($this->departmentData);
	}
}