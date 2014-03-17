<?php
abstract class AbstractChartBuilder {
	
	public abstract function drawChart(Chart $chart);
	
	public abstract function createLegend();
	
}