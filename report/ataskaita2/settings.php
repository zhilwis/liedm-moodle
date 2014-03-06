<?php


defined('MOODLE_INTERNAL') || die;
global $PAGE, $COURSE;


if ($hassiteconfig) { // needs this condition or there is error on login page
   /* $ADMIN->add(get_string('pluginname', 'local_ataskaita2'), new admin_externalpage('local_ataskaita2',
        get_string('pluginname', 'local_ataskaita2'),
        new moodle_url('/local/ataskaita2/index.php')));*/
    //TODO: reikia nuorodos
//    $ADMIN->add('root', new admin_category('local_ataskaita2',  get_string('pluginname', 'local_ataskaita2')));
//    $ADMIN->add('local_ataskaita1', new admin_externalpage('local_ataskaita2_page',
//        get_string('pluginname', 'local_ataskaita2'),
//        new moodle_url('/local/ataskaita2/index.php', array('id'=>$COURSE->id))));
}