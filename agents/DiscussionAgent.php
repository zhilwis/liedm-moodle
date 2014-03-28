<?php

include_once 'DAL.php';

class DiscussionAgent {

    private $db;

    function __construct() {
        $this->db = new DAL();
    }

    function __destruct() {
        
    }

    function FillUsers($id) {
        $user = $this->db->GetStudentsByCourse($id);
        for ($i = 0; $i < count($user); $i++) {
            $user[$i]->posts = $this->db->FillUserPosts($id, $user[$i]->id);
            $total = 0;
            foreach ($user[$i]->posts as $post){
                $total += $post->total_post_count;
            }
            $user[$i]->total_post_count = $total;
        }
        return $user;
    }

}

?>
