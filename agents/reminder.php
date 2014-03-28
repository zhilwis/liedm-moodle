<?php
  
  
  require_once('../config.php');
  include_once('DAL.php');
  
  $id = required_param('id', PARAM_INT);           // Course ID
 
	// Ensure that the course specified is valid
	if (!$course = $DB->get_record('course', array('id'=> $id))) {
    	print_error('Course ID is incorrect');
	}
	
require_login($course);
$PAGE->set_url('/question.php', array('id' => $cm->id)); 
$PAGE->set_pagelayout('standard');
$PAGE->set_title($COURSE->fullname);
$PAGE->set_heading($COURSE->fullname); 
$PAGE->navbar->add(get_string('settings', 'block_agents_for_student'), new moodle_url('../agents/reminder.php?id='.$_GET['id']));

	$host = $CFG->dbhost;	
	$user = $CFG->dbuser;
	$pass = $CFG->dbpass;
	$dbname = $CFG->dbname;
	$prefix = $CFG->prefix;
	$crs = $id;
	$userid = $USER->id;
	
        
        $DAL = new DAL();
        
        $roleid = $DAL->UserRoleSelect($id, $userid);


echo $OUTPUT->header();


if (isset($_POST['submit'])) {
    
        $first_event = $_POST['first_event'];
        $second_event = $_POST['second_event'];
          
        $new_event = "";
        
        if (isset($_POST['new_event'])) {
            $new_event = '1';
        } else {
            $new_event = '0';
        }
             

 if ((($first_event !="") && ($second_event =="")) || (($first_event =="") && ($second_event !=""))) {
     
     echo '<p align="center" style="color:red;">Abu artėjančių įvykių laukai turi būti užpildyti.</p>'; 
      
 } else if ($first_event < $second_event) {
     
     echo '<p align="center" style="color:red;">Priminimo kartojimo periodas negali būti didesnis už pirmo priminimo išsiutimo periodą.</p>'; 
 } else {
     
     $DAL->InsertUpcomingEventSettings($first_event, $second_event, $crs, $userid);     
 }
 
 if ($new_event != "") {
        
     $DAL->InsertNewEventSettings($new_event, $crs, $userid);
 }
 
  
}


$settings = $DAL->GetStudentSettings($userid, $crs);

//echo $settings->new_event_settings->new_event;



?>

<form name="forma1" method="post" action="" >

      <div align = "left">
          
          <table>
          <tr>
              <td colspan="3"><strong>Pranešimų apie naujus kurso įvykius nustatymas</strong></td> 
          </tr>
          <tr>
              <td><input name="new_event" type="checkbox"  <?php if($settings->new_event_settings->new_event == '1'){echo("checked");}?>/></td>
              <td>Noriu gauti pranešimus apie naujai paskelbtus kurso įvykius</td>
              <td></td>
          </tr>
          
          
      </table>
          
          <table>
              <tr>
                  <td colspan="3"><strong>Pranešimų apie artėjančius kurso įvykius nustatymas</strong></td>
              </tr>
              <tr>
                  <td>Apie artėjantį įvykį pirmą kartą pranešti prieš</td>
                  <td><select name="first_event">
		<option value="">Pasirinkite laikotarpį... </option>
		<option value="1" <?php if($settings->upcoming_event_settings->first_event == '1'){echo("selected");}?>>1</option>
		<option value="2" <?php if($settings->upcoming_event_settings->first_event == '2'){echo("selected");}?>>2</option>
		<option value="3" <?php if($settings->upcoming_event_settings->first_event == '3'){echo("selected");}?>>3</option>
		<option value="4" <?php if($settings->upcoming_event_settings->first_event == '4'){echo("selected");}?>>4</option>
		<option value="5" <?php if($settings->upcoming_event_settings->first_event == '5'){echo("selected");}?>>5</option>
		<option value="6" <?php if($settings->upcoming_event_settings->first_event == '6'){echo("selected");}?>>6</option>
		<option value="7" <?php if($settings->upcoming_event_settings->first_event == '7'){echo("selected");}?>>7</option>
		<option value="8" <?php if($settings->upcoming_event_settings->first_event == '8'){echo("selected");}?>>8</option>
		<option value="9" <?php if($settings->upcoming_event_settings->first_event == '9'){echo("selected");}?>>9</option>
		<option value="10" <?php if($settings->upcoming_event_settings->first_event == '10'){echo("selected");}?>>10</option>
		<option value="11" <?php if($settings->upcoming_event_settings->first_event == '11'){echo("selected");}?>>11</option>
		<option value="12" <?php if($settings->upcoming_event_settings->first_event == '12'){echo("selected");}?>>12</option>
		<option value="13" <?php if($settings->upcoming_event_settings->first_event == '13'){echo("selected");}?>>13</option>
		<option value="14" <?php if($settings->upcoming_event_settings->first_event == '14'){echo("selected");}?>>14</option>
		<option value="15" <?php if($settings->upcoming_event_settings->first_event == '15'){echo("selected");}?>>15</option>
		<option value="16" <?php if($settings->upcoming_event_settings->first_event == '16'){echo("selected");}?>>16</option>
		<option value="17" <?php if($settings->upcoming_event_settings->first_event == '17'){echo("selected");}?>>17</option>
		<option value="18" <?php if($settings->upcoming_event_settings->first_event == '18'){echo("selected");}?>>18</option>
		<option value="19" <?php if($settings->upcoming_event_settings->first_event == '19'){echo("selected");}?>>19</option>
		<option value="20" <?php if($settings->upcoming_event_settings->first_event == '20'){echo("selected");}?>>20</option>
		<option value="21" <?php if($settings->upcoming_event_settings->first_event == '21'){echo("selected");}?>>21</option>
		<option value="22" <?php if($settings->upcoming_event_settings->first_event == '22'){echo("selected");}?>>22</option>
		<option value="23" <?php if($settings->upcoming_event_settings->first_event == '23'){echo("selected");}?>>23</option>
		<option value="24" <?php if($settings->upcoming_event_settings->first_event == '24'){echo("selected");}?>>24</option>
		<option value="25" <?php if($settings->upcoming_event_settings->first_event == '25'){echo("selected");}?>>25</option>
		<option value="26" <?php if($settings->upcoming_event_settings->first_event == '26'){echo("selected");}?>>26</option>
		<option value="27" <?php if($settings->upcoming_event_settings->first_event == '27'){echo("selected");}?>>27</option>
		<option value="28" <?php if($settings->upcoming_event_settings->first_event == '28'){echo("selected");}?>>28</option>
		<option value="29" <?php if($settings->upcoming_event_settings->first_event == '29'){echo("selected");}?>>29</option>
		<option value="30" <?php if($settings->upcoming_event_settings->first_event == '30'){echo("selected");}?>>30</option></td>
                  <td>dienų</td>
              </tr>
              <tr>
                  <td>Priminimą kartoti kas</td>
                  <td><select name="second_event">
		<option value="">Pasirinkite periodą... </option>
		<option value="1" <?php if($settings->upcoming_event_settings->second_event == '1'){echo("selected");}?>>1</option>
		<option value="2" <?php if($settings->upcoming_event_settings->second_event == '2'){echo("selected");}?>>2</option>
		<option value="3" <?php if($settings->upcoming_event_settings->second_event == '3'){echo("selected");}?>>3</option>
		<option value="4" <?php if($settings->upcoming_event_settings->second_event == '4'){echo("selected");}?>>4</option>
		<option value="5" <?php if($settings->upcoming_event_settings->second_event == '5'){echo("selected");}?>>5</option>
		<option value="6" <?php if($settings->upcoming_event_settings->second_event == '6'){echo("selected");}?>>6</option>
		<option value="7" <?php if($settings->upcoming_event_settings->second_event == '7'){echo("selected");}?>>7</option>
                    </td>
                  <td>dienų</td>
              </tr>
          </table>
      
        <input name="submit" type="submit" value="Saugoti" />
          
      
      </div>

</form>

<?



echo $OUTPUT->footer();



?>