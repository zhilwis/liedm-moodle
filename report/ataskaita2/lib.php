<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Irmantas
 * Date: 14.2.20
 * Time: 15.05
 * To change this template use File | Settings | File Templates.
 */
function report_ataskaita2_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT;
    if (has_capability('report/participation:view', $context)) {
        $url = new moodle_url('/report/ataskaita2', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_ataskaita2'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}