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
        $user_clicks =get_user_clicks_in_course($course_id, $user_id,  $from, $to);
        $result[$user_id] = add_first_last_day(add_dates($user_clicks), $from, $to);
    }
    return $result;
}



//prideda pirma laikotarpio ir paskutine diena, jeigu jomis nebuvo padaryta paspaudimu
//tam kad nupiestu istisine linija
function add_first_last_day($user_clicks, $from = 0, $to = PHP_INT_MAX) {
    $from_date = date('Y-m-d', $from);
    $to_date = date('Y-m-d', $to);
    $result = array();
  /*  if (!in_array($from_date, $user_clicks)){
        array_push($result, array('amount'=>0, 'count_date'=>$from_date));
    }*/

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



//TODO: ko nereiki aistrinti(auksciau)





































//suskaiciuoja kiek kuris vartotojas kartu megino testa
//grade - kiek procentu turejo surinkti vartotojas
function get_quizes_attempts_stats($course_id, $from = 0, $to = PHP_INT_MAX, $grade = 0){
    global $DB, $CFG;

    $format = "'%Y-%m-%d'";

    $sql = "SELECT attempts.userid as userid, quiz.id as quizid, COUNT(DATE_FORMAT(FROM_UNIXTIME(attempts.timefinish), {$format})) as attempts,
            DATE_FORMAT(FROM_UNIXTIME(attempts.timefinish), {$format}) as finishdate, quiz.name as quizname
            FROM {$CFG->prefix}quiz_attempts as attempts
            INNER JOIN {$CFG->prefix}quiz as quiz on attempts.quiz = quiz.id
            WHERE quiz.course = {$course_id} AND attempts.state = 'finished' AND attempts.timefinish >= {$from} AND attempts.timefinish <= {$to}
            AND attempts.sumgrades/quiz.sumgrades*100 >= {$grade}
            GROUP BY attempts.userid, attempts.quiz, DATE_FORMAT(FROM_UNIXTIME(attempts.timefinish), {$format})
            ORDER BY quiz.id, attempts.timefinish";


    $result = $DB->get_recordset_sql($sql);

    return moodle_recordset_to_array($result);
}





function get_quizes_attempts_stats_by_user($course_id, $user_id, $from = 0, $to = PHP_INT_MAX, $grade = 0){
    global $DB, $CFG;

    $format = "'%Y-%m-%d'";

    $sql = "SELECT attempts.userid as userid, quiz.id as quizid, quiz.name as quizname,
            COUNT(DATE_FORMAT(FROM_UNIXTIME(attempts.timefinish), {$format})) as attempts,
            DATE_FORMAT(FROM_UNIXTIME(attempts.timefinish), {$format}) as finishdate, quiz.name as quizname
            FROM {$CFG->prefix}quiz_attempts as attempts
            INNER JOIN {$CFG->prefix}quiz as quiz on attempts.quiz = quiz.id
            WHERE quiz.course = {$course_id} AND attempts.state = 'finished' AND attempts.timefinish >= {$from} AND attempts.timefinish <= {$to}
            AND attempts.sumgrades/quiz.sumgrades*100 >= {$grade} AND attempts.userid = {$user_id}
            GROUP BY attempts.userid, attempts.quiz, DATE_FORMAT(FROM_UNIXTIME(attempts.timefinish), {$format})
            ORDER BY quiz.id, attempts.timefinish";


    $result = $DB->get_recordset_sql($sql);

    return moodle_recordset_to_array($result);
}



function make_string_for_chart_attempt($attempt, $color, $title = NULL) {
    $attempt_string = "[";
    //$attempt_string .= "'{$attempt["finishdate"]}', '{$attempt["quizname"]}', '{$attempt["attempts"]}'";
    $attempt_string .= "'{$attempt["finishdate"]}', '{$attempt["quizid"]}', '{$attempt["attempts"]}'";
    $attempt_string .= ", {color: '{$color}'";
    if (isset($title) and !empty($title))
        $attempt_string .= ", label:'{$title}'";
    $attempt_string .= "}";
    $attempt_string .= "]";
    return $attempt_string;
}

function make_string_for_chart($all_quiz_attempts, $successful_quiz_attempts) {

    $red = "#FF0000";        //all attempts color
    $green = "#00FF00";      //successful attempt color
    $string_for_chart = "[";

    //var_dump($successful_quiz_attempts);
    foreach($all_quiz_attempts as $quiz_attempt) {
        $succ_attempt = search_array_in_array($successful_quiz_attempts, array('userid'=>$quiz_attempt['userid'],
                                                                               'quizid'=>$quiz_attempt['quizid'],
                                                                               'finishdate'=>$quiz_attempt['finishdate']));

        /*if (sizeof($succ_attempt) < 1) {
            var_dump($succ_attempt);
            echo 'dafuck';
        }*/
        $success_rate = 0;
        if (sizeof($succ_attempt) >= 1) {
            $success_rate = count_quiz_success_rate($quiz_attempt, $succ_attempt[0]);

        }
        //var_dump($success_rate);
        $string_for_chart .= make_string_for_chart_attempt($quiz_attempt, $red, round($success_rate, 2).'%');
    }

    foreach($successful_quiz_attempts as $quiz_succ_attempt) {
        $string_for_chart .= make_string_for_chart_attempt($quiz_succ_attempt, $green);
    }

    $string_for_chart .= "]";
    $pattern = '[\]\[]';
    $replacement = '], [';
    $string_for_chart = preg_replace($pattern, $replacement, $string_for_chart);                //padedami kableliai tarp masyvo elementu(masyvu)
    return $string_for_chart;
}


function get_quizes_in_course($course_id) {
    global $DB;
    $quizes = $DB->get_records('quiz', array('course'=>$course_id));
    return $quizes;
}


function make_string_quizes_in_course($quizes) {
    $string_for_chart = "[";
    $index = 1;

    foreach($quizes as $quiz) {
        //$string_for_chart .= "[{$index}, '{$quiz->name}']";
        $string_for_chart .= "[{$quiz->index}, '{$quiz->name}']";
        //$string_for_chart .= "['{$quiz->name}', '{$quiz->id}']";

        $index++;
    }

    /*$first = true;
    foreach($quizes as $quiz) {

        //$string_for_chart .= "[{$index}, '{$quiz->name}']";
        if ($first)
            $string_for_chart .= "'{$quiz->name}'";
        else
            $string_for_chart .= ", '{$quiz->name}'";
       // $index++;
        $first = false;

    }*/
    $string_for_chart .= "]";
    $pattern = '[\]\[]';
    $replacement = '], [';
    $string_for_chart = preg_replace($pattern, $replacement, $string_for_chart);                //padedami kableliai tarp masyvo elementu(masyvu)
    return $string_for_chart;
}




//$search - array('key'=>'value')
function search_array_in_array($arrays, $search) {
    $result = array();
    foreach($arrays as $array) {
        $found = true;;
        foreach($search as $search_key => $search_value) {
            if ($array[$search_key] != $search_value) {
               $found = false;
                break;
            }
        }
        if ($found)
            array_push($result, $array);
    }
    return $result;
}


//return success rate in percents
function count_quiz_success_rate($total_quiz_info, $successful_quiz_info) {
    return $successful_quiz_info['attempts']/$total_quiz_info['attempts']*100;
}

function test_search_array_in_array() {
    $in = array(array('id'=>3));
    $search = array('id'=>3);
    echo 'hahah';
    var_dump(search_array_in_array($in, $search));


    $in = array(array('id'=>3, 'brum'=>'brum'));
    $search = array('id'=>3, 'brum'=>'brum');
    echo 'hahah';
    var_dump(search_array_in_array($in, $search));

    $in = array(array('id'=>3, 'brum'=>'brum', 'la'=>'la', 'keepsusalive'=>'keepsusalive'));
    $search = array('id'=>3, 'brum'=>'brum', 'la'=>'la');
    echo 'hahah';
    var_dump(search_array_in_array($in, $search));
}

function map_course_quizes($quizes) {
    $index = 0;
    $q =  array();
    foreach ($quizes as $key=>$quiz) {
        $index++;
        $q[$quiz->id] = $quiz;
        $q[$quiz->id]->index = $index;
    }
    return $quizes;
}



//change quizid with new from $new_quizes_ids
function remap_quizes($quizes_attempts, $new_quizes_ids) {
    foreach ($quizes_attempts as $key => $attempt) {
        $quizes_attempts[$key]['quizid'] = $new_quizes_ids[$attempt['quizid']]->index;
    }
    return $quizes_attempts;
}