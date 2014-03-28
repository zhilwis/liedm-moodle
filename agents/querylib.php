<?php 
function InsertQuestion ($question, $answer, $crs, $host, $user, $pass, $dbname, $prefix) {
 
 	$db = mysql_connect($host, $user, $pass) or die(mysql_error());
    mysql_select_db($dbname) or die(mysql_error());
	mysql_set_charset('utf8');
	
	
	

  if (($question != "") && ($answer != "")) {

   $query = 'INSERT INTO '.$prefix.'questions_agent
			(id, course_id, question, answer)
				  VALUES
				     (NULL,
					  "' . mysql_real_escape_string($crs, $db) . '", '.
					  '"' . mysql_real_escape_string($question, $db) . '", '.
					  '"' . mysql_real_escape_string($answer, $db) . '")';
		$result = mysql_query($query, $db) or die (mysql_error());
		$id = mysql_insert_id($db); 
		
		mysql_close($db);
		
		echo '<p align="center">The question has been successfully added to FAQ.</p>';

} else {
    echo '<p align="center" style="color: red;"><strong>Type question and answer.</strong></p>';
    }
}	



function UserRoleSelect ($host, $user, $pass, $dbname, $prefix, $id, $userid) {
	
	$db = mysqli_connect($host, $user, $pass, $dbname);


	$query1 = 'select a.roleid from '.$prefix.'role_assignments as a 
			  inner join '.$prefix.'context as c on a.contextid = c.id 
			  where c.instanceid = '.$id.' and c.contextlevel = 50 and a.userid ='.$userid;
	$result1 = mysqli_query($db, $query1);
	$row = mysqli_fetch_array($result1);
	
	mysqli_close($db);
	
	$roleid = $row['roleid'];
	
	return $roleid;	
	
	}

function InsertActivitySettings ($period, $crs, $host, $user, $pass, $dbname, $prefix, $userid){
	
	$db = mysql_connect($host, $user, $pass) or die(mysql_error());
    mysql_select_db($dbname) or die(mysql_error());
	mysql_set_charset('utf8');
	
	$current_time = date('Y-m-d H:i:s');
	
	if ($period != "") {

   $query = 'INSERT INTO '.$prefix.'activity_agent
			(id, user_id, course_id, period, last_call, start_time)
				  VALUES
				     (NULL,
					  "' . mysql_real_escape_string($userid, $db) . '", '.
					  '"' . mysql_real_escape_string($crs, $db) . '", '.
					  '"' . mysql_real_escape_string($period, $db) . '", 
					   NULL, '.
					  '"' . mysql_real_escape_string($current_time, $db) . '")';
		$result = mysql_query($query, $db) or die (mysql_error());
		$id = mysql_insert_id($db); 
		
		mysql_close($db);
		
		echo '<p align="center">Your settings have been saved.</p>';

} else {
    echo '<p align="center" style="color: red;"><strong>Select period.</strong></p>';
    }
	
	
}

	
?>