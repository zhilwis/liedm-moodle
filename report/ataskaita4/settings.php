<?php


defined('MOODLE_INTERNAL') || die;
global $PAGE, $COURSE;


if ($hassiteconfig) { // needs this condition or there is error on login page
   /* $ADMIN->add('root', new admin_category('local_ataskaita4', get_string('pluginname', 'local_ataskaita4')));
    $ADMIN->add('ataskaita4', new admin_externalpage('local_ataskaita4',
        get_string('pluginname', 'local_ataskaita4'),
        new moodle_url('/local/ataskaita4/index.php')));*/
    //TODO: reikia nuorodos
//    $ADMIN->add('root', new admin_category('local_ataskaita4',  get_string('pluginname', 'local_ataskaita4')));
//    $ADMIN->add('local_ataskaita1', new admin_externalpage('local_ataskaita4_page',
//        get_string('pluginname', 'local_ataskaita4'),
//        new moodle_url('/local/ataskaita4/index.php', array('id'=>$COURSE->id))));
}