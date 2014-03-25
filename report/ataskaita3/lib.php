<?php
/**
 * User: Vartotojas
 * Date: 13.9.6
 * Time: 09.22
 */
/*global $COURSE;
($COURSE->id);
die;*/

require_once('locallib.php');

function report_ataskaita3_cron() {
    global $DB, $CFG;
   $last_run = get_config('report_ataskaita3', 'last_run_time');
   $time_to_inctive = get_config('report_ataskaita3', 'default_module_time');
    //$last_run = 0;
    //$time_to_inctive = 1800;

    //var_dump($last_run);
    //jeigu butu meginama paleisti du kartus ta pacia diena
    if (date('Y-m-d', $last_run) === date('Y-m-d', time())) {
        mtrace("Siandien jau buvo paleista");
        return true;
    }
    mtrace("ataskaitų generavimas prasidėjo");

    $latest_record = ataskaita3_get_latest_record_daily_log();

    $from = 0;
    if (!$latest_record) {
        $first_record_log = ataskaita3_get_first_record_log();
        $from = $first_record_log->time;
    } else {
        $first_record_log = ataskaita3_get_first_record_log(strtotime($latest_record->date)+24*60*60);
        $from = $first_record_log->time;
    }


    if (!$from) {
        return true;
    }


   $to = $from + get_config('report_ataskaita3', 'max_run_time_days') * 24 * 60 * 60;
//    $to = $from + 365 * 24 * 60 * 60 * 1;


    $today = floor(time() / (24 * 60 *60)) * 24 * 60 *60;

    if ($to > $today) {
        $to = $today;
    }


    $sql = "INSERT INTO {$CFG->prefix}daily_log (user_id, course, module, date, amount, spent_time)
            SELECT diff.userid userid, diff.course course, diff.module module, diff.date date, COUNT(*) amount , SUM(diff.diff) spent_time
            FROM

                (SELECT clicks.userid, clicks.course, clicks.module, clicks.date,
                IF( clicks.nextuserid = clicks.userid AND clicks.nextdate = clicks.date AND (clicks.nexttime - clicks.time) < $time_to_inctive, clicks.nexttime - clicks.time, $time_to_inctive) diff
                FROM
                (SELECT CAST(@olduserid AS SIGNED) userid, CAST(@oldcourse AS SIGNED) course ,  CAST(@oldmodule AS CHAR) module,  CAST(@olddate AS CHAR) date, CAST(@oldtime AS UNSIGNED) time,
                @olduserid := log.userid nextuserid, @oldcourse := log.course nextcourse, @oldmodule := log.module nextmodule, 
                @olddate := CONCAT(DATE_FORMAT(FROM_UNIXTIME(log.time), '%Y-%m-%d')) nextdate, @oldtime := log.time nexttime 
                FROM
                        ((SELECT userid, course, module, time FROM {$CFG->prefix}log 
                    WHERE time < $to AND time > $from)
                    UNION ALL
                    (SELECT 9999999999 as userid, null as course, null as module, 9999999999 as time) 
                    ORDER BY userid ASC, time ASC) log,
                    (SELECT @olduserid := null, @oldcourse := null, @oldmodule := null, @oldtime := null, @olddate := null) sqlvars) clicks 
                WHERE clicks.userid IS NOT NULL AND clicks.course IS NOT NULL AND clicks.module IS NOT NULL AND clicks.date IS NOT NULL AND clicks.time IS NOT NULL) diff
            GROUP BY diff.userid, diff.date, diff.course, diff.module
            ORDER BY diff.date, diff.userid, diff.course, diff.module";

            //var_dump($sql);
    $DB->execute($sql);


/*
    $sql = "INSERT INTO {$CFG->prefix}daily_log (user_id, course, module, date, amount, spent_time)
            SELECT log.userid AS userid, log.course AS course,
            log.module AS modulename, DATE_FORMAT(FROM_UNIXTIME(log.time), '%Y-%m-%d') AS logdate,
            COUNT(*) AS amount, 0 AS spent_time
            FROM {$CFG->prefix}log AS log
            WHERE log.time > {$from} AND log.time < {$to}
            GROUP BY log.userid, log.course, log.module, DATE_FORMAT(FROM_UNIXTIME(log.time), '%Y-%m-%d')
            ORDER BY log.time ASC, log.course ASC, log.module ASC, log.userid ASC";
    $DB->execute($sql);
    $courses = get_courses();

    //$courses = array(array_pop($courses));
    //var_dump($courses);
    $usql="";
    $time_spent = new TimeSpent();
    foreach ($courses as $course) {

        $users = ataskaita3_get_users_id_by_course($course->id);
        //$to = PHP_INT_MAX;

        $time_spent->set_parameters($users, $course->id, $from, $to);
        $time_spent->count_spent_time();
       // break;
        $users_time_spent = $time_spent->get_all_users_spent_time();
        //var_dump(get_modules());
        
       foreach ($users_time_spent as $user_id=>$user_time_spent) {
           foreach ($user_time_spent as $module_name=>$spent_time_in_module) {
               foreach ($spent_time_in_module as $day=>$time) {
                   $user_log = $DB->get_record('daily_log', array('course'=>$course->id,
                                                                  'module'=>$module_name,
                                                                  'user_id'=>$user_id,
                                                                  'date'=>$day));
                   $user_log->spent_time = $time;
                   $DB->update_record('daily_log', $user_log);

                    
               }
           }
       }
        
       $time_spent->clean();
    }
*/
    mtrace("ataskaitų generavimas baigėsi");
    set_config('last_run_time', time(), 'report_ataskaita3');
    return true;
}


//get all users enrolled on the course
function ataskaita3_get_users_id_by_course($course_id) {
    global $CFG;
    global $DB;
    $sql = "SELECT user.id userid FROM {$CFG->prefix}user user
			INNER JOIN {$CFG->prefix}user_enrolments enrolments on user.id = enrolments.userid
			INNER JOIN {$CFG->prefix}enrol enrol on enrolments.enrolid = enrol.id
			WHERE enrol.courseid = '{$course_id}';";
    $result = $DB->get_recordset_sql($sql);
    return moodle_recordset_to_array($result);
}

//gauna paskutini irasa is daily_log lenteles
function ataskaita3_get_latest_record_daily_log($course_id = -1) {
    global $DB, $CFG;
    if ($course_id >= 0)
        $sql = "SELECT * FROM {$CFG->prefix}daily_log AS log WHERE course = {$course_id} ORDER BY log.date DESC LIMIT 1";
    else
        $sql = "SELECT * FROM {$CFG->prefix}daily_log AS log ORDER BY log.date DESC LIMIT 1";
    $record = $DB->get_record_sql($sql);
    return $record;
}

function ataskaita3_get_first_record_log($from = 0) {
    global $DB, $CFG;
    $sql = "SELECT * FROM {$CFG->prefix}log AS log WHERE log.time > {$from} ORDER BY log.time ASC LIMIT 1";
    $record  = $DB->get_record_sql($sql);
    return $record;
}
function report_ataskaita3_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT;
    if (has_capability('report/participation:view', $context)) {
        $url = new moodle_url('/report/ataskaita3', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_ataskaita3'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}