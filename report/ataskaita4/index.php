<?php



require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/select_students_form.php');



$id = required_param('id', PARAM_INT); // Course ID

//require_login();
require_course_login($id);
$PAGE->set_url('/report/ataskaita4/index.php', array('id'=>$id));
//TODO: patikrinti ar visu failus reikia itraukti
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita4/dist/jquery.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita4/dist/jquery.jqplot.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita4/dist/plugins/jqplot.highlighter.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita4/dist/plugins/jqplot.cursor.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita4/dist/plugins/jqplot.dateAxisRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita4/dist/plugins/jqplot.canvasTextRenderer.min.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita4/dist/plugins/jqplot.canvasAxisTickRenderer.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita4/dist/plugins/jqplot.bubbleRendererCustom.js') );
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita4/dist/plugins/jqplot.categoryAxisRendererCustom.js') );   //koreguotas js (prie koreguotu failu parasyta "custom")
//$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/ataskaita4/chart.js') );
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/report/ataskaita4/dist/jquery.jqplot.css'));
//$context = context_module::instance($cm->id);
// require_capability('moodle/site:viewreports', $context);

$PAGE->set_title(format_string(get_string('pluginname', 'report_ataskaita4')));
$PAGE->set_heading(format_string(get_string('pluginname', 'report_ataskaita4')));
$PAGE->set_pagelayout("standard");
echo $OUTPUT->header();
echo $OUTPUT->heading("<h2>". get_string('pluginname', 'report_ataskaita4') ."</h2>");
$students = get_course_students($id);
//$students_form = new select_students_form($PAGE->url->out(), array('students'=>$students));
$students_form = new select_students_form(new moodle_url(null, array('id'=>$id)), array('students'=>$students));


$students_form->display();
echo "<pre>";



$all_quiz_attempts = NULL;
$successful_quiz_attempts = NULL;

$show = false;
if ($data = $students_form->get_data()) {
    //$clicks = get_all_modules_click_counts($id, $data->students_select, $data->timestart, $data->timefinish);

    //$clicks = get_users_clicks_in_course($id, $data->students_select, $data->timestart, $data->timefinish);
   // $clicks = make_rising_amounts($clicks);




    //TODO: kai nera duomenu isvesti pranesima
    $all_quiz_attempts = get_quizes_attempts_stats_by_user($id, $data->students_select, $data->timestart, $data->timefinish);
    $successful_quiz_attempts = get_quizes_attempts_stats_by_user($id, $data->students_select, $data->timestart, $data->timefinish, 50);

    $quizes = get_quizes_in_course($id);
  
    $new_quiz_ids = map_course_quizes($quizes);
    $all_quiz_attempts = remap_quizes($all_quiz_attempts, $new_quiz_ids);
    $successful_quiz_attempts = remap_quizes($successful_quiz_attempts, $new_quiz_ids);

    //var_dump($quizes);
    //var_dump($all_quiz_attempts);
    //var_dump($successful_quiz_attempts);
    //test_search_array_in_array();
    $show = true;
}


//var_dump($clicks);


//var_dump($clicks);
echo "</pre>";


if ((sizeof($all_quiz_attempts) <= 0) and $show) {
    echo get_string('data_not_found', 'report_ataskaita4');
}

echo '<div id="chartdiv" style="height:400px;width:90%;;"></div>';

echo $OUTPUT->footer();


if ($show){
    echo "<script language=\"javascript\" type=\"text/javascript\">
    $.jqplot('chartdiv', [".make_string_for_chart($all_quiz_attempts, $successful_quiz_attempts)."], {
        title:'". get_string('pluginname', 'report_ataskaita4') ."',
          seriesDefaults:{
            renderer: $.jqplot.BubbleRenderer,
            rendererOptions: {
                autoscalePointsFactor: -0.15,
                autoscaleMultiplier: 0.35,
                highlightMouseDown: true,
                bubbleAlpha: 0.8,

            },
            shadow: false
        },

        axes:{
            xaxis:{
                renderer:$.jqplot.DateAxisRenderer,
                tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
                tickOptions: {
                    formatString:'%Y-%m-%d',
                    angle: -90,
                    fontSize: '10pt',
                },
                min: \"". date('Y-m-d', $data->timestart - 60*60*24). "\",
                max: \"". date('Y-m-d', $data->timefinish + 60*60*24). "\"

            },
            yaxis: {
                renderer: $.jqplot.CategoryAxisRenderer,

                tickRenderer: $.jqplot.CanvasAxisTickRenderer ,

                ticks: " . make_string_quizes_in_course($new_quiz_ids)  . ",

                tickOptions: {
                },


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