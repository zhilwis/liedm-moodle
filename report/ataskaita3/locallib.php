<?php







class TimeSpent {

    private $clicks_times = array();  //vartotojo atliktu paspaudimu laikai array($user_id=>array( array(module=> , time=>))))
    private $time_spent = array();  //vartotoju praleistas laikas moduliu medziagoje array($user_id=>array($module_id=>array($date=>$time_spent))) tikriausiai jau nebe module_id o module_name
    private $users = array();       //array($user1, $user2,...))
    private $course = -1;
    private $from = 0;              //data nuo (timestamp)
    private $to = PHP_INT_MAX;      //data iki (timestamp)
    private $time_spent_in_modules = array();  //kiek laiko praleista kiekviename modulyje(activity)

    /*
    function __construct($users, $course, $from = 0, $to = PHP_INT_MAX) {
        $this->set_parameters($users, $course, $from, $to);
    }
    */
    public function set_parameters($users, $course, $from = 0, $to = PHP_INT_MAX) {
        $this->users = $users;
        $this->course = $course;
        $this->from = $from;
        $this->to = $to;

        if (sizeof($this->users) > 0) {
            if (is_array($this->users[0])) {
                $usr = array();
                foreach ($this->users as $user) {
                    array_push($usr, $user['userid']);
                }
                $this->users = $usr;
            }
        }
       // var_dump($this->users);

    }

    /*
     * gauna duomenis is duomenu bazes
     */
    private function get_clicks_times() {
        global $DB;
        global $CFG;

        $sql = "SELECT log.userid, log.module, log.time  FROM {$CFG->prefix}log log
                WHERE log.time > {$this->from} and log.time < {$this->to} and log.course = {$this->course}";

        //--------------konkretiem vartotojam----------------
        if (sizeof($this->users)>0) {
            $sql .= " and (";
            $first = true;
            foreach($this->users as $user) {
                if ($first) {
                    $sql .= "log.userid = " . $user;
                    $first = false;
                } else {
                    $sql .= " or log.userid = " . $user;
                }
            }
            $sql .= ")";
        }
        //---------------------------------------------------
        $sql .= " ORDER BY log.time ASC";


        $db_clicks_times = $DB->get_recordset_sql($sql);

        foreach($db_clicks_times as $db_click_time) {
            if (!isset($this->clicks_times[$db_click_time->userid])) {
                $this->clicks_times[$db_click_time->userid] = array();
            }
            array_push($this->clicks_times[$db_click_time->userid],
                array('module'=>$db_click_time->module, 'time'=>$db_click_time->time));
        }
    }

    /*
     * pagrazina kiek po vieno paspaudimo uzskaityti vartotojui laiko
     */
    private function get_module_time($module_id) {
        //TODO: kiekvienam moduliui atskirai nustatomus laikus
        $time = get_config('report_ataskaita3', 'default_module_time');
        return $time;
    }

    /*
     * turi buti: $time2>time1
     * paskaiciuoja laiko tarpa tarp dvieju laiku
     * jeigu mazesnis nei duota paskaiciuoja skirtuma,
     * jeigu didesnis grazina moduliui nustatyta laika
     */
    private function time_spent($time1, $time2, $module_id) {
        $diff = intval($time2) - intval($time1);
        $module_time = $this->get_module_time($module_id);
        if($diff > $module_time) {
            return $module_time;
        } else {
            return $diff;
        }

    }

    /*
     * Skaiciuoja laika praleista modulyje
     */
    private function add_time_spent_to_module($module_name, $time_spent, $time) {
        $format = "Y-m-d";
        $date = date($format, $time);
        if (!isset($this->time_spent_in_modules[$module_name])) {
            $this->time_spent_in_modules[$module_name] = array();
        }
        if (!isset($this->time_spent_in_modules[$module_name][$date])) {
            $this->time_spent_in_modules[$module_name][$date] = 0;
        }
        $this->time_spent_in_modules[$module_name][$date] += $time_spent;
    }

    /*
     * grazina masyva su vartotojo praleistais laikais moduliuose
     * array($module_id=>$time, ...)
     */
    private function count_user_spent_time($user_clicks_times) {

        $format = "Y-m-d";

        array_push($user_clicks_times, array('module'=>-1, 'time'=>PHP_INT_MAX));
        $module = -1;
        $time = 0;
        $time_spent_in_modules = array();
        foreach ($user_clicks_times as $user_module_click_time) {
            $time_spent = TimeSpent::time_spent($time, $user_module_click_time['time'], $module);

            if ($module != -1) {

                TimeSpent::add_time_spent_to_module($module, $time_spent, $time);
                if (!isset($time_spent_in_modules[$module])) {
                    $time_spent_in_modules[$module] = array();
                    $time_spent_in_modules[$module][date($format, $time)] = $time_spent;
                } else {
                    if (isset($time_spent_in_modules[$module][date($format, $time)])) {
                        $time_spent_in_modules[$module][date($format, $time)] += $time_spent;
                    } else {
                        $time_spent_in_modules[$module][date($format, $time)] = $time_spent;
                    }
                }
            }
            $module = $user_module_click_time['module'];
            $time = $user_module_click_time['time'];
        }

        return $time_spent_in_modules;
    }

    /*
     * suskaiciuoja kiek naudotojas praleido ziuredamas moduliu medziaga
     */
    public function count_spent_time() {
        TimeSpent::get_clicks_times();
        foreach ($this->clicks_times as $user_id=>$user_clicks_times) {
            $this->time_spent[$user_id] = $this->count_user_spent_time($user_clicks_times);
        }
        
    }

    /*
     * grazina kiek vartotojas laiko praleido prie modulio medziagos kiekviena diena
     */
    public function get_user_spent_time($user_id, $module_id) {
        return $this->clicks_times[$user_id][$module_id];
    }

    /*
     *grazina visu vartotoju praleistus laikus moduliuose
     */
    public function get_all_users_spent_time() {
        return $this->time_spent;
    }


    public function get_string_time_spent_in_module($module_name) {
        if (array_key_exists($module_name, $this->time_spent_in_modules)) {
            $result = "[";
            $time_spent_in_module = $this->time_spent_in_modules[$module_name];
            foreach ($time_spent_in_module as $date => $time_spent_day) {
                $result .=  "[\"" . $date . "\", " . $time_spent_day . "]";
            }
            $result .= "]";
            $pattern = '[\]\[]';
            $replacement = '], [';
            $result = preg_replace($pattern, $replacement, $result);
            return $result;
        } else {
            return '[]';
        }
    }

    public function clean() {
        $this->clicks_times = array();
        $this->time_spent = array();
        $this->users = array();
        $this->course = -1;
        $this->from = 0;
        $this->to = PHP_INT_MAX;
        $this->time_spent_in_modules = array();
    }

}



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
function get_module_click_count($course_id, $module_id, $users_ids,$from = 0, $to = PHP_INT_MAX) {
    global $DB;
    global $CFG;

    $format = "'%Y-%m-%d'";

   // $module_name = get_module_name($module_id);

    $sql = "SELECT COUNT(DATE_FORMAT(FROM_UNIXTIME(log.time), {$format})) as amount, DATE_FORMAT(FROM_UNIXTIME(log.time), {$format}) as count_date
            FROM {$CFG->prefix}log log INNER JOIN {$CFG->prefix}modules modules on log.module = modules.name
            WHERE log.time > {$from} and log.time < {$to} and log.course = {$course_id} and modules.id = {$module_id}";

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
    $sql .= " GROUP BY DATE_FORMAT(FROM_UNIXTIME(log.time), {$format})
             ORDER BY log.time ASC; ";
    //   if ($module_id == 26) echo $sql;
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

function get_module_name($module_id) {
    global $DB;
    $result = $DB->get_record('modules', array('id'=>$module_id));
    return $result->name;
}


//return string which look like js array
function clicks_array_to_string($clicks, $module_id) {
  //  var_dump($module_id);
    $result = '[';
    //foreach($clicks as $user_clicks) {
     //       $result .= '[';
            $user_clicks = $clicks[$module_id];
            $user_string = '';
            foreach($user_clicks as $user_clicks_in_day) {
                $user_string .= '["'.$user_clicks_in_day['count_date']. '", '.$user_clicks_in_day['amount'].']';
            }
            $result .= $user_string;
            $result .= ']';
    //}
    //$result .= ']';

    $pattern = '[\]\[]';
    $replacement = '], [';
    $result = preg_replace($pattern, $replacement, $result);                //padedami kableliai tarp masyvo elementu(masyvu)
    return $result;
}



class StatisticFromDailyLog {
    private $course = 0;
    private $user_ids = array();        //masyvas is vartotoju id
    private $module_id = 0;
    private $data = null;


    public function __construct($course, $user_ids, $module_id) {
        $this->course = $course;
        $this->user_ids = $user_ids;
        $this->module_id = $module_id;

    }

    public function getData() {
        global $DB, $CFG;

        $module_name = get_module_name($this->module_id);

        $where = "(";
        $first = true;
        foreach($this->user_ids as $user_id) {
            $where .= ($first ?" log.user_id = {$user_id}" : " OR log.user_id = {$user_id}");
            $first = false;
        }
        $where .= ")";

        $sql = "SELECT log.date, SUM(log.amount) AS amount, SUM(log.spent_time) AS spent_time
                FROM {$CFG->prefix}daily_log AS log
                WHERE {$where} AND log.course = {$this->course} AND log.module = \"{$module_name}\"
                GROUP BY log.course, log.date, log.module";


        $data = moodle_recordset_to_array($DB->get_recordset_sql($sql));
        $this->data = $data;

        if ($data)
            return true;
        return false;
    }

    public function getSpentTimeString() {

        $str = "[";
        foreach($this->data as $obj) {
            $str .= "[\"" . $obj["date"] . "\", " . $obj["spent_time"]. "]";
        }
        $str .= "]";
        $pattern = '[\]\[]';
        $replacement = '], [';
        $str = preg_replace($pattern, $replacement, $str);
        return $str;
    }

    public function getClicksAmountString() {
        $str = "[";
        foreach($this->data as $obj) {
            $str .= "[\"" . $obj["date"] . "\", " . $obj["amount"] . "]";
        }
        $str .= "]";
        $pattern = '[\]\[]';
        $replacement = '], [';
        $str = preg_replace($pattern, $replacement, $str);
        return $str;
    }

}



//laikotarpio tarp pasirinkimu tikrinimas grafiko atvaizdavimui
function ataskaita3_get_duration($from_date, $to_date){
    $time_duration_sec = $to_date - $from_date;
    $time_duration = floor($time_duration_sec/(60*60*24));
    return $time_duration;
}


function ataskaita3_get_ticks($from, $to) {

    $tick_number = 30;
    $time = ataskaita3_get_duration($from, $to);
    if($time<=30){
        $tick_number = $time+1;
    }
    return $tick_number;
}

function ataskaita3_get_ticks_interval($from, $to) {
    $tick_number = 30.0;
    //var_dump($from);
    $time = ataskaita3_get_duration($from, $to);
    $interval = ceil($time / $tick_number);
    return $interval;
}