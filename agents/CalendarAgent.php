<?php

include_once 'DAL.php';

class CalendarAgent {

    private $db;

    function __construct() {
        $this->db = new DAL();
    }

    function __destruct() {
        
    }

    function GetCourses($events) {
        $a = NULL;
        $i = 0;
        $a[$i] = $events[$i]->course_id;
        foreach ($events as $event) {
            foreach ($a as $course) {
                if ($course != $event->course_id) {
                    $i++;
                    $a[$i] = $event->course_id;
                    break;
                }
            }
        }
        return $a;
    }

    function FormMessageByCourse($course, $events) {
        $a = 'Pranešimas apie naujus kurso ' . $this->db->GetCourseName($course) . ' įvykius:
            ';
        foreach ($events as $event) {
            if ($event->course_id == $course) {
                $a .= $event->name . ', pradžia ' . date('Y-m-d H:m:s', $event->start_time) . '
                    ';
            }
        }
        return $a;
    }

    function CheckNewEvents() {
        $events = $this->db->GetNewCalendarEvents();
        if ($events != NULL) {
            $courses = $this->GetCourses($events);
            foreach ($courses as $course) {
                $msg = $this->FormMessageByCourse($course, $events);
                $subject = 'Priminimas apie naujus ' . $this->db->GetCourseName($course) . ' įvykius';
                $users = $this->db->GetNewCalendarEventsReceivers($course);
               
                if ($users != NULL) {
                    foreach ($users as $user) {
                        //moodle msg
                      
                        global $DB;

                        $sender = $this->db->GetTeachersByCourse($course);
                        $rec = $DB->get_record('user', array('id' => 2));
                        $eventdata = new object();
                        $eventdata->component = 'moodle';    // the component sending the message. Along with name this must exist in the table message_providers
                        $eventdata->name = 'instantmessage';        // type of message from that module (as module defines it). Along with component this must exist in the table message_providers
                        $eventdata->userfrom = $rec;      // user object
                        $eventdata->userto = $user;        // user object
                        $eventdata->subject = $subject;   // very short one-line subject
                        $eventdata->fullmessage = $msg;      // raw text
                        $eventdata->fullmessageformat = FORMAT_PLAIN;   // text format
                        $eventdata->fullmessagehtml = $msg;      // html rendered version
                        $eventdata->smallmessage = '';             // useful for plugins like sms or twitter
                        $eventdata->notification = 1;


                        message_send($eventdata);
                        //email
                        $email = $this->db->GetUserEmail($user);
                        mail($email, $subject, $msg);
                    }
                }
            }
            foreach ($events as $event) {
                $this->db->InsertRemindedEvent($event->id);
            }
        }
        $users = $this->db->GetPeriodicReminderUsers();
        $now = date('Y-m-d');
        foreach ($users as $user) {
            $events = $this->db->GetUserEvents($user->course_id);
            if ($events != NULL) {
                foreach ($events as $event) {
                    for ($i = $user->first_event; $i > 0; $i -= $user->second_event) {
                        if ($now == date('Y-m-d', strtotime(date('Y-m-d', $event->start_time) . ' - ' . $i . ' days'))) {

                            $subject = 'Periodinis priminimas apie arėtjantį įvykį';
                            $msg = $event->name . ' (kursas ' . $this->db->GetCourseName($event->course_id) . ') prasideda ' . date('Y-m-d', $event->start_time);

                            //moodle msg
                            global $DB;
                            $sender = $this->db->GetTeachersByCourse($event->course_id);
                            $rec = $DB->get_record('user', array('id' => 2));
                            $eventdata = new object();
                            $eventdata->component = 'moodle';    // the component sending the message. Along with name this must exist in the table message_providers
                            $eventdata->name = 'instantmessage';        // type of message from that module (as module defines it). Along with component this must exist in the table message_providers
                            $eventdata->userfrom = $rec;      // user object
                            $eventdata->userto = $user->id;        // user object
                            $eventdata->subject = $subject;   // very short one-line subject
                            $eventdata->fullmessage = $msg;      // raw text
                            $eventdata->fullmessageformat = FORMAT_PLAIN;   // text format
                            $eventdata->fullmessagehtml = $msg;      // html rendered version
                            $eventdata->smallmessage = '';             // useful for plugins like sms or twitter
                            $eventdata->notification = 1;

                            message_send($eventdata);
                            //email
                            $email = $this->db->GetUserEmail($user->id);
                            mail($email, $subject, $msg);

                            break;
                        }
                    }
                }
            }
        }
    }

}

//TODO istrinti
$test = new CalendarAgent();
$test->CheckNewEvents();
?>
