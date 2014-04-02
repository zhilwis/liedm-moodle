<?php 

    require_once('../../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once($CFG->dirroot.'/blocks/vuagentas/lib.php');

    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad', 'error');
    }
    
    $cid = optional_param('cid', 0, PARAM_INT);
    
    if (! $course = $DB->get_record('course', array('id'=>$cid))) {
        print_error('coursemisconf');
    }
    
    $PAGE->set_url('/blocks/vuagentas/info.php', array('cid' => $cid));
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
    $PAGE->set_title($course->shortname.': vuagentas');
    $PAGE->set_heading($course->fullname);
    $PAGE->set_pagelayout('course');
    
    
    if ($cid == SITEID) { // home page
        $PAGE->navbar->add(get_string('pluginname','block_vuagentas'));
    } else {
        $countcategories = $DB->count_records('course_categories');
        if ($countcategories > 1 || ($countcategories == 1 && $DB->count_records('course') > 200)) {
            $PAGE->navbar->add(get_string('categories'));
        } else {
            $PAGE->navbar->add(get_string('courses'), new moodle_url('/course/category.php?id='.$course->category));
            $PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php?id='.$cid));
            $PAGE->navbar->add(get_string('pluginname','block_vuagentas'));
        }
    }
    
    $rating = vuagentas_get_rerating($cid);
    echo $OUTPUT->header();
        if(is_object($rating)){
            echo $OUTPUT->box(get_string('rating', 'block_vuagentas'). ' ' . vuagentas_get_star($rating->total));
            foreach ($rating->err as $key => $value){
                echo $OUTPUT->box_start();
                if($key == '0'){
                   foreach ($value as $text){
                       echo $text;
                   }
                } else {
                    echo get_section_name($rating->course, $key). '<br /><br />';
                    foreach ($value as $text){
                       echo $text;
                   }
                }
                
                echo $OUTPUT->box_end();
            }
        } else {
            echo $OUTPUT->box($rating);
        }
        /// Finish the page
        echo $OUTPUT->footer();