<?php


require_once 'lib/chart/Axis.php';
require_once 'lib/chart/Point.php';
require_once 'lib/chart/Rectangle.php';
require_once 'lib/chart/FilledRectangle.php';
require_once 'lib/chart/HorizontalLayout.php';
require_once 'lib/chart/VerticalLayout.php';
require_once 'lib/chart/DefaultColor.php';
require_once 'lib/chart/BarChart.php';
require_once 'lib/data/DefaultDataSet.php';
require_once 'lib/domain/Course.php';
require_once 'lib/chart/AbstractChartBuilder.php';
require_once 'lib/chart/DepartmentChartBuilder.php';
require_once 'lib/chart/CourseChartBuilder.php';

require('../../config.php');
header("Content-type: image/png");



$defaultColor = new DefaultColor();
$data[] = new DefaultDataSet("Įvertinimas", new DefaultColor(DefaultColor::YELLOW));
for($i=0;$i<15;$i++){
	$data[0]->add(new Course("Matematika", "Mat", $defaultColor->next(), rand(1,400)));
}

$defaultColor->reset();
$sss =  $_SESSION["QUALITY_CHART_BUILDER"];



$data[] = new DefaultDataSet($sss->toString(), new DefaultColor(DefaultColor::YELLOW));
for($i=0;$i<15;$i++){
	$data[1]->add(new Course("Matematika", "Mat", $defaultColor->next(), rand(1,400)));
}

$defaultColor->reset();


$data[] = new DefaultDataSet("Nuvertinimas", new DefaultColor(DefaultColor::GREEN));
for($i=0;$i<15;$i++){
	$data[2]->add(new Course("Fizika", "Fiz", $defaultColor->next(), rand(1,400)));
}


$chart = new BarChart(1000,800,"X","Y", Chart::Y);
$chart->addDataSet($data[0]);
$chart->addDataSet($data[1]);
//$chart->addDataSet($data[2]);

$chart->createPNGChart();