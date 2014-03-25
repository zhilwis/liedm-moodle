<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

//converts stdobject to array
function object_to_array($d) {
    if (is_object($d)) {
        $d = get_object_vars($d);
    }
    if (is_array($d)) {
        return array_map(__FUNCTION__, $d);
    } else {
        return $d;
    }
}



//converts moodle recordset to array
function moodle_recordset_to_array($recordset, $key = "") {
    $result = array();
    foreach ($recordset as $record) {
        $r = object_to_array($record);
        if (strlen($key)>0) {
            $result[$r[$key]] = $r;
        } else {
            array_push($result, $r);
        }
    }
    return $result;
}


//gaunami kurso studentai(be destytoju)
//return array(student_id=>full_name, ...)
function get_course_students($course_id) {
    global $CFG;
    global $DB;
    $sql = "SELECT role.userid, user.firstname, user.lastname FROM {$CFG->prefix}user user
            INNER JOIN {$CFG->prefix}role_assignments role on user.id = role.userid
			INNER JOIN {$CFG->prefix}context cont
			on  role.contextid = cont.id and cont.instanceid = {$course_id} and role.roleid = 5;";

    $students = $DB->get_recordset_sql($sql);
    $result = array();

    foreach ($students as $student) {
        $result[$student->userid] = $student->firstname . ' ' . $student->lastname;
    }

    return $result;
}



// return module clicks by date
// array(array(amount=>..., count_date=>...), ...)

function get_module_click_count($course_id, $module_id, $users_ids,$from = 0, $to = PHP_INT_MAX) {
    global $DB;
    global $CFG;

    $sql = "SELECT COUNT(DATE_FORMAT(FROM_UNIXTIME(log.time), '%d-%m-%Y')) as amount, DATE_FORMAT(FROM_UNIXTIME(log.time), '%d-%m-%Y') as count_date
            FROM {$CFG->prefix}log log
            INNER JOIN {$CFG->prefix}course_modules cm on log.cmid = cm.id and cm.module = {$module_id} and cm.course = {$course_id}
            WHERE log.time > {$from} and log.time < {$to}";

    $first = true;
    foreach($users_ids as $user_id) {
        if ($first) {
            $sql .= " and (log.userid = {$user_id}";
            $first = false;
        } else {
            $sql .= " or log.userid = {$user_id}";
        }
    }
    if (sizeof($users_ids) > 0){
        $sql .= ")";
    }
    $sql .= " GROUP BY DATE_FORMAT(FROM_UNIXTIME(log.time), '%d-%m-%Y')
             ORDER BY log.time ASC; ";
//    echo $sql;
    $result = $DB->get_recordset_sql($sql);
    return moodle_recordset_to_array($result);
}

//returns all modules clicks by date
function get_all_modules_click_counts($course_id, $users_ids, $from = 0, $to = PHP_INT_MAX) {

    $modules = get_modules();
    $result = array();
    foreach($modules as $module) {
        $result[$module->id] = get_module_click_count($course_id, $module->id, $users_ids, $from, $to);
    }
    return $result;
}

//gauna informacija apie modulius
function get_modules() {
    global $DB;
    $modules = $DB->get_records('modules');
    return $modules;
}



//gauna vartotojo kurse paspaudimus per diena
//nera dienu, kuriomis veiklos nebuvo
function get_user_clicks_in_course($course_id, $user_id, $from = 0, $to = PHP_INT_MAX) {
    global $DB;
    global $CFG;

    $from = date('Y-m-d', $from);
    $to = date('Y-m-d', $to);

    /*$sql = "SELECT COUNT(DATE_FORMAT(FROM_UNIXTIME(log.time), '%Y-%m-%d')) as amount, DATE_FORMAT(FROM_UNIXTIME(log.time), '%Y-%m-%d') as count_date
            FROM {$CFG->prefix}log log
            INNER JOIN {$CFG->prefix}course_modules cm on log.cmid = cm.id and cm.course = {$course_id}
            WHERE log.time > {$from} and log.time < {$to} and log.userid = {$user_id}
            GROUP BY DATE_FORMAT(FROM_UNIXTIME(log.time), '%Y-%m-%d')
            ORDER BY log.time ASC;";*/
    $sql = "SELECT SUM(amount) as amount, date as count_date, user_id
            FROM {$CFG->prefix}daily_log
            WHERE course = {$course_id} AND user_id = {$user_id} AND date >= '{$from}' AND date <= '{$to}'
            GROUP BY user_id, course, date
			ORDER BY date ASC;";

    $result = $DB->get_recordset_sql($sql);

     return moodle_recordset_to_array($result);

}


function get_users_clicks_in_course($course_id, $users_ids, $from = 0, $to = PHP_INT_MAX) {
    global $DB;

    $dbman = $DB->get_manager();
    $result = null;

    if ($dbman->table_exists('daily_log')) {
        $result = ataskaita1_get_users_clicks_in_course_from_daily_log($course_id, $users_ids, $from, $to);
    } else {
        $result = ataskaita1_get_users_clicks_in_course_from_log($course_id, $users_ids, $from, $to);
    }
    return $result;
}

//return how much each user made clicks in course
function ataskaita1_get_users_clicks_in_course_from_log($course_id, $users_ids, $from = 0, $to = PHP_INT_MAX) {
    $result = array();
    foreach($users_ids as $user_id) {
        $user_clicks = get_user_clicks_in_course($course_id, $user_id,  $from, $to);
        $result[$user_id] = $user_clicks;
        //var_dump($result);
        //$result[$user_id] = add_first_last_day(add_dates($user_clicks), $from, $to);
    }
    //var_dump($result);
    return $result;
}




function ataskaita1_get_users_clicks_in_course_from_daily_log($course_id, $users_ids, $from  = 0, $to = PHP_INT_MAX) {
    global $DB, $CFG;

    $users_ids_str = "";

    $first = true;
    foreach ($users_ids as $user_id) {
        $users_ids_str .= !$first ? " OR " : " ";
        $users_ids_str .= " log.user_id = {$user_id} ";
        $first = false;
    }

    $from = date('Y-m-d', $from);
    $to = date('Y-m-d', $to);

    $sql = "SELECT log.user_id AS user_id, log.date AS count_date, SUM(log.amount) AS amount
            FROM {$CFG->prefix}daily_log AS log
            WHERE ({$users_ids_str}) AND log.date > \"{$from}\" AND log.date < \"{$to}\" AND log.course = {$course_id}
            GROUP BY log.user_id, log.course, log.date
            ORDER BY log.user_id, log.date ASC;";
    //var_dump($sql);

    $data = $DB->get_recordset_sql($sql);

    $result = array();

    foreach ($data as $obj) {
        if (!isset($result[$obj->user_id]) or !is_array($result[$obj->user_id])) {
            $result[$obj->user_id] = array();
        }
        array_push($result[$obj->user_id], (array)$obj);
    }
    //var_dump($result);

    return $result;
}

function ataskaita1_add_last_days_for_users($users_data, $from, $to) {

    $result = array();

    foreach ($users_data as $user_id=>$user_data) {
        $result[$user_id] = ataskaita1_add_first_last_day($user_data, $from, $to);
    }
    return $result;
}



//prideda pirma laikotarpio ir paskutine diena, jeigu jomis nebuvo padaryta paspaudimu
//tam kad nupiestu istisine linija
function ataskaita1_add_first_last_day($user_clicks, $from = 0, $to = PHP_INT_MAX) {
    $from_date = date('Y-m-d', $from);
    $to_date = date('Y-m-d', $to);
    $result = array();
  /*  if (!in_array($from_date, $user_clicks)){
        array_push($result, array('amount'=>0, 'count_date'=>$from_date));
    }*/

    if  (sizeof($user_clicks)!=0 and $user_clicks[0]['count_date'] != $from_date){
        array_push($result, array('amount'=>0, 'count_date'=>$from_date));
    }
   // $result = array_merge($result, $user_clicks);


    $result = array_merge($result, $user_clicks);
    if (sizeof($user_clicks)!=0 and $user_clicks[sizeof($user_clicks)-1]['count_date'] != $to_date){
        array_push($result, array('amount'=>0, 'count_date'=>$to_date));
    }
    return $result;
}


function ataskaita1_add_dates($clicks) {
    $result = array();
    foreach($clicks as $user_id=>$user_clicks) {
        $result[$user_id] = ataskaita1_add_dates_user($user_clicks);
    }
    return $result;
}

//prides papildomas datas teisingam grafiko atvaizdavimui
function ataskaita1_add_dates_user($user_clicks) {
    $result = array();

    foreach($user_clicks as $user_clicks_in_day) {

        $day_before = date('Y-m-d', strtotime($user_clicks_in_day['count_date']) - 60 * 60 * 24);

        $size_of_result = sizeof($result);
       if ($size_of_result != 0 and $result[$size_of_result - 1]['count_date'] != $day_before) {
           array_push($result, array('amount'=>0, 'count_date'=>$day_before));
       }
       array_push($result, $user_clicks_in_day);
    }
    return $result;
}



//making data to be rising (sum up all amount before going date)
function make_rising_user_clicks_amounts($user_clicks) {
    $sum = 0;
    $result = array();
    foreach ($user_clicks as $key => $user_clicks_in_day) {
        $user_clicks_in_day['amount'] = intval($user_clicks_in_day['amount']) + $sum;
        $sum = intval($user_clicks_in_day['amount']);
        array_push($result, $user_clicks_in_day);
    }
    return $result;
}

function make_rising_amounts($clicks) {
    $result = array();
    foreach ($clicks as $user_clicks) {
        array_push($result, make_rising_user_clicks_amounts($user_clicks));
    }

    return $result;
}

//return string which look like js array
function clicks_array_to_string($clicks) {
    $result = '[';
    foreach($clicks as $user_clicks) {
            $result .= '[';
            $user_string = '';
            foreach($user_clicks as $user_clicks_in_day) {
                $user_string .= '["'.$user_clicks_in_day['count_date']. '", '.$user_clicks_in_day['amount'].']';
            }
            $result .= $user_string;
            $result .= ']';
    }
    $result .= ']';

    $pattern = '[\]\[]';
    $replacement = '], [';
    $result = preg_replace($pattern, $replacement, $result);                //padedami kableliai tarp masyvo elementu(masyvu)
    return $result;
}


function make_labels_string($labels) {
    $result = '';

    foreach ($labels as $label) {
        $label_string = "{label: '".$label."'}";
        $result .= $label_string;
    }
    $pattern = '[\}\{]';
    $replacement = '}, {';
    $result = preg_replace($pattern, $replacement, $result);                //padedami kableliai tarp masyvo elementu(masyvu)

    return $result;
}


//grazina masyva su studentu pilnais vardais
//$students_ids - studentu id masyvas, kuriu vardu reikia
//$students - asociatyvus masyvas (array(student_id=>fullname)) su studentu vardais
function get_selected_students_fullnames($students_ids, $students) {
    $students_full_names = array();
    foreach($students_ids as $student_id) {
        array_push($students_full_names, $students[$student_id]);
    }
    return $students_full_names;
}

//laikotarpio tarp pasirinkimu tikrinimas grafiko atvaizdavimui
function get_duration($from_date, $to_date){
    $time_duration_sec = $to_date - $from_date;
    $time_duration = floor($time_duration_sec/(60*60*24));
    return $time_duration;
}