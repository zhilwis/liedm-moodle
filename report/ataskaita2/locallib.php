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





//gauna informacija apie modulius
//@name_as_id - masyvo id pagal modulio pavadinima
function get_modules($name_as_id = false) {
    global $DB;
    $modules = $DB->get_records('modules');

    $result = array();
    foreach($modules as $module) {
        $id = $name_as_id ? $module->name : $module->id;
        $result[$id] = $module;
    }
    return $result;
}



//gauna vartotojo kurse paspaudimus per diena
//nera dienu, kuriomis veiklos nebuvo
function get_user_clicks_in_course($course_id, $user_id, $from = 0, $to = PHP_INT_MAX) {
    global $DB;
    global $CFG;

    $sql = "SELECT COUNT(DATE_FORMAT(FROM_UNIXTIME(log.time), '%Y-%m-%d')) as amount, DATE_FORMAT(FROM_UNIXTIME(log.time), '%Y-%m-%d') as count_date
            FROM {$CFG->prefix}log log
            INNER JOIN {$CFG->prefix}course_modules cm on log.cmid = cm.id and cm.course = {$course_id}
            WHERE log.time > {$from} and log.time < {$to} and log.userid = {$user_id}
            GROUP BY DATE_FORMAT(FROM_UNIXTIME(log.time), '%Y-%m-%d')
            ORDER BY log.time ASC;";

    $result = $DB->get_recordset_sql($sql);
    return moodle_recordset_to_array($result);
}


//return how much each user made clicks in course
function get_users_clicks_in_course($course_id, $users_ids, $from = 0, $to = PHP_INT_MAX) {
    $result = array();
    foreach($users_ids as $user_id) {
        $user_clicks = get_user_clicks_in_course($course_id, $user_id,  $from, $to);
        $result[$user_id] = add_first_last_day(add_dates($user_clicks), $from, $to);
    }
    return $result;
}


/*
//prideda pirma laikotarpio ir paskutine diena, jeigu jomis nebuvo padaryta paspaudimu
//tam kad nupiestu istisine linija
function add_first_last_day($user_clicks, $from = 0, $to = PHP_INT_MAX) {
    $from_date = date('Y-m-d', $from);
    $to_date = date('Y-m-d', $to);

    if  (sizeof($user_clicks)==0 or $user_clicks[0]['count_date'] != $from_date){
        array_push($result, array('amount'=>0, 'count_date'=>$from_date));
    }
    // $result = array_merge($result, $user_clicks);
    if (sizeof($user_clicks)==0 or $user_clicks[sizeof($user_clicks)-1]['count_date'] != $to_date){
        array_push($result, array('amount'=>0, 'count_date'=>$to_date));
    }

    $result = array_merge($result, $user_clicks);

    return $result;
}
*/
/*
//prides papildomas datas teisingam grafiko atvaizdavimui
function add_dates($user_clicks) {
    $result = array();

    foreach($user_clicks as $user_clicks_in_day) {

        $day_before = date('Y-m-d', strtotime($user_clicks_in_day['count_date']) - 60 * 60 * 24);

        $size_of_result = sizeof($result);
       if ($size_of_result == 0 or $result[$size_of_result - 1]['count_date'] != $day_before) {
           array_push($result, array('amount'=>0, 'count_date'=>$day_before));
       }
       array_push($result, $user_clicks_in_day);
    }
    return $result;
}
*/

/*
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
*/
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


//TODO: ko nereikia istrinti(auksciau)
//-----------------------------------------------auksciau is ataskaita1--------------------------------------



//gauna kurse esamu
function get_modules_ids_existing_in_course($course_id){
    global $DB;
    global $CFG;
    $sql = "SELECT cm.module as id, modules.name as name FROM {$CFG->prefix}course_modules cm
            INNER JOIN {$CFG->prefix}modules modules on cm.module = modules.id and modules.visible = true
            WHERE cm.course = {$course_id}
            GROUP BY module;";


    $modules = moodle_recordset_to_array($DB->get_recordset_sql($sql));

    $result = array();
    foreach($modules as $module) {
        $result[$module['id']] = $module['name'];
    }

    return $result;
}

// return module clicks by date
// array(array(amount=>..., count_date=>...), ...)
function get_module_click_count($course_id, $module_id, $users_ids, $from = 0, $to = PHP_INT_MAX) {
    global $DB;
    global $CFG;

    $sql = "SELECT COUNT(DATE_FORMAT(FROM_UNIXTIME(log.time), '%Y-%m-%d')) as amount, DATE_FORMAT(FROM_UNIXTIME(log.time), '%Y-%m-%d') as count_date
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
    $sql .= " GROUP BY DATE_FORMAT(FROM_UNIXTIME(log.time), '%Y-%m-%d')
             ORDER BY log.time ASC; ";
//    echo $sql;
    $result = $DB->get_recordset_sql($sql);
    return moodle_recordset_to_array($result);
}


//use log table(slow)
//returns selected modules clicks by date
function get_all_modules_click_counts_from_log($course_id, $users_ids, $modules,$from = 0, $to = PHP_INT_MAX) {
    //$modules = get_modules();
    $result = array();
    foreach($modules as $module) {
        $result[$module] = add_first_last_day(get_module_click_count($course_id, $module, $users_ids, $from, $to), $from, $to);
    }
    return $result;
}

//use daily_log table (should be faster)
function get_all_modules_click_counts_from_daily_log($course_id, $users_ids, $modules, $from = 0, $to = PHP_INT_MAX) {
    global $DB, $CFG;

    $modules_info = get_modules();

    $first = true;
    $modules_str = "(";
    foreach($modules as $module) {
        $modules_str .= $first ? " ": " OR ";
        $modules_str .= " log.module = \"" . $modules_info[$module]->name ."\"";
        $first = false;
    }
    $modules_str .= ")";

    $users_str = "(";
    $first = true;
    foreach($users_ids as $user_id) {
        $users_str .= ($first ?" log.user_id = {$user_id}" : " OR log.user_id = {$user_id}");
        $first = false;
    }
    $users_str .= ")";

    $from = date("Y-m-d", $from );
    $to = date("Y-m-d", $to);

    $sql = "SELECT log.date AS count_date, SUM(log.amount) AS amount, log.module AS module
            FROM {$CFG->prefix}daily_log AS log
            WHERE log.course = {$course_id} AND {$users_str} AND {$modules_str}
            AND log.date >= \"{$from}\" AND log.date <= \"{$to}\"
            GROUP BY log.course, log.module, log.date ";

    $clicks_count = $DB->get_recordset_sql($sql);
    $result = array();
    $modules_info = get_modules(true);

    foreach($clicks_count as $daily_clicks_counts) {
        $m_id = $modules_info[$daily_clicks_counts->module]->id;        //module id
        if (isset($result[$m_id]))
        if (!is_array($result[$m_id])) {
            $result[$m_id] = array();
        }
        $result[$m_id][] = object_to_array($daily_clicks_counts);
    }

    return $result;
}

function get_all_modules_click_counts($course_id, $users_ids, $modules, $from = 0, $to = PHP_INT_MAX) {
    global $DB;
    $dbman = $DB->get_manager();

    $result = null;
    if ($dbman->table_exists("daily_log"))
        $result = get_all_modules_click_counts_from_daily_log($course_id, $users_ids, $modules, $from, $to);
    else
        $result = get_all_modules_click_counts_from_log($course_id, $users_ids, $modules, $from, $to);
    return $result;
}




function make_string_for_chart($modules_click_counts, $modules, $colors) {
    $modules_with_colors = array();
    $color_for_modules = $colors;
    foreach ($modules as $module_id=>$module) {
        $modules_with_colors[$module_id] = array_pop($color_for_modules);
    }
  //  var_dump($modules);


    $string_for_chart = "[";

    foreach ($modules_click_counts as $module_id=>$module_clicks) {
        foreach ($module_clicks as $day=>$module_clicks_in_day) {
            $date = $module_clicks_in_day['count_date'];
            $clicks = $module_clicks_in_day['amount'];
            $color = $modules_with_colors[$module_id];
            $module_name = $modules[$module_id];
            $string_for_chart .= "['{$date}', '{$module_name}', {$clicks}, {color:'{$color}'}]";
        }
    }

    $string_for_chart .= "]";


    $pattern = '[\]\[]';
    $replacement = '], [';
    $string_for_chart = preg_replace($pattern, $replacement, $string_for_chart);                //padedami kableliai tarp masyvo elementu(masyvu)
    return $string_for_chart;
}

//laikotarpio tarp pasirinkimu tikrinimas grafiko atvaizdavimui
function ataskaita2_get_duration($from_date, $to_date){
    $time_duration_sec = $to_date - $from_date;
    $time_duration = floor($time_duration_sec/(60*60*24));
    return $time_duration;
}


function ataskaita2_get_ticks($from, $to) {
    $tick_number = 30;
    $time = ataskaita2_get_duration($from, $to);
    if($time<=30){
        $tick_number = $time+1;
    }
    return $tick_number;
}

function ataskaita2_get_ticks_interval($from, $to) {
    $tick_number = 30.0;
    $time = ataskaita2_get_duration($from, $to);
    $interval = ceil($time / $tick_number);
    return $interval;
}