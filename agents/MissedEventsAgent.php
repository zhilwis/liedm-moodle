<?php

include_once 'DAL.php';

class MissedEventsAgent {

    private $db;

    function __construct() {
        mb_internal_encoding("UTF-8");
        $this->db = new DAL();
    }

    function __destruct() {
        
    }

    function GenerateMessage($events, $count) {
        $res = '';

        if (count($events->quiz) > $count) {
            $res .= '
                Pradelsti testai:
                ';
            foreach ($events->quiz as $item) {
                $res .= $item->name . ', atsiskaitymo data ' . date('Y-m-d', $item->time) . '
                    ';
            }
        }

        if (count($events->assign) > $count) {
            $res .= '
                Pradelsti individualūs atsiskaitymai:
                ';
            foreach ($events->assign as $item) {
                $res .= $item->name . ', atsiskaitymo data ' . date('Y-m-d', $item->time) . '
                    ';
            }
        }

        if (count($events->lesson) > $count) {
            $res .= '
                Pradelstos pamokos:
                ';
            foreach ($events->lesson as $item) {
                $res .= $item->name . ', atsiskaitymo data ' . date('Y-m-d', $item->time) . '
                    ';
            }
        }

        if (count($events->workshop_submissions) > $count) {
            $res .= '
                Pradelsti "workshop" atsiskaitymai:
                ';
            foreach ($events->workshop_submissions as $item) {
                $res .= $item->name . ', atsiskaitymo data ' . date('Y-m-d', $item->time) . '
                    ';
            }
        }

        if (count($events->workshop_assessments) > $count) {
            $res .= '
                Pradelsti "workshop" vertinimai:
                ';
            foreach ($events->workshop_assessments as $item) {
                $res .= $item->name . ', atsiskaitymo data ' . date('Y-m-d', $item->time) . '
                    ';
            }
        }

        return $res;
    }

    function CheckMissedEvents() {
        $courses = $this->db->GetMissedItems();
        foreach ($courses as $course) {
            $send = false;
            if ($course->last_call == NULL) {
                $send = true;
            } else {
                $sendDate = date('Y-m-d', strtotime($course->last_call . ' + ' . $course->period . ' days'));
                $now = date("Y-m-d");
                if ($now >= $sendDate) {
                    $send = true;
                }
            }
            if ($send == true) {
                //TODO atkomentuoti
                $this->db->UpdateMissedEventLastCall($course->id);
                $students = $this->db->GetStudentsByCourse($course->course_id);
                foreach ($students as $student) {
                    $events = $this->db->GetUserMissedEvents($student->id, $course->course_id);
                    $msg = $this->GenerateMessage($events, $course->missed_event);
                    if ($msg != '') {
                        $txt = $student->first_name . ' ' . $student->last_name . '
                            vėlavimų ataskaita
';
                        $txt .= $msg;
                        $subject = $student->first_name . ' ' . $student->last_name . ' vėlavimų ataskaita';

                        //moodle msg
                        global $DB;
                        
                        $user = $DB->get_record('user', array('id'=>$student->id));
                        $eventdata = new object();
                        $eventdata->component = 'moodle';    // the component sending the message. Along with name this must exist in the table message_providers
                        $eventdata->name = 'instantmessage';        // type of message from that module (as module defines it). Along with component this must exist in the table message_providers
                        $eventdata->userfrom = 2;      // user object
                        $eventdata->userto = $student->id;        // user object
                        $eventdata->subject = $subject;   // very short one-line subject
                        $eventdata->fullmessage = $txt;      // raw text
                        $eventdata->fullmessageformat = FORMAT_PLAIN;   // text format
                        $eventdata->fullmessagehtml = $txt;      // html rendered version
                        $eventdata->smallmessage = '';             // useful for plugins like sms or twitter
                        $eventdata->notification = 1;
                        

                        message_send($eventdata);
                        //email
                        $email = $this->db->GetUserEmail($student->id);
                        mail($email, $subject, $txt);
                        
                        $teachers = $this->db->GetTeachersByCourse($course->course_id);
                        foreach ($teachers as $teacher) {
                            $user = $DB->get_record('user', array('id'=>$teacher->id));
                            $eventdata = new object();
                            $eventdata->component = 'moodle';    // the component sending the message. Along with name this must exist in the table message_providers
                            $eventdata->name = 'instantmessage';        // type of message from that module (as module defines it). Along with component this must exist in the table message_providers
                            $eventdata->userfrom = 2;      // user object
                            $eventdata->userto = $teacher->id;        // user object
                            $eventdata->subject = $subject;   // very short one-line subject
                            $eventdata->fullmessage = $txt;      // raw text
                            $eventdata->fullmessageformat = FORMAT_PLAIN;   // text format
                            $eventdata->fullmessagehtml = $txt;      // html rendered version
                            $eventdata->smallmessage = '';             // useful for plugins like sms or twitter
                            $eventdata->notification = 1;

                            message_send($eventdata);

                            $email = $this->db->GetUserEmail($teacher->id);
                            mail($email, $subject, $txt);
                        }
                    }
                }
            }
        }
    }

}

//TODO istrinti
$test = new MissedEventsAgent();
$test->CheckMissedEvents();
?>
