<?php
/**
 * User: Vartotojas
 * Date: 13.9.4
 * Time: 16.22
 */

function report_ataskaita1_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT;
    if (has_capability('report/participation:view', $context)) {
        $url = new moodle_url('/report/ataskaita1', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_ataskaita1'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}
