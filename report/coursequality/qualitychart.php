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
require_once 'lib/domain/Department.php';
require_once 'lib/chart/AbstractChartBuilder.php';
require_once 'lib/chart/DepartmentChartBuilder.php';
require_once 'lib/chart/CourseChartBuilder.php';
require_once 'lib/chart/Padding.php';

require('../../config.php');
header("Content-type: image/png");

$chartBuilder =  $_SESSION["QUALITY_CHART_BUILDER"];
$chartBuilder->drawChart(new BarChart(Chart::Y));

