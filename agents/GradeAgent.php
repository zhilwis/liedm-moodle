<?php

include_once 'DAL.php';

class GradeAgent {

    private $db;

    function __construct() {
        $this->db = new DAL();
    }

    function __destruct() {
        
    }

    function FillUsers($id) {
        $user = $this->db->GetStudentsByCourse($id);
        for ($i = 0; $i < count($user); $i++) {
            $user[$i]->modules = $this->db->FillUserGrades($id, $user[$i]->id);
            $user[$i]->course_grade = $this->db->GetUserCourseGrade($id, $user[$i]->id);
        }
        return $user;
    }

}


?>
