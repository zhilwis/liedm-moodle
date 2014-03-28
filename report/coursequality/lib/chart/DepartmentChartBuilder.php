<?php
require_once 'AbstractChartBuilder.php';
class DepartmentChartBuilder extends AbstractChartBuilder{
	/**
	 * @var array
	 */
	private $departmentData;
	
	public function DepartmentChartBuilder($departmentData) {
		$this->departmentData = $departmentData;
	}

	public function drawChart(Chart $chart){

		$padding  = new Padding(20);
		$padding->left += 100;
		$padding->bottom += 20;
		
		$height = $padding->bottom+$padding->top;
		foreach ($this->departmentData as $department) {
			$chart->addDataSet($department);
			$height+=$department->size()*15+15*3;
		}
		$chart->setSize(700, $height, $padding);
		$chart->createPNGChart();
	}
	
	public function createLegend(){
		echo "<div  class=\"quality-legend\">";
		$dataSet = $this->departmentData[0];
		for($i=0;$i<$dataSet->size();$i++){
			echo "<div class=\"quality-title\">".$dataSet->get($i)->getTitle().
				"<span class=\"quality-color\" style=\"background-color:".$dataSet->get($i)->getColor()->getHTMLColor()."\">&nbsp;</span></div>";
		}
		echo "</div>";
	}
	
		
}