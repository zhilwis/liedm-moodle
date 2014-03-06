<?php


defined('MOODLE_INTERNAL') || die;
global $PAGE, $COURSE;


if ($hassiteconfig) { // needs this condition or there is error on login page
    /*$ADMIN->add(get_string('pluginname', 'report_ataskaita3'), new admin_externalpage('report_ataskaita3',
        get_string('pluginname', 'report_ataskaita3'),
        new moodle_url('/report/ataskaita3/index.php')));*/


    //$ADMIN->add('root', new admin_category('report_ataskaita35',  get_string('pluginname', 'report_ataskaita3')));
    //$ADMIN->add('report_ataskaita35', new admin_externalpage('report_ataskaita3_page',
        //get_string('pluginname', 'report_ataskaita3'),
       // new moodle_url('/report/ataskaita3/index.php', array('id'=>$COURSE->id))));
   //$settings = new admin_settingpage('report_ataskaita3_settings', 'settings');
    //$ADMIN->add('report_ataskaita35', $settings);

    $settings->add( new admin_setting_configtext('report_ataskaita3/default_module_time', get_string('default_module_time', 'report_ataskaita3'),
        get_string('conf_default_module_time', 'report_ataskaita3'), 1800, PARAM_INT));
    $settings->add( new admin_setting_configtext('report_ataskaita3/last_run_time', get_string('last_run_time', 'report_ataskaita3'),
        get_string('conf_last_run_time', 'report_ataskaita3'), 0, PARAM_INT));
    $settings->add( new admin_setting_configtext('report_ataskaita3/max_run_time_days', get_string('max_run_time_days', 'report_ataskaita3'),
        get_string('conf_max_run_time_days', 'report_ataskaita3'), 31, PARAM_INT));

}

