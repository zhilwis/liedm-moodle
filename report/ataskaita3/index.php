<?php


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__) . '/select_form.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT); // Course ID

//require_login();
require_course_login($id);

$PAGE->set_url('/report/ataskaita3/index.php', array('id'=>$id));
//TODO: patikrinti ar visu failus reikia itraukti
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita3/dist/jquery.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita3/dist/jquery.jqplot.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita3/dist/plugins/jqplot.dateAxisRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita3/dist/plugins/jqplot.categoryAxisRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita3/dist/plugins/jqplot.barRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita3/dist/plugins/jqplot.canvasAxisTickRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita3/dist/plugins/jqplot.canvasTextRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita2/dist/plugins/jqplot.cursor.min.js') );
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/report/ataskaita3/dist/jquery.jqplot.css'));
//$context = context_module::instance($cm->id);
// require_capability('moodle/site:viewreports', $context);
//ataskaita3_cron();
$PAGE->set_title(format_string(get_string('pluginname', 'report_ataskaita3')));
$PAGE->set_heading(format_string(get_string('pluginname', 'report_ataskaita3')));
$PAGE->set_pagelayout("standard");
echo $OUTPUT->header();
echo $OUTPUT->heading("<h2>". get_string('pluginname', 'report_ataskaita3') ."</h2>");




$students = get_course_students($id);
$modules = get_modules_ids_existing_in_course($id);
$students_form = new select_form(new moodle_url(null, array('id'=>$id)), array('students'=>$students, 'modules'=>$modules));


$students_form->display();
echo "<pre>";
$show = false;
//$time_spent = new TimeSpent();

if ($data = $students_form->get_data()) {
   //var_dump($data);
    //$time_spent->set_parameters($data->students_select, $id, $data->timestart, $data->timefinish);
    //$time_spent->count_spent_time();
    //$time_spent_in_module_str = $time_spent->get_string_time_spent_in_module(get_module_name($data->selected_modules));
    //$clicks_count = get_all_modules_click_counts($id, $data->students_select, $data->timestart, $data->timefinish) ;
    //$clicks_count_str = clicks_array_to_string($clicks_count, $data->selected_modules);
    $new_data = new StatisticFromDailyLog($id, $data->students_select, $data->selected_modules);
    $new_data->getData();
    $clicks_count_str = $new_data->getClicksAmountString();
    $time_spent_in_module_str = $new_data->getSpentTimeString();
    //var_dump($clicks_count);
    
    $show = true;


echo "</pre>";

    if ((sizeof($clicks_count_str) <= 0) and (($time_spent_in_module_str) <= 0) and $show) {
        echo get_string('data_not_found', 'report_ataskaita3');
    }
}
echo '<div id="chartdiv" style="height:400px;width:90%;"></div>';

echo $OUTPUT->footer();

if ($show){

    echo "<script language=\"javascript\" type=\"text/javascript\">
    var data1 = " . $time_spent_in_module_str . ";

    var data2 = " . $clicks_count_str . ";

    $.jqplot('chartdiv', [data1, data2], {
        series:[{renderer:$.jqplot.BarRenderer,  rendererOptions: {barWidth: 5}}, { yaxis:'y2axis', markerOptions: { size:7, style:\"filledSquare\" }, showLine:false}],

        title:'". get_string('pluginname', 'report_ataskaita3') ."',


        axes:{
            xaxis:{
                renderer:$.jqplot.DateAxisRenderer,

                tickInterval: \"". ataskaita3_get_ticks_interval($data->timestart, $data->timefinish) ." day\",
                min: \"". date('Y-m-d', $data->timestart). "\",
                max: \"". date('Y-m-d', $data->timefinish). "\",
                tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
                tickOptions: {
                    formatString:'%Y-%m-%d',
                    angle: -90,
                    fontSize: '10pt',
                    },
            },

            yaxis:{
                autoscale:true,
            },
            y2axis:{
                autoscale:true,
                min: 0,

            }

        },
        cursor: {
            show: true,
            tooltipLocation:'sw',
            zoom:true
        }
    });
    </script>";
}



