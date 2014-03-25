<?php


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__) . '/select_form.php');



$id = required_param('id', PARAM_INT); // Course ID

//require_login();
require_course_login($id);

//TODO: patikrinti ar visu failus reikia itraukti
$PAGE->set_url('/report/ataskaita2/index.php', array('id'=>$id));
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita2/dist/jquery.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita2/dist/jquery.jqplot.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita2/dist/plugins/jqplot.highlighter.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita2/dist/plugins/jqplot.cursor.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita2/dist/plugins/jqplot.dateAxisRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita2/dist/plugins/jqplot.bubbleRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita2/dist/plugins/jqplot.categoryAxisRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita3/dist/plugins/jqplot.canvasTextRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita3/dist/plugins/jqplot.canvasAxisTickRenderer.min.js') );
//$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita2/chart.js') );
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/report/ataskaita2/dist/jquery.jqplot.css'));
//$context = context_module::instance($cm->id);
// require_capability('moodle/site:viewreports', $context);

$PAGE->set_title(format_string(get_string('pluginname', 'report_ataskaita2')));
$PAGE->set_heading(format_string(get_string('pluginname', 'report_ataskaita2')));
$PAGE->set_pagelayout("standard");
echo $OUTPUT->header();
echo $OUTPUT->heading("<h2>". get_string('pluginname', 'report_ataskaita2') ."</h2>");


$colors = array("#4bb2c5", "#EAA228", "#c5b47f", "#579575", "#839557", "#958c12", "#953579", "#4b5de4", "#d8b83f", "#ff5800", "#0085cc", "#c747a3", "#cddf54", "#FBD178", "#26B4E3", "#bd70c7");

$students = get_course_students($id);
$modules = get_modules_ids_existing_in_course($id);
$students_form = new select_form(new moodle_url(null, array('id'=>$id)), array('students'=>$students, 'modules'=>$modules));


$students_form->display();
echo "<pre>";
$show = false;
if ($data = $students_form->get_data()) {
    //$clicks = get_all_modules_click_counts($id, $data->students_select, $data->timestart, $data->timefinish);
   //var_dump($data);
    //$clicks = get_users_clicks_in_course($id, $data->students_select, $data->timestart, $data->timefinish);
    $clicks = get_all_modules_click_counts($id, $data->students_select, $data->selected_modules, $data->timestart, $data->timefinish);
    //var_dump($clicks);
    $show = true;


//var_dump($clicks);
//$clicks = make_rising_amounts($clicks);
//var_dump($clicks);
//var_dump(make_string_for_chart($clicks, $modules, $colors));
//var_dump(clicks_array_to_string($clicks));
echo "</pre>";

    if ((sizeof($clicks) <= 0) and $show) {
        echo get_string('data_not_found', 'report_ataskaita2');
    }
}
echo '<div id="chartdiv" style="height:400px;width:90%;"></div>';

echo $OUTPUT->footer();


if ($show){
    //numberTicks: ".ataskaita2_get_ticks($data->timestart, $data->timefinish).",
    echo "<script language=\"javascript\" type=\"text/javascript\">
    $.jqplot('chartdiv', [".make_string_for_chart($clicks, $modules, $colors)."], {
        title:'". get_string('pluginname', 'report_ataskaita2') ."',
          seriesDefaults:{
            renderer: $.jqplot.BubbleRenderer,
            rendererOptions: {
                bubbleGradients: false,
                autoscalePointsFactor: -0.15,
                autoscaleMultiplier: 0.85,
                highlightMouseDown: true,
                bubbleAlpha: 0.8,

            },
            shadow: true
        },

        axes:{
            xaxis:{
                renderer:$.jqplot.DateAxisRenderer,
                tickRenderer:$.jqplot.CanvasAxisTickRenderer,
                tickInterval: \"". ataskaita2_get_ticks_interval($data->timestart, $data->timefinish) ." day\",
                tickOptions:{
                   formatString:'%Y.%m.%d',
                   angle: -90,
                   fontSize: '12px'

                },
                min: \"". date('Y-m-d', $data->timestart). "\",
                max: \"". date('Y-m-d', $data->timefinish). "\"

            },
            yaxis: {
                renderer: $.jqplot.CategoryAxisRenderer
            }
        },
        cursor:{
            show: true,
            zoom:true,
            showTooltip:false
        }


    });
    </script>";
}