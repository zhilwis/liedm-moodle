<?php
include_once 'DAL.php';
$id = required_param('id', PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('Course ID is incorrect');
}

$userid = $USER->id;

require_login($course);

$DAL = new DAL();
$roleid = $DAL->UserRoleSelect($id, $userid);

if (($roleid == 5) || ($roleid == 6) || ($roleid == 7) || ($roleid == 8)) {

    echo 'You do not have permission to delete questions';
} else {
    $question_id = $_GET['questionid'];
    $DAL -> DeleteQuestion($question_id);
    echo '<script>window.location = "../agents/question.php?id=' . $id . '"; </script>';
}
?>
