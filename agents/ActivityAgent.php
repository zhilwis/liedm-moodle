<?php

include_once 'DAL.php';
include_once 'DiscussionAgent.php';
include_once 'ChatAgent.php';
include_once 'GradeAgent.php';

class ActivityAgent {

    private $db;

    function __construct() {
        $this->db = new DAL();
    }

    function __destruct() {
        
    }

    function GenerateText($id) {
        $discussions = new DiscussionAgent();
        $chat = new ChatAgent();
        $student_grades = new GradeAgent();

        $posts = $discussions->FillUsers($id);
        $count_chats = $chat->FillUsers($id);
        $grades = $student_grades->FillUsers($id);

        $rez = '';

        for ($i = 0; $i < count($posts); $i++) {

            $rez .= '<p><strong>' . $posts[$i]->first_name . ' ' . $posts[$i]->last_name . '</strong></p>';
            $rez .= '<table><tbody>';
            $rez .= '<tr ><td colspan="2"><strong><i>Pasisakymų skaičius diskusijų forumuose</strong></i></td></tr>';
            foreach ($posts[$i]->posts as $post) {
                $rez .= '<tr style="border:solid 1px black;"><td style="border:solid 1px black;">' . $post->forum_name . '</td><td style="border:solid 1px black;">' . $post->total_post_count . '</td></tr>';
                foreach ($post->discussions as $discussion) {
                    $rez .= '<tr style="font-style:italic; border:solid 1px black;"><td style="padding-left: 20px; border:solid 1px black;">' . $discussion->discussion_name . '</td><td style="border:solid 1px black;">' . $discussion->post_count . '</td></tr>';
                }
            }

            $rez .= '<tr><td style="border:solid 1px black; text-align: right;"><strong>Viso pasisakymų diskusijose:</strong></td><td style="border:solid 1px black;"><strong>' . $posts[$i]->total_post_count . '</strong></td></tr>';
            $rez .= '<tr><td></td><td></td></tr>';
            $rez .= '<tr><td colspan="2"><strong><i>Pasisakymų skaičius pokalbių svetainėse</i></strong></td></tr>';
            foreach ($count_chats[$i]->chats as $chat1) {
                $rez .= '<tr style="border:solid 1px black;"><td style="border:solid 1px black; text-align: left;">' . $chat1->chat_name . '</td><td>' . $chat1->msg_count . '</td></tr>';
            }
            $rez .= '<tr style="border:solid 1px black;"><strong><td style="border:solid 1px black; text-align: right;"><strong>Viso pasisakymų pokalbių svetainėse:</strong></td><td><strong>' . $count_chats[$i]->total_msg_count . '</strong></td></tr>';
            $rez .= '<tr><td></td><td></td></tr>';

            $question_count = $this->db->QuestionsAsked($id, $posts[$i]->id);

            if ($question_count != NULL) {
                $rez .= '<tr><td><strong><i>Dėstytojui užduotų klausimų skaičius:</i></strong></td><td><strong>' . $question_count . '</strong></td></tr>';
            } else {
                $rez .= '<tr><td><strong><i>Dėstytojui užduotų klausimų skaičius:</i></strong></td><td><strong>0</strong></td></tr>';
            }

            $rez .= '<tr><td></td><td></td></tr>';
            $rez .= '<tr><td colspan="2"><strong><i>Studento įvertinimai</i></strong></tr>';
            foreach ($grades[$i]->modules as $grade) {
                $rez .= '<tr style="border:solid 1px black;"><td style="border:solid 1px black;">' . $grade->item_name . '</td><td style="border:solid 1px black;">';
                if ($grade->grade != '') {
                    $rez .= $grade->grade;
                } else {
                    $rez .= '-';
                }'</td></tr>';
            }
            $rez .= '<tr><td style="border:solid 1px black; text-align: right;"><strong>Kurso įvertinimas</strong></td><td style="border:solid 1px black;">' . $grades[$i]->course_grade . '</td></tr>';
            $rez .= '</tbody></table>';
        }
        return $rez;
    }

    //true -> message sent
    //false -> error sending msg
    function SendMessages() {
        try {
            $users = $this->db->GetActivityItems();
            foreach ($users as $user) {
                $send = false;
                if ($user->last_call == NULL) {
                    $send = true;
                } else {
                    $sendDate = date('Y-m-d', strtotime($user->last_call. ' + '.$user->period.' days'));
                    $now = date("Y-m-d");
                    if ($now >= $sendDate){
                        $send = true;
                    }
                }
                if ($send == true) {
                    $email = $this->db->GetUserEmail($user->user_id);
                    $txt = $this->GenerateText($user->course_id);
                    //kurso name - data - moodle ataskaita
                    $subject = $this->db->GetCourseName($user->course_id)." - ".date("Y-m-d")." - moodle ataskaita";
                    mail($email, $subject, $txt);
                    
                    $this->db->UpdateLastCall($user->id);
                }
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}

//TODO istrinti
$test = new ActivityAgent();
$test->SendMessages();
?>
