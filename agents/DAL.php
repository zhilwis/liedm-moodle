<?php

include_once '../config.php';

class DAL {

    private $username; //= $CFG->dbuser;
    private $pass; //= $CFG->dbpass;
    private $host; //= $CFG->dbhost;
    private $name; //= $CFG->dbname;
    private $prefix; //= $CFG->prefix;
    private $con;

    function __construct() {

        mb_internal_encoding("UTF-8");
        
        global $CFG;

        $this->username = $CFG->dbuser;
        $this->pass = $CFG->dbpass;
        $this->host = $CFG->dbhost;
        $this->name = $CFG->dbname;
        $this->prefix = $CFG->prefix;

        $this->con = mysqli_connect($this->host, $this->username, $this->pass, $this->name);

        // Check connection
        if (mysqli_connect_errno($this->con)) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
    }

    function __destruct() {
        mysqli_close($this->con);
    }

    function GetStudentsByCourse($id) {
        $a = NULL;
        //query start
        //roleid 5 = Student
        //contextlevel 50 = CONTEXT_COURSE
        $result = mysqli_query($this->con, "SELECT u.id, u.firstname, u.lastname 
                FROM " . $this->prefix . "role_assignments AS r INNER JOIN " . $this->prefix . "user AS u 
                    ON r.userid = u.id 
                    WHERE r.contextid = (SELECT id FROM " . $this->prefix . "context WHERE instanceid = " . $id . " AND contextlevel = 50) 
                    AND r.roleid = 5");
        //end query
        $j = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a[$j] = new stdClass();
            $a[$j]->id = $row['id'];
            $a[$j]->first_name = $row['firstname'];
            $a[$j]->last_name = $row['lastname'];
            $j++;
        }
        return $a;
    }

    function FillUserPosts($id, $userid) {
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT * FROM " . $this->prefix . "forum WHERE course = " . $id);
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $total = 0;
            $a[$i] = new stdClass();
            $a[$i]->forum_name = $row['name'];
            $a[$i]->forum_id = $row['id'];
            $a[$i]->discussions = NULL;
            $res = mysqli_query($this->con, "SELECT * FROM " . $this->prefix . "forum_discussions WHERE course = " . $id . " AND forum = " . $row['id']);
            $j = 0;
            while ($r = mysqli_fetch_array($res)) {
                $a[$i]->discussions[$j] = new stdClass();
                $a[$i]->discussions[$j]->discussion_id = $r['id'];
                $a[$i]->discussions[$j]->discussion_name = $r['name'];
                $query = mysqli_query($this->con, "SELECT * FROM " . $this->prefix . "forum_posts WHERE discussion = " . $r['id'] . " AND userid = " . $userid);
                $a[$i]->discussions[$j]->post_count = 0;
                if ($query) {
                    $a[$i]->discussions[$j]->post_count = $query->num_rows;
                }
                $total += $a[$i]->discussions[$j]->post_count;
                $j++;
            }
            $a[$i]->total_post_count = $total;
            $i++;
        }
        return $a;
    }

    function FillUserChatMsgs($id, $userid) {
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT * FROM " . $this->prefix . "chat WHERE course = " . $id);
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a[$i] = new stdClass();
            $a[$i]->chat_id = $row['id'];
            $a[$i]->chat_name = $row['name'];
            $query = mysqli_query($this->con, "SELECT cm.id 
                FROM " . $this->prefix . "chat AS c INNER JOIN " . $this->prefix . "chat_messages AS cm 
                    ON c.id = cm.chatid 
                    WHERE c.course = " . $id . " AND cm.userid = " . $userid . " AND cm.system = 0");
            if ($query) {
                $a[$i]->msg_count = $query->num_rows;
            }
        }
        return $a;
    }

    function FillUserGrades($id, $userid) {
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT * FROM " . $this->prefix . "grade_items WHERE courseid = " . $id . " AND itemtype = 'mod'");
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a[$i] = new stdClass();
            $a[$i]->item_id = $row['id'];
            $a[$i]->item_name = $row['itemname'];
            $a[$i]->grade = NULL;
            $res = mysqli_query($this->con, "SELECT * FROM " . $this->prefix . "grade_grades WHERE itemid = " . $row['id'] . " AND userid = " . $userid);
            while ($r = mysqli_fetch_array($res)) {
                $a[$i]->grade = $r['finalgrade'];
            }
            $i++;
        }
        return $a;
    }

    function GetUserCourseGrade($id, $userid) {
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT gg.finalgrade 
                FROM " . $this->prefix . "grade_items AS gi INNER JOIN " . $this->prefix . "grade_grades AS gg 
                    ON gg.itemid = gi.id 
                    WHERE gi.courseid = " . $id . " AND gg.userid = " . $userid . " AND gi.itemtype = 'course'");
        while ($row = mysqli_fetch_array($result)) {
            $a = $row['finalgrade'];
        }
        return $a;
    }

    function GetCourseQuestions($id) {
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT question FROM " . $this->prefix . "questions_agent WHERE course_id = " . $id);
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a[$i] = $row['question'];
            $i++;
        }
        return $a;
    }

    function GetCourseQuestionAnswer($id, $q) {
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT answer FROM " . $this->prefix . "questions_agent WHERE course_id = " . $id . " AND question = '" . $q . "'");
        while ($row = mysqli_fetch_array($result)) {
            $a = $row['answer'];
        }
        return $a;
    }

    function IncreaseQuestionCount($id, $userid) {
        $a = NULL;
        $rowId = NULL;
        $result = mysqli_query($this->con, "SELECT * FROM " . $this->prefix . "questions_asked_agent WHERE course_id = " . $id . " AND user_id = '" . $userid . "'");
        while ($row = mysqli_fetch_array($result)) {
            $a = $row['question_count'];
            $rowId = $row['id'];
        }
        if ($a == NULL) {
            //insert
            mysqli_query($this->con, "INSERT INTO " . $this->prefix . "questions_asked_agent (user_id, course_id, question_count) VALUES (" . $userid . ", " . $id . ", 1)");
        } else {
            //update
            mysqli_query($this->con, "UPDATE " . $this->prefix . "questions_asked_agent SET question_count = " . ($a + 1) . " WHERE id = " . $rowId);
        }
    }

    function GetTeachersByCourse($id) {
        $a = NULL;
        //query start
        //roleid 3 = teacher
        //contextlevel 50 = CONTEXT_COURSE
        $result = mysqli_query($this->con, "SELECT u.id, u.firstname, u.lastname 
                FROM " . $this->prefix . "role_assignments AS r INNER JOIN " . $this->prefix . "user AS u 
                    ON r.userid = u.id 
                    WHERE r.contextid = (SELECT id FROM " . $this->prefix . "context WHERE instanceid = " . $id . " AND contextlevel = 50) 
                    AND r.roleid = 3");
        //end query
        $j = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a[$j] = new stdClass();
            $a[$j]->id = $row['id'];
            $a[$j]->first_name = $row['firstname'];
            $a[$j]->last_name = $row['lastname'];
            $j++;
        }
        return $a;
    }

    /*   function SendMessage($from, $to, $subject, $msg) {
      mysqli_query($this->con, "INSERT INTO " . $this->prefix . "message
      (useridfrom, useridto, subject, fullmessage, smallmessage, timecreated)
      VALUES (" . $from . "," . $to . ",'" . $subject . "','" . $msg . "', '" . $msg . "',UNIX_TIMESTAMP())");
      } */

    function InsertQuestion($question, $answer, $crs) {
        mysql_set_charset('utf8');
        mysqli_query($this->con, "INSERT INTO " . $this->prefix . "questions_agent
			(course_id, question, answer)
				  VALUES
				     (" . $crs . ", '" . $question . "', '" . $answer . "')");
    }

    function UserRoleSelect($id, $userid) {

        $roleid = NULL;

        $result = mysqli_query($this->con, 'select a.roleid from ' . $this->prefix . 'role_assignments as a 
			  inner join ' . $this->prefix . 'context as c on a.contextid = c.id 
			  where c.instanceid = ' . $id . ' and c.contextlevel = 50 and a.userid =' . $userid);

        while ($row = mysqli_fetch_array($result)) {
            $roleid = $row['roleid'];
        }

        return $roleid;
    }

    function GetCourseQuestionsAnswers($id) {
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT id, question, answer FROM " . $this->prefix . "questions_agent WHERE course_id = " . $id);
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a[$i] = new stdClass();
            $a[$i]->id = $row['id'];
            $a[$i]->question = $row['question'];
            $a[$i]->answer = $row['answer'];
            $i++;
        }
        return $a;
    }

    function DeleteQuestion($id) {
        $result = mysqli_query($this->con, "DELETE FROM " . $this->prefix . "questions_agent WHERE id = " . $id);
    }

    function QuestionsAsked($id, $userid) {

        $question_count = NULL;

        $result = mysqli_query($this->con, 'select question_count from ' . $this->prefix . 'questions_asked_agent 
			        		  where user_id = ' . $userid . ' and course_id = ' . $id);

        $row = mysqli_fetch_array($result);
        $question_count = $row['question_count'];


        return $question_count;
    }

    function InsertActivitySettings($period, $crs, $userid) {
        
        $current_time = date('Y-m-d H:i:s');
        
        $result = mysqli_query($this->con, 'select user_id, course_id from ' . $this->prefix . 'activity_agent 
			        		  where user_id = ' . $userid . ' and course_id = ' . $crs);

        $row = mysqli_fetch_array($result);
        
        if ($row != NULL) {
        
        $result2 = mysqli_query($this->con, 'UPDATE ' . $this->prefix . 'activity_agent SET
			period = '.$period.', start_time = "'.$current_time.'"
			where user_id = '.$userid.' and course_id = '.$crs);

        
        } else {
            
        $result1 = mysqli_query($this->con, 'INSERT INTO ' . $this->prefix . 'activity_agent
			(user_id, course_id, period, start_time)
				  VALUES
				     (' . $userid . ',' . $crs . ',' . $period . ',"'. $current_time .'")');
        
        }
        
    }
    
    function InsertMissedEventSettings($missed_event, $crs, $period) {
        
        $current_time = date('Y-m-d H:i:s');
        
        $result = mysqli_query($this->con, 'select course_id from ' . $this->prefix . 'missed_event_agent 
			        		  where course_id = ' . $crs);

        $row = mysqli_fetch_array($result);
        
        if ($row != NULL) {
            
            $result2 = mysqli_query($this->con, 'UPDATE ' . $this->prefix . 'missed_event_agent SET
			missed_event = '.$missed_event.', period = '.$period.', start_time = "'.$current_time.'"
			where course_id = '.$crs);
        
        
        } else {
            
            $result1 = mysqli_query($this->con, 'INSERT INTO ' . $this->prefix . 'missed_event_agent
			(course_id, missed_event, period, start_time)
				  VALUES
				     (' . $crs . ',' . $missed_event . ', ' .$period. ' , "' .$current_time. '")');
            
        }
        
    }
    
    function InsertNewEventSettings($new_event, $crs, $userid) {
        
        $result = mysqli_query($this->con, 'select course_id, user_id from ' . $this->prefix . 'new_event_agent 
			        		  where course_id = ' . $crs .' and user_id = '.$userid);
        
        $row = mysqli_fetch_array($result);
        
        
        
        if ($row != NULL) {
            
            $result2 = mysqli_query($this->con, 'UPDATE ' . $this->prefix . 'new_event_agent SET
			new_event = '.$new_event.'
			where user_id = '.$userid.' and course_id = '.$crs);
            
        } else {
        
            $result1 = mysqli_query($this->con, 'INSERT INTO ' . $this->prefix . 'new_event_agent
			(user_id, course_id, new_event)
				  VALUES
				     (' . $userid . ',' . $crs . ', '.$new_event.')');   
        }
    }
    
    function InsertUpcomingEventSettings($first_event, $second_event, $crs, $userid) {
        
        $result = mysqli_query($this->con, 'select user_id, course_id from ' . $this->prefix . 'upcoming_event_agent 
                     where user_id = '.$userid.' and course_id = '.$crs);
        
        $row = mysqli_fetch_array($result);
        
        if ($row != NULL) {
            
            $result2 = mysqli_query($this->con, 'UPDATE ' . $this->prefix . 'upcoming_event_agent SET
			first_event = '.$first_event.', second_event = '.$second_event.'
			where user_id = '.$userid.' and course_id = '.$crs);
            
        } else {       
        
            $result = mysqli_query($this->con, 'INSERT INTO ' . $this->prefix . 'upcoming_event_agent
			(user_id, course_id, first_event, second_event)
				  VALUES
				     (' . $userid . ',' . $crs . ',' . $first_event . ',"' . $second_event . '")');
        }
        
    }
    
    function GetActivityItems(){
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT * 
                FROM " . $this->prefix . "activity_agent");
        //end query
        $j = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a[$j] = new stdClass();
            $a[$j]->id = $row['id'];
            $a[$j]->user_id = $row['user_id'];
            $a[$j]->course_id = $row['course_id'];
            $a[$j]->period = $row['period'];
            $a[$j]->last_call = $row['last_call'];
            $a[$j]->start_time = $row['start_time'];
            $j++;
        }
        return $a;
    }
    
    function GetUserEmail($id){
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT email 
                FROM " . $this->prefix . "user WHERE id = ".$id);
        while ($row = mysqli_fetch_array($result)) {
            $a = $row['email'];
        }
        return $a;
    }
    
    function UpdateLastCall($id){
        mysqli_query($this->con, "UPDATE " . $this->prefix . "activity_agent SET last_call = NOW() WHERE id = " . $id);
    }
    
    function GetCourseName($id){
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT fullname 
                FROM " . $this->prefix . "course WHERE id = ".$id);
        while ($row = mysqli_fetch_array($result)) {
            $a = $row['fullname'];
        }
        return $a;
    }
    
    function UpdateMissedEventLastCall($id){
        mysqli_query($this->con, "UPDATE " . $this->prefix . "missed_event_agent SET last_call = NOW() WHERE id = " . $id);
    }
    
    function GetUserMissedEvents($userId, $courseId){
        $a = new stdClass();
        $a->quiz = NULL;
        $a->assign = NULL;
        $a->lesson = NULL;
        $a->workshop_submissions = NULL;
        $a->workshop_assessments = NULL;
        //fill quiz
        $result = mysqli_query($this->con, "SELECT q.id, q.name, q.timeclose 
                FROM " . $this->prefix . "quiz as q inner join " . $this->prefix . "quiz_attempts as a
                    on q.id = a.quiz WHERE q.course = ".$courseId . " AND a.userid = ".$userId." AND a.state <> 'finished' AND q.timeclose < NOW() AND q.timeclose > 0");
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a->quiz[$i] = new stdClass();
            $a->quiz[$i]->id = $row['id']; 
            $a->quiz[$i]->name = $row['name'];
            $a->quiz[$i]->time = $row['timeclose'];
            $i++;
        }
        
        //fill assign
        $result = mysqli_query($this->con, "SELECT a.id, a.name, a.duedate 
                FROM " . $this->prefix . "assign as a inner join " . $this->prefix . "assign_submission as s
                    on a.id = s.assignment WHERE a.course = ".$courseId . " AND a.duedate < NOW() AND a.duedate > 0 AND NOT EXISTS (select userid 
                from " . $this->prefix . "assign_submission 
                where userid = ".$userId.") 
                        GROUP BY a.id");
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a->assign[$i] = new stdClass();
            $a->assign[$i]->id = $row['id']; 
            $a->assign[$i]->name = $row['name'];
            $a->assign[$i]->time = $row['duedate'];
            $i++;
        }
        
        //fill lesson
        $result = mysqli_query($this->con, "SELECT a.id, a.name, a.deadline 
                FROM " . $this->prefix . "lesson as a inner join " . $this->prefix . "lesson_timer as t
                    on a.id = t.lessonid WHERE a.course = ".$courseId . " AND a.deadline < NOW() AND a.deadline > 0 AND NOT EXISTS (select userid 
                from " . $this->prefix . "lesson_timer 
                where userid = ".$userId.") 
                        GROUP BY a.id");
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a->lesson[$i] = new stdClass();
            $a->lesson[$i]->id = $row['id']; 
            $a->lesson[$i]->name = $row['name'];
            $a->lesson[$i]->time = $row['deadline'];
            $i++;
        }
        
        //fill workshop submissions
        $result = mysqli_query($this->con, "SELECT a.id, a.name, a.submissionend 
                FROM " . $this->prefix . "workshop as a inner join " . $this->prefix . "workshop_submissions as t
                    on a.id = t.workshopid WHERE a.course = ".$courseId . " AND a.submissionend < NOW() AND a.submissionend > 0 AND NOT EXISTS (select authorid 
                from " . $this->prefix . "workshop_submissions 
                where authorid = ".$userId.") 
                        GROUP BY a.id");
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a->workshop_submissions[$i] = new stdClass();
            $a->workshop_submissions[$i]->id = $row['id']; 
            $a->workshop_submissions[$i]->name = $row['name'];
            $a->workshop_submissions[$i]->time = $row['submissionend'];
            $i++;
        }
        
        //fill workshop assessements
        $result = mysqli_query($this->con, "SELECT b.id, b.name, b.assessmentend 
                FROM " . $this->prefix . "workshop as b inner join " . $this->prefix . "workshop_submissions as a on b.id = a.workshopid
                     inner join " . $this->prefix . "workshop_assessments as t
                    on a.id = t.submissionid WHERE b.course = ".$courseId . " AND b.assessmentend < NOW() AND b.assessmentend > 0 AND NOT EXISTS (select reviewerid 
                from " . $this->prefix . "workshop_assessments 
                where reviewerid = ".$userId.") 
                        GROUP BY a.id");
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a->workshop_assessments[$i] = new stdClass();
            $a->workshop_assessments[$i]->id = $row['id']; 
            $a->workshop_assessments[$i]->name = $row['name'];
            $a->workshop_assessments[$i]->time = $row['assessemntend'];
            $i++;
        }
        
        return $a;
    }
    
    function GetMissedItems(){
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT * 
                FROM " . $this->prefix . "missed_event_agent");
        //end query
        $j = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a[$j] = new stdClass();
            $a[$j]->id = $row['id'];
            $a[$j]->missed_event = $row['missed_event'];
            $a[$j]->course_id = $row['course_id'];
            $a[$j]->period = $row['period'];
            $a[$j]->last_call = $row['last_call'];
            $a[$j]->start_time = $row['start_time'];
            $j++;
        }
        return $a;
    }
    
    function GetSettings($userid, $crs) {
        
        $a = new stdClass();
        $a->missed_settings = new stdClass();
        $a->missed_settings -> missed_event = 0;
        $a->activity_settings = new stdClass();
        $a->activity_settings -> period = 0;
        $a->new_event_settings = new stdClass();
        $a->new_event_settings->new_event = 0;
        $a->upcoming_event_settings = new stdClass();
        $a->upcoming_event_settings -> first_event = 0;
        $a->upcoming_event_settings -> second_event = 0;
        
        
        $result = mysqli_query($this->con, "SELECT missed_event, period
            FROM " . $this->prefix . "missed_event_agent 
                WHERE course_id = ".$crs);
        
        while ($row = mysqli_fetch_array($result)) {
            $a->missed_settings = new stdClass();
            $a->missed_settings->missed_event = $row['missed_event'];
            $a->missed_settings->period = $row['period'];
        }
        
       
        $result = mysqli_query($this->con, "SELECT period 
            FROM " . $this->prefix . "activity_agent 
                WHERE course_id = ".$crs." and user_id = ".$userid);
         
       
        while ($row = mysqli_fetch_array($result)) {
            $a->activity_settings = new stdClass();
            $a->activity_settings->period = $row['period'];  
        }
        
        
        $result = mysqli_query($this->con, "SELECT new_event 
            FROM " . $this->prefix . "new_event_agent 
                WHERE course_id = ".$crs." and user_id = ".$userid);
                
        
            
        
            while ($row = mysqli_fetch_array($result)) {    
                
                $a->new_event_settings = new stdClass();
                $a->new_event_settings->new_event = $row['new_event'];
            }
      
         
        
        
        
   
        $result = mysqli_query($this->con, "SELECT first_event, second_event 
            FROM " . $this->prefix . "upcoming_event_agent 
                WHERE course_id = ".$crs." and user_id = ".$userid);
         
       
        while ($row = mysqli_fetch_array($result)) {
            $a->upcoming_event_settings = new stdClass();
            $a->upcoming_event_settings->first_event= $row['first_event']; 
            $a->upcoming_event_settings->second_event= $row['second_event'];  
        }
         
        return $a;
        
        
    }
    
    function GetStudentSettings($userid, $crs) {
        
        $a = new stdClass();
        $a->new_event_settings = new stdClass();
        $a->new_event_settings->new_event = 0;
        $a->upcoming_event_settings = new stdClass();
        $a->upcoming_event_settings -> first_event = 0;
        $a->upcoming_event_settings -> second_event = 0;
        
        $result = mysqli_query($this->con, "SELECT new_event 
            FROM " . $this->prefix . "new_event_agent 
                WHERE course_id = ".$crs." and user_id = ".$userid);
                
        while ($row = mysqli_fetch_array($result)) {
            $a->new_event_settings = new stdClass();
            $a->new_event_settings->new_event = $row['new_event'];  
        }
        
        
        $result = mysqli_query($this->con, "SELECT first_event, second_event 
            FROM " . $this->prefix . "upcoming_event_agent 
                WHERE course_id = ".$crs." and user_id = ".$userid);
         
       
        while ($row = mysqli_fetch_array($result)) {
            $a->upcoming_event_settings = new stdClass();
            $a->upcoming_event_settings->first_event= $row['first_event']; 
            $a->upcoming_event_settings->second_event= $row['second_event'];  
        }
         
        return $a;
        
        
    }

    function GetNewCalendarEvents(){
        $a = NULL;
        
        $result = mysqli_query($this->con, "SELECT * 
            FROM " . $this->prefix . "event_tracking_agent LIMIT 1");
        
        while ($row = mysqli_fetch_array($result)) {
            $a = 1;
        }
        
        if($a == NULL){
            //fill table with initial values
            $result = mysqli_query($this->con, "SELECT id 
            FROM " . $this->prefix . "event");
            while ($row = mysqli_fetch_array($result)) {
                mysqli_query($this->con, "INSERT INTO " . $this->prefix . "event_tracking_agent (event_id) VALUES (" . $row['id'] . ")");
            }
            return $a;
        }else{
            $a = NULL;
        }
        
        $result = mysqli_query($this->con, "SELECT * 
            FROM " . $this->prefix . "event WHERE id NOT IN (SELECT event_id FROM " . $this->prefix . "event_tracking_agent)");
        
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a[$i] = new stdClass();
            $a[$i]->id = $row['id'];
            $a[$i]->course_id = $row['courseid'];
            $a[$i]->name = $row['name'];
            $a[$i]->start_time = $row['timestart'];
            $i++;
        }
        
        return $a;
    }
    
    function GetNewCalendarEventsReceivers($courseId){
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT user_id 
            FROM " . $this->prefix . "new_event_agent WHERE new_event = 1");
        
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a[$i] = $row['user_id'];
            $i++;
        }
        
        return $a;
    }
    
    function InsertRemindedEvent($id){
        mysqli_query($this->con, "INSERT INTO " . $this->prefix . "event_tracking_agent (event_id) VALUES (" . $id . ")");
    }
    
    function GetPeriodicReminderUsers(){
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT * 
            FROM " . $this->prefix . "upcoming_event_agent");
        
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a[$i] = new stdClass();
            $a[$i]->id = $row['user_id'];
            $a[$i]->course_id = $row['course_id'];
            $a[$i]->first_event = $row['first_event'];
            $a[$i]->second_event = $row['second_event'];
            $i++;
        }
        
        return $a;
    }
    
    function GetUserEvents($course){
        $a = NULL;
        $result = mysqli_query($this->con, "SELECT * 
            FROM " . $this->prefix . "event WHERE courseid = ".$course);
        
        $i = 0;
        while ($row = mysqli_fetch_array($result)) {
            $a[$i] = new stdClass();
            $a[$i]->id = $row['id'];
            $a[$i]->course_id = $row['courseid'];
            $a[$i]->name = $row['name'];
            $a[$i]->start_time = $row['timestart'];
            $i++;
        }
        
        return $a;
    }
}

?>
