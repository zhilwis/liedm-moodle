<?php
include_once 'DAL.php';



$id = required_param('id', PARAM_INT);           // Course ID
// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('Course ID is incorrect');
}

require_login($course);
$PAGE->set_url('/question.php', array('id' => $cm->id));
$PAGE->set_pagelayout('standard');
$PAGE->set_title($COURSE->fullname);
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add(get_string('addnewfaq', 'block_agents_for_teacher'), new moodle_url('../agents/question.php?id=' . $_GET['id']));

$question = $_POST['question'];
$answer = $_POST['answer'];
$crs = $id;
$userid = $USER->id;

$DAL = new DAL();
$roleid = $DAL->UserRoleSelect($id, $userid);


if (($roleid == 5) || ($roleid == 6) || ($roleid == 7) || ($roleid == 8)) {

    echo '<script>window.location = "../course/view.php?id=' . $id . '"; </script>';
} else {

    echo $OUTPUT->header();
    ?>

    <form name="forma1" method="post" action="" >

        <div align = "center">
            <strong>Įtraukti klausimą į DUK</strong>
            <br>
            <br>
            <table>
                <tr>
                    <td>Klausimas</td>
                    <td><input name="question" type="text" size="58" maxlength="100" /></td>
                </tr>
                <tr>
                    <td>Atsakymas</td>
                    <td><textarea name="answer" cols="60" rows="5"></textarea></td>
                </tr>
                <tr>
                    <td colspan="2" align="center"><input name="submit" type="submit" value="Itraukti" /></td>
                </tr> 
            </table>
        </div>

    </form>


    <?
    if (isset($_POST['submit'])) {

        if (($question != "") && ($answer != "")) {

            $DAL = new DAL();

            $ans = $DAL->GetCourseQuestionAnswer($crs, $question);
            
            if ($ans == NULL) {
                
                $DAL->InsertQuestion($question, $answer, $crs);
               
                
                echo '<p align="center">The question has been successfully added to FAQ.</p>';
            } else {
                echo '<p style="text-align:center;">This question already exists in database.</p>';
            }
        } else {
            echo '<p align="center" style="color: red;"><strong>Type question and answer.</strong></p>';
        }
    }
    
    echo '<p><strong>Įvesti klausimai</strong></p>';
    $questionsdb = $DAL->GetCourseQuestionsAnswers($crs);
    ?>
<table style="border:solid 1px black;" width="100%">
    <tr style="border:solid 1px black;">
        <td style="border:solid 1px black;"><strong>Klausimas</strong></td>
        <td style="border:solid 1px black;"><strong>Atsakymas</strong></td>
        <td> </td>
    </tr>
 <?   for($i=0; $i<count($questionsdb); $i++){
    echo '<tr style="border:solid 1px black;">';
    echo '<td style="border:solid 1px black;">'.$questionsdb[$i]->question;'</a></td>';
    echo '<td style="border:solid 1px black;">'.$questionsdb[$i]->answer;'</td>';
    echo '<td style="border:solid 1px black;" width="15%"><a href="../agents/deletequestion.php?id=' . $_GET['id'].'&questionid='.$questionsdb[$i]->id.'">Pašalinti klausimą</td>';
    '</tr>';
    }
 ?>
</table>

<?    
    echo $OUTPUT->footer();
}
?>