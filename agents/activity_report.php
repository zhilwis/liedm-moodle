<script>
    function SwitchContent(index) {
        var img = document.getElementById("studentSwitch" + index);
        var div = document.getElementById("content" + index);
        if (div) {
            if (div.style.display == "none") {
                div.style.display = "inherit";
                img.src = "../pix/t/switch_minus.png";
            } else {
                div.style.display = "none";
                img.src = "../pix/t/switch_plus.png";
            }
        }
    }
</script>

<?php
include_once 'DAL.php';
include_once 'DiscussionAgent.php';
include_once 'ChatAgent.php';
include_once 'GradeAgent.php';

mb_internal_encoding("UTF-8");

$id = required_param('id', PARAM_INT);           // Course ID
// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('Course ID is incorrect');
}

require_login($course);
$PAGE->set_url('/activity_report.php', array('id' => $cm->id));
$PAGE->set_pagelayout('standard');
$PAGE->set_title($COURSE->fullname);
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add(get_string('course_act_rep', 'block_agents_for_teacher'), new moodle_url('../agents/activity_report.php?id=' . $_GET['id']));

$question = $_POST['question'];
$answer = $_POST['answer'];
$crs = $id;
$userid = $USER->id;

$DAL = new DAL();
$roleid = $DAL->UserRoleSelect($id, $userid);


if (($roleid == 5) || ($roleid == 6) || ($roleid == 7) || ($roleid == 8)) {

    echo '<script>window.location = "../course/view.php?id=' . $id . '"; </script>';
} else {

    $discussions = new DiscussionAgent();
    $chat = new ChatAgent();
    $student_grades = new GradeAgent();

    echo $OUTPUT->header();

    mb_internal_encoding("UTF-8");

    $posts = $discussions->FillUsers($_GET['id']);
    $count_chats = $chat->FillUsers($_GET['id']);
    $grades = $student_grades->FillUsers($_GET['id']);

    // print_r($posts);
    //  print_r($count_chats);
    //  print_r($grades);

    for ($i = 0; $i < count($posts); $i++) {



        echo '<p><a style="cursor: pointer;" onClick="SwitchContent(' . $i . ');"><img id="studentSwitch' . $i . '" src="../pix/t/switch_plus.png"></a><strong>' . $posts[$i]->first_name . ' ' . $posts[$i]->last_name . '</strong></p>';
        echo '<div id=content' . $i . ' style="display:none">';
        echo '<table><tbody>';
        echo '<tr ><td colspan="2"><strong><i>Pasisakymų skaičius diskusijų forumuose</strong></i></td></tr>';
        foreach ($posts[$i]->posts as $post) {
            echo '<tr style="border:solid 1px black;"><td style="border:solid 1px black;">' . $post->forum_name . '</td><td style="border:solid 1px black;">' . $post->total_post_count . '</td></tr>';
            foreach ($post->discussions as $discussion) {
                echo '<tr style="font-style:italic; border:solid 1px black;"><td style="padding-left: 20px; border:solid 1px black;">' . $discussion->discussion_name . '</td><td style="border:solid 1px black;">' . $discussion->post_count . '</td></tr>';
            }
        }

        echo '<tr><td style="border:solid 1px black; text-align: right;"><strong>Viso pasisakymų diskusijose:</strong></td><td style="border:solid 1px black;"><strong>' . $posts[$i]->total_post_count . '</strong></td></tr>';
        echo '<tr><td></td><td></td></tr>';
        echo '<tr><td colspan="2"><strong><i>Pasisakymų skaičius pokalbių svetainėse</i></strong></td></tr>';
        foreach ($count_chats[$i]->chats as $chat1) {
            echo '<tr style="border:solid 1px black;"><td style="border:solid 1px black; text-align: left;">' . $chat1->chat_name . '</td><td>' . $chat1->msg_count . '</td></tr>';
        }
        echo '<tr style="border:solid 1px black;"><strong><td style="border:solid 1px black; text-align: right;"><strong>Viso pasisakymų pokalbių svetainėse:</strong></td><td><strong>' . $count_chats[$i]->total_msg_count . '</strong></td></tr>';
        echo '<tr><td></td><td></td></tr>';

        $question_count = $DAL->QuestionsAsked($id, $posts[$i]->id);

        if ($question_count != NULL) {
            echo '<tr><td><strong><i>Dėstytojui užduotų klausimų skaičius:</i></strong></td><td><strong>' . $question_count . '</strong></td></tr>';
        } else {
            echo '<tr><td><strong><i>Dėstytojui užduotų klausimų skaičius:</i></strong></td><td><strong>0</strong></td></tr>';
        }

        echo '<tr><td></td><td></td></tr>';
        echo '<tr><td colspan="2"><strong><i>Studento įvertinimai</i></strong></tr>';
        foreach ($grades[$i]->modules as $grade) {
            echo '<tr style="border:solid 1px black;"><td style="border:solid 1px black;">' . $grade->item_name . '</td><td style="border:solid 1px black;">';
            if ($grade->grade != '') {
                echo $grade->grade;
            } else {
                echo '-';
            }'</td></tr>';
        }
        echo '<tr><td style="border:solid 1px black; text-align: right;"><strong>Kurso įvertinimas</strong></td><td style="border:solid 1px black;">' . $grades[$i]->course_grade . '</td></tr>';
        echo '</tbody></table>';
        echo '</div>';
    }


    echo $OUTPUT->footer();
}
?>
