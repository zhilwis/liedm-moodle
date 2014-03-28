<?php

include_once 'DAL.php';

class ChatAgent {

    private $db;

    function __construct() {
        $this->db = new DAL();
    }

    function __destruct() {
        
    }

    function FillUsers($id) {
        $user = $this->db->GetStudentsByCourse($id);
        for ($i = 0; $i < count($user); $i++) {
            $user[$i]->chats = $this->db->FillUserChatMsgs($id, $user[$i]->id);
            $user[$i]->total_msg_count = 0;
            if ($user[$i]->chats){
                $total = 0;
                for($j = 0; $j < count($user[$i]->chats); $j++){
                    $total += $user[$i]->chats[$j]->msg_count;
                }
                $user[$i]->total_msg_count = $total;
            }
        }
        return $user;
    }

}

?>
