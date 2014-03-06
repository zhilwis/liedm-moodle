<?php


defined('MOODLE_INTERNAL') || die;
global $PAGE, $COURSE;
/*echo "<pre>";
var_dump($PAGE);
echo "</pre>";
die;*/
if ($hassiteconfig) { // needs this condition or there is error on login page
   /* $ADMIN->add('root', new admin_category('report_ataskaita1', get_string('pluginname', 'report_ataskaita1')));
    $ADMIN->add('ataskaita1', new admin_externalpage('report_ataskaita1',
        get_string('pluginname', 'report_ataskaita1'),
        new moodle_url('/local/ataskaita1/index.php')));*/

    //TODO: reikia nuorodos
/*    $ADMIN->add('root', new admin_category('report_ataskaita1',  get_string('reportname', 'report_ataskaita1')));
    $ADMIN->add('report_ataskaita1', new admin_externalpage('report_ataskaita1_page',
        get_string('pluginname', 'report_ataskaita1'),
        new moodle_url('/report/ataskaita1/index.php', array('id'=>$COURSE->id))));*/
}

/*$ADMIN->add('root', new admin_category('report_ataskaita1', get_string('config', 'report_ataskaita1')));
$settings = new admin_settingpage('report_ataskaita1_settings', 'settings');
$ADMIN->add('report_ataskaita1', $settings);
$settings->add( new admin_setting_configtext('report_ataskaita1/default_module_time', get_string('default_module_time', 'local_ataskaita3'),
    get_string('conf_default_module_time', 'local_ataskaita3'), 1800, PARAM_INT));*/

