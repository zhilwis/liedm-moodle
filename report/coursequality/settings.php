<?php


/**
 * Links and settings
 *
 * Contains settings used by coursequality report.
 *
 * @package report
 * @copyright  
 * @license   
 * @subpackage coursequality  
 */

defined('MOODLE_INTERNAL') || die;

// just a link to course report
//TODO add 'report/log:view' access rule
$external_page = new admin_externalpage('reportcoursequality', get_string('coursequality', 'admin'), "$CFG->wwwroot/report/coursequality/index.php");
$ADMIN->add('reports', $external_page);

//error_log($external_page->path."\n\r", 3, "c:\\my-errors.log");
// no report settings
$settings = null;
