<?php


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/select_students_form.php');
require_once('lib.php');


$id = required_param('id', PARAM_INT); // Course ID

//require_login();
require_course_login($id);

//TODO: patikrinti ar visu failus reikia itraukti
$PAGE->set_url('/report/ataskaita1/index.php', array('id'=>$id));

$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita1/dist/jquery.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita1/dist/jquery.jqplot.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita1/dist/plugins/jqplot.highlighter.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita1/dist/plugins/jqplot.cursor.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita1/dist/plugins/jqplot.dateAxisRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita1/dist/plugins/jqplot.canvasTextRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita1/dist/plugins/jqplot.canvasAxisTickRenderer.min.js') );
//$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita1/chart.js') );
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/report/ataskaita1/dist/jquery.jqplot.css'));
//$context = context_module::instance($cm->id);
// require_capability('moodle/site:viewreports', $context);

$PAGE->set_title(format_string(get_string('pluginname', 'report_ataskaita1')));
$PAGE->set_heading(format_string(get_string('pluginname', 'report_ataskaita1')));
$PAGE->set_pagelayout("standard");
echo $OUTPUT->header();
echo $OUTPUT->heading("<h2>". get_string('pluginname', 'report_ataskaita1') ."</h2>");
$students = get_course_students($id);
//$students_form = new select_students_form($PAGE->url->out(), array('students'=>$students));
$students_form = new select_students_form(new moodle_url(null, array('id'=>$id)), array('students'=>$students));

$students_form->display();
echo "<pre>";

$show = false;
$clicks = NULL;
if ($data = $students_form->get_data()) {
    //$clicks = get_all_modules_click_counts($id, $data->students_select, $data->timestart, $data->timefinish);

    $clicks = get_users_clicks_in_course($id, $data->students_select, $data->timestart, $data->timefinish);
    $clicks = ataskaita1_add_last_days_for_users($clicks, $data->timestart, $data->timefinish);
    $clicks = ataskaita1_add_dates($clicks);
    $clicks = make_rising_amounts($clicks);


    $show = true;
}

echo "</pre>";

if ((sizeof($clicks) <= 0) and $show) {
    echo get_string('data_not_found', 'report_ataskaita1');
}

echo '<div id="chartdiv" style="height:400px;width:90%;;"></div>';

echo $OUTPUT->footer();

if ($show){
    $tick_number = 30;
    $time = get_duration($data->timestart, $data->timefinish);
    if($time<=30){
        $tick_number = $time+1;
    }
    echo "<script language=\"javascript\" type=\"text/javascript\">
    $.jqplot('chartdiv', ".clicks_array_to_string($clicks).", {
        title:'". get_string('pluginname', 'report_ataskaita1') ."',
        legend:{
               renderer: $.jqplot.EnhancedLegendRenderer,
               location: 'nw',
               show:true
        },

        series: [".make_labels_string(get_selected_students_fullnames($data->students_select, $students))."],

        seriesDefaults:{
        showMarker: false
        },
        highlighter: {
            show: true
        },
        axes:{
            xaxis:{
                renderer:$.jqplot.DateAxisRenderer,
                tickRenderer:$.jqplot.CanvasAxisTickRenderer,
                tickOptions:{
                   angle: -90,
                   fontSize: '11px'
                },
                rendererOptions:{
                    numberTicks: ".$tick_number.",
                },
                min: \"". date('Y-m-d', $data->timestart). "\",
                max: \"". date('Y-m-d', $data->timefinish). "\"

            },
            yaxis:{
                min: 0
            }
        }

    });
    </script>";
}