<?php
require_once('../config.php');
include_once 'DAL.php';

global $DB;

$DAL = new DAL();

$id = required_param('id', PARAM_INT);           // Course ID
// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('Course ID is incorrect');
}

require_login($course);
$PAGE->set_url('/ask_question.php', array('id' => $cm->id));
$PAGE->set_pagelayout('standard');
$PAGE->set_title($COURSE->fullname);
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add(get_string('questionforteacher', 'block_agents_for_student'), new moodle_url('../agents/ask_question.php?id=' . $_GET['id']));

$userid = $USER->id;

echo $OUTPUT->header();

if (isset($_POST["question"])) {
    $DAL->IncreaseQuestionCount($id, $userid);
    echo '<p style="text-align:center;"><strong>' . $_POST["question"] . '</strong></p>';
    $ans = $DAL->GetCourseQuestionAnswer($id, $_POST["question"]);
    if ($ans == NULL) {
        $teacher = $DAL->GetTeachersByCourse($id);

        $msg = 'Kurso ' . $COURSE->fullname . ' studentas uždavė šį klausimą:' . $_POST['question'];
        $subject = "Question agent: new question on course " . $COURSE->fullname;

        $msg_html = '<p>Kurso ' . $COURSE->fullname . ' studentas uždavė šį klausimą: <a href="../agents/question.php?id=' . $_GET['id'] . '">' . $_POST['question'] . '</a></p>';
        $url='../agents/question.php?id=' . $_GET['id'];
        $url_name = 'Įtraukti klausimą į DUK';
        
        $userid = $USER->id;
        
        $user = $DB->get_record('user', array('id'=>$USER->id));

        for ($i = 0; $i < count($teacher); $i++) {
                     
            $eventdata = new object();
            $eventdata->component = 'moodle';    // the component sending the message. Along with name this must exist in the table message_providers
            $eventdata->name = 'instantmessage';        // type of message from that module (as module defines it). Along with component this must exist in the table message_providers
            $eventdata->userfrom = $user;      // user object
            $eventdata->userto = $teacher[$i]->id;        // user object
            $eventdata->subject = $subject;   // very short one-line subject
            $eventdata->fullmessage = $msg;      // raw text
            $eventdata->fullmessageformat = FORMAT_PLAIN;   // text format
            $eventdata->fullmessagehtml = $msg_html;      // html rendered version
            $eventdata->contexturl = $url;
            $eventdata->contexturlname = $url_name;
            $eventdata->smallmessage = '';             // useful for plugins like sms or twitter
            $eventdata->notification = 1;
            

            $result = message_send($eventdata);
        }
        echo '<p style="text-align:center;">Deja atsaykymas į Jūsų pateiktą klausimą nebuvo rastas. Klausimas persiųstas kurso dėstytojui.</p>';
    }
    else
        echo '<p style="text-align:center;">Atsakymas į Jūsų pateiktą klausimą:</p><p style="text-align:center;"><strong>' . $ans . '</strong></p>';
    ?>
    <form style="text-align:center;" name="forma1" method="post" action="" >
        <input name="submit" type="submit" value="Pateikti kitą klausimą" />
    </form>
    <?php
} else {//start else
    ?>

    <form name="forma1" method="post" action="" >

        <div align = "center">
            <strong>Pradėkite rašyti klausimą</strong>
            <br>
            <br>
            <table>
                <tr>
                    <td>Klausimas:</td>
                    <td><input name="question" list="questions" placeholder="įveskite savo klausimą čia" type="text" size="58" maxlength="100" />
                        <datalist id="questions">
    <?php
    $questionList = $DAL->GetCourseQuestions($id);
    for ($i = 0; $i < count($questionList); $i++) {
        echo '<option value = "' . $questionList[$i] . '">' . $questionList[$i] . '</option>';
    }
    ?>
                        </datalist>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center"><input name="submit" type="submit" value="Siųsti klausimą" /></td>
                </tr>

            </table>
        </div>

    </form>

    <?
}//end else
echo $OUTPUT->footer();
?>