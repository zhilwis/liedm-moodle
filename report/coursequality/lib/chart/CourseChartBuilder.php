<?php
require_once 'AbstractChartBuilder.php';
class CourseChartBuilder extends AbstractChartBuilder {
	/**
	 * @var DefaultDataSet
	 */
	private $courseData;
	public function CourseChartBuilder(DefaultDataSet $courseData) {
		$this->courseData = $courseData;
	}
	
	public function drawChart(Chart $chart){
		$padding  = new Padding(20);
		$padding->left += 100;
		$padding->bottom += 20;
		
		$height = $this->courseData->size()*(15+15)+$padding->bottom+$padding->top+20;
		$chart->addDataSet($this->courseData,5);
		
		$chart->setSize(700, $height, $padding);
		$chart->createPNGChart();
		
	}
	public function createLegend(){

		echo "<div  class=\"quality-legend\">";
		for($i=0;$i<$this->courseData->size();$i++){
			echo "<div class=\"quality-title\">".$this->courseData->get($i)->getFullTitle().
				"<span class=\"quality-color\" style=\"background-color:".$this->courseData->get($i)->getColor()->getHTMLColor()."\">&nbsp;</span></div>";
		}
		echo "</div>";
		
	}
	
}