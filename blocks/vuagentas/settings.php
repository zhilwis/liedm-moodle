<?php
/**
 * Version details
 *
 * @package    block_vuagentas
 * @copyright  2014 onwards Tomas Vitkauskas
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_heading('vuagentas/courseheader', 
                                                get_string('labelcourseheader', 'block_vuagentas'),
                                                get_string('desccourseheader', 'block_vuagentas')));
        
$settings->add(new admin_setting_configtext('vuagentas/sekcijos',
                                                get_string('labelsekcijos', 'block_vuagentas'),
                                                get_string('descsekcijos', 'block_vuagentas'),
                                                '3', PARAM_INT, 2));

$settings->add(new admin_setting_configtext('vuagentas/forum',
                                                get_string('labelforum', 'block_vuagentas'),
                                                get_string('descforum', 'block_vuagentas'),
                                                '1', PARAM_INT, 1));

$settings->add(new admin_setting_configtext('vuagentas/chat',
                                                get_string('labelchat', 'block_vuagentas'),
                                                get_string('descchat', 'block_vuagentas'),
                                                '1', PARAM_INT, 1));

$settings->add(new admin_setting_configtext('vuagentas/glossary',
                                                get_string('labelglossary', 'block_vuagentas'),
                                                get_string('descglossary', 'block_vuagentas'),
                                                '1', PARAM_INT, 1));

$settings->add(new admin_setting_heading('vuagentas/sectionheader', 
                                                get_string('labelsectionheader', 'block_vuagentas'),
                                                get_string('descsectionheader', 'block_vuagentas')));

$settings->add(new admin_setting_configtext('vuagentas/page',
                                                get_string('labelpage', 'block_vuagentas'),
                                                get_string('descpage', 'block_vuagentas'),
                                                '1', PARAM_INT, 1));

$settings->add(new admin_setting_configtext('vuagentas/video',
                                                get_string('labelvideo', 'block_vuagentas'),
                                                get_string('descvideo', 'block_vuagentas'),
                                                '1', PARAM_INT, 1));

$settings->add(new admin_setting_configtext('vuagentas/ppt',
                                                get_string('labelppt', 'block_vuagentas'),
                                                get_string('descppt', 'block_vuagentas'),
                                                '1', PARAM_INT, 1));

$settings->add(new admin_setting_configtext('vuagentas/link',
                                                get_string('labellink', 'block_vuagentas'),
                                                get_string('desclink', 'block_vuagentas'),
                                                '1', PARAM_INT, 1));

$settings->add(new admin_setting_configtext('vuagentas/testai', 
                                                get_string('labeltestai', 'block_vuagentas'),
                                                get_string('desctestai', 'block_vuagentas'), 
                                                '1', PARAM_INT, 2));
