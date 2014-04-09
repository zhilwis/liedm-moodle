<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Reports implementation
 *
 * @package    report
 * @subpackage activity
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die ;

require_once (dirname(__FILE__) . '/lib.php');
require_once ($CFG -> dirroot . '/lib/statslib.php');

define('STATS_REPORT_USER_ACTIVITY_BY_ROLES',35); // double impose student writes and teacher writes on a line graph.
define('STATS_REPORT_OBJECT_ACTIVITY',34); // 2+3 added up, teacher vs student.
			


function report_activity_mode_menu($course, $mode, $time, $url) {
	global $CFG, $OUTPUT;
	/*
	 $reportoptions = stats_get_report_options($course->id, $mode);
	 $timeoptions = report_activity_timeoptions($mode);
	 if (empty($timeoptions)) {
	 print_error('nostatstodisplay', '', $CFG->wwwroot.'/course/view.php?id='.$course->id);
	 }
	 */

	$options = array();
	$options[STATS_MODE_GENERAL] = get_string('statsmodegeneral');
	$options[STATS_MODE_DETAILED] = get_string('statsmodedetailed');
	if (has_capability('report/activity:view', context_system::instance())) {
		$options[STATS_MODE_RANKED] = get_string('reports');
	}
	$popupurl = $url . "?course=$course->id&time=$time";
	$select = new single_select(new moodle_url($popupurl), 'mode', $options, $mode, null);
	$select -> set_label(get_string('reports'), array('class' => 'accesshide'));
	$select -> formid = 'switchmode';
	return $OUTPUT -> render($select);
}

function report_activity_timeoptions($mode) {
	global $CFG, $DB;

	if ($mode == STATS_MODE_DETAILED) {
		$earliestday = $DB -> get_field_sql('SELECT MIN(timeend) FROM {stats_user_daily}');
		$earliestweek = $DB -> get_field_sql('SELECT MIN(timeend) FROM {stats_user_weekly}');
		$earliestmonth = $DB -> get_field_sql('SELECT MIN(timeend) FROM {stats_user_monthly}');
	} else {
		$earliestday = $DB -> get_field_sql('SELECT MIN(timeend) FROM {stats_daily}');
		$earliestweek = $DB -> get_field_sql('SELECT MIN(timeend) FROM {stats_weekly}');
		$earliestmonth = $DB -> get_field_sql('SELECT MIN(timeend) FROM {stats_monthly}');
	}

	if (empty($earliestday))
		$earliestday = time();
	if (empty($earliestweek))
		$earliestweek = time();
	if (empty($earliestmonth))
		$earliestmonth = time();

	$now = stats_get_base_daily();
	$lastweekend = stats_get_base_weekly();
	$lastmonthend = stats_get_base_monthly();

	return activity_get_time_options($now, $lastweekend, $lastmonthend, $earliestday, $earliestweek, $earliestmonth);
}

function report_activity_report($course, $report, $mode, $user, $roleid, $time, $groupname) {
	global $CFG, $DB, $OUTPUT;

	if ($user) {
		$userid = $user -> id;
	} else {
		$userid = 0;
	}

	
	//$roleoptions = array();

	$roleoptions = 
			
			
			array(
					'3' => 'Destytojas',
					'5' => 'Studentas',
					'6' => 'Destytojas ir Studentas'
				);
	
	$sqlgroups = "	
					SELECT DISTINCT mdl_user.city
					FROM mdl_user
					INNER JOIN mdl_role_assignments ON mdl_user.id = mdl_role_assignments.userid
					WHERE mdl_user.city IS NOT NULL AND
					mdl_user.city NOT LIKE '' AND
					mdl_role_assignments.roleid = '5' ORDER BY
					mdl_user.city ASC 
				";
					
				 
	$groups = $DB -> get_records_sql($sqlgroups);			 
 	//var_dump($groups);
	$groupoptions = array();
	foreach ($groups as $key => $g) {
		
			//$groupoptions[$g -> city] = format_string($g -> city, true, '');
			$groupoptions[$g->city] = format_string($g -> city, true, '');
		
	}
	
	$objektasoptions = array(
		'Forumas'	
	);
	foreach ($objektasoptions as $key => $obj) {
		
			$objektas[$key] = format_string($obj, true, '');
		
	}

	
	
	$courses = get_courses('all', 'c.shortname', 'c.id,c.shortname,c.fullname');
	$courseoptions = array();
	
	foreach ($courses as $c) {
		$context = context_course::instance($c -> id);

		if (has_capability('report/activity:view', $context)) {
			$courseoptions[$c -> id] = format_string($c -> shortname, true, array('context' => $context));
		}
	}
	
	$reportoptions = activity_get_report_options($mode);
	// $reportoptions = array(
		// 'Naudotojų aktyvumas',
		// 'Objektų aktyvumas'
	// );

	$timeoptions = report_activity_timeoptions($mode);
	if (empty($timeoptions)) {
		print_error('nostatstodisplay', '', $CFG -> wwwroot . '/course/view.php?id=' . $course -> id);
	}
	
	$users = array();
	$table = new html_table();
	$table -> width = 'auto';
	$table -> align = array('left', 'left', 'left', 'left');
	
	$table -> data[] = array(html_writer::label(get_string('course'), 'menucourse'), 
							html_writer::select($courseoptions, 'course', $course->id, $nothing = array(''=>'choosedots')), 
							html_writer::label(get_string('statsreporttype'), 'menureport'), 
							html_writer::select($reportoptions, 'report', $report, $nothing = array(''=>'choosedots')));		
	$table -> data[] = array(html_writer::label('Grupes', 'menugroups'), 
							html_writer::select($groupoptions, 'group', $groupname, $nothing = array(''=>'choosedots')), 
							html_writer::label('Objektas', 'menuobjektas'), 
							html_writer::select($objektas, 'objektas', '', $nothing = array(''=>'choosedots')));
	$table -> data[] = array(html_writer::label('Roles', 'menurole'), 
							html_writer::select($roleoptions, 'roleid', $roleid, $nothing = array(''=>'choosedots')),
							html_writer::label(get_string('statstimeperiod'), 'menutime'), 
							html_writer::select($timeoptions, 'time', $time, $nothing = array(''=>'choosedots'))); 
	$table -> data[] = array('<input type="submit" value="'.get_string('view').'" />' );			
												
						

	echo '<form action="index.php" method="post">' . "\n" . '<div>' . "\n" . '<input type="hidden" name="mode" value="' . $mode . '" />' . "\n";

	echo html_writer::table($table);

	echo '</div>';
	echo '</form>';
	
	
	
	//echo 'after $course->id => ' . var_dump($course -> shortname);
	if (!empty($report) && !empty($time)) {
		if ($report == STATS_REPORT_LOGINS && $course -> id != SITEID) {
			print_error('reportnotavailable');
			
		}

		$param = activity_get_parameters($time, $report, $course -> id, $mode, '', $groupname);

		//echo $param->table."</br>";
		
		if (!empty($param -> sql)) {
			$sql = $param -> sql;
		} else {
			if (!empty($groupname)) {
			    $param->table = 'user_'.$param->table;
			}
			 if(!empty($groupname)){
// 				
				$sql = 'SELECT '.((empty($param->fieldscomplete)) ? ' CONCAT (timeend, roleid) AS uniqueid, timeend, roleid,' : '').$param->fields
				.' FROM {stats_'.$param->table .'} '
				.((!empty($groupname)) ? ' 
					Inner Join mdl_user ON mdl_stats_user_monthly.userid = mdl_user.id ': '')
				.' WHERE '
				.(($course->id == SITEID) ? '' : ' courseid = '.$course->id.' AND ')
				.((!empty($userid)) ? ' userid = '.$userid.' AND ' : '')
				//.((!empty($roleid)) ? ' roleid = '.$roleid.' AND ' : '')
				.((!empty($param->stattype)) ? ' stattype = \''.$param->stattype.'\' AND ' : '')
				.' timeend >= '.$param->timeafter
				.((!empty($groupname)) ? ' AND   mdl_user.city = \''.$groupname .'\' ': '')
				.' '.$param->extras
				.' ORDER BY timeend DESC';
				
							
			}else{
				// //TODO: lceanup this ugly mess
				$sql = 'SELECT ' . ((empty($param -> fieldscomplete)) ? 'id,roleid,timeend,' : '') 
				. $param -> fields 
				. ' FROM {stats_' . $param -> table . '} WHERE ' 
				. (($course -> id == SITEID) ? '' : ' courseid = ' . $course -> id . ' AND ') 
				. ((!empty($userid)) ? ' userid = ' . $userid . ' AND ' : '') 
				. ((!empty($roleid)) ? ' roleid = ' . $roleid . ' AND ' : '')
				. ((!empty($param -> stattype)) ? ' stattype = \'' . $param -> stattype . '\' AND ' : '') 
				. ' timeend >= ' . $param -> timeafter . ' ' 
				. $param -> extras . ' ORDER BY timeend DESC';
			}
			
			
			
			
		    // echo $sql;
		}

		$stats = $DB -> get_records_sql($sql);

		if (empty($stats)) {
			echo $OUTPUT -> notification(get_string('statsnodata'));

		} else {

			$stats = stats_fix_zeros($stats, $param -> timeafter, $param -> table, (!empty($param -> line2)));
			
			//echo $OUTPUT -> heading(format_string($course -> shortname) . ' - ' . get_string('statsreport' . $report) . ((!empty($user)) ? ' ' . get_string('statsreportforuser') . ' ' . fullname($user, true) : '') . ((!empty($roleid)) ? ' ' . $DB -> get_field('role', 'name', array('id' => $roleid)) : ''));
			if(!empty($groupname)){
				
				echo $OUTPUT -> heading(format_string($course -> shortname) . ' - '  
			
			
			. ((!empty($groupname)) ? ' ' . $groupname	 .' ': ''));
				
				
			}else{
				echo $OUTPUT -> heading(format_string($course -> shortname) . ' - '  
			
				
				. ((!empty($user)) ? ' ' . get_string('statsreportforuser') . ' ' . fullname($user, true). ',': '') 
				. ((!empty($roleid)) ? ' ' . $DB -> get_field('role', 'name', array('id' => $roleid)) : '')
				);
				
			
			}
			
			// echo $course -> id."</br>";
			// echo $report."</br>";
			// echo $time."</br>";
			// echo $roleid."</br>";
			// echo $mode."</br>";
			// echo 'group - '.$groupname."</br>";

			// echo '<div class="graph"><img src="' . $CFG -> wwwroot . '/report/activity/graph.php?mode=' . $mode 
			// . '&amp;course=' . $course -> id 
			// . '&amp;time=' . $time 
			// . '&amp;report=' . $report 
			// . '&amp;roleid=' . $roleid 
			// . '" alt="' . get_string('statisticsgraph') . '" /></div>';
			
		if(!empty($groupname)){
				echo '<div class="graph"><img src="' 
			. $CFG -> wwwroot . '/report/activity/graph.php?mode=1' 
			. '&amp;course=' . $course -> id 
			. '&amp;time=' . $time
			. '&amp;report=' . $report 
			. '&amp;roleid=' . $roleid 
			. '&amp;group='. $groupname. '" /></div>';
		}else{
			echo '<div class="graph"><img src="' 
			. $CFG -> wwwroot . '/report/activity/graph.php?mode=1' 
			. '&amp;course=' . $course -> id 
			. '&amp;time=' . $time
			. '&amp;report=' . $report 
			. '&amp;roleid=' . $roleid .'" /></div>';
			
		}
		
		//	echo '<div class="graph"><img src="' . $CFG -> wwwroot . '/report/activity/graph.php?mode=1' . '&amp;course=' . $course -> id . '&amp;time=' . $time. '&amp;report=35&amp;roleid=' . $roleid .'" /></div>';
		
			
			
			$table = new html_table();
			$table -> align = array('left', 'center', 'center', 'center');
			
			if(empty($groupname)) $param -> table = str_replace('user_', '', $param -> table);
			
			if(!empty($groupname)){
					
				switch ($param->table) {
				case 'user_daily' :
					$period = get_string('day');
					break;
				case 'user_weekly' :
					$period = get_string('week');
					break;
				case 'user_monthly' :
					$period = get_string('month', 'form');
					break;
				default :
					$period = '';
				
				}
			}else{
				switch ($param->table) {
					case 'daily' :
						$period = get_string('day');
						break;
					case 'weekly' :
						$period = get_string('week');
						break;
					case 'monthly' :
						$period = get_string('month', 'form');
						break;
					default :
						$period = '';
				}
			}

			$table -> head = array(get_string('periodending', 'moodle', $period));
			if (empty($param -> crosstab)) {
				$table -> head[] = $param -> line1;
				if (!empty($param -> line2)) {
					$table -> head[] = $param -> line2;
				}
			}
			if (!file_exists($CFG -> dirroot . '/report/log/index.php')) {
				// bad luck, we can not link other report
			} else if (empty($param -> crosstab)) {
				foreach ($stats as $stat) {
					$a = array(userdate($stat -> timeend - (60 * 60 * 24), get_string('strftimedate'), $CFG -> timezone), $stat -> line1);
					if (isset($stat -> line2)) {
						$a[] = $stat -> line2;
					}
					if (empty($CFG -> loglifetime) || ($stat -> timeend - (60 * 60 * 24)) >= (time() - 60 * 60 * 24 * $CFG -> loglifetime)) {
						if (has_capability('report/log:view', context_course::instance($course -> id))) {
							$a[] = '<a href="' . $CFG -> wwwroot . '/report/log/index.php?id=' . $course -> id . '&amp;chooselog=1&amp;showusers=1&amp;showcourses=1&amp;user=' . $userid . '&amp;date=' . usergetmidnight($stat -> timeend - (60 * 60 * 24)) . '">' . get_string('course') . ' ' . get_string('logs') . '</a>&nbsp;';
						} else {
							$a[] = '';
						}
					}
					$table -> data[] = $a;
				}
			} else {
				$data = array();
				$roles = array();
				$times = array();
				$missedlines = array();
				$coursecontext = context_course::instance($course -> id);
				$rolenames = role_fix_names(get_all_roles($coursecontext), $coursecontext, ROLENAME_ALIAS, true);
				foreach ($stats as $stat) {
					if (!empty($stat -> zerofixed)) {
						$missedlines[] = $stat -> timeend;
					}
					$data[$stat -> timeend][$stat -> roleid] = $stat -> line1;
					if ($stat -> roleid != 0) {
						if (!array_key_exists($stat -> roleid, $roles)) {
							$roles[$stat -> roleid] = $rolenames[$stat -> roleid];
						}
					} else {
						if (!array_key_exists($stat -> roleid, $roles)) {
							$roles[$stat -> roleid] = get_string('all');
						}
					}
					if (!array_key_exists($stat -> timeend, $times)) {
						$times[$stat -> timeend] = userdate($stat -> timeend, get_string('strftimedate'), $CFG -> timezone);
					}
				}

				foreach ($data as $time => $rolesdata) {
					if (in_array($time, $missedlines)) {
						$rolesdata = array();
						foreach ($roles as $roleid => $guff) {
							$rolesdata[$roleid] = 0;
						}
					} else {
						foreach (array_keys($roles) as $r) {
							if (!array_key_exists($r, $rolesdata)) {
								$rolesdata[$r] = 0;
							}
						}
					}
					krsort($rolesdata);
					$row = array_merge(array($times[$time]), $rolesdata);
					if (empty($CFG -> loglifetime) || ($stat -> timeend - (60 * 60 * 24)) >= (time() - 60 * 60 * 24 * $CFG -> loglifetime)) {
						if (has_capability('report/log:view', context_course::instance($course -> id))) {
							$row[] = '<a href="' . $CFG -> wwwroot . '/report/log/index.php?id=' . $course -> id . '&amp;chooselog=1&amp;showusers=1&amp;showcourses=1&amp;user=' . $userid . '&amp;date=' . usergetmidnight($time - (60 * 60 * 24)) . '">' . get_string('course') . ' ' . get_string('logs') . '</a>&nbsp;';
						} else {
							$row[] = '';
						}
					}
					$table -> data[] = $row;
				}
				krsort($roles);
				$table -> head = array_merge($table -> head, $roles);
			}
			$table -> head[] = get_string('logs');
			if (!empty($lastrecord)) {
			$lastrecord[] = $lastlink;
			$table->data[] = $lastrecord;
			}
			echo html_writer::table($table);
		}
	}
}

function activity_get_report_options($mode) {
	global $CFG, $DB;
	define('AJAX_SCRIPT', true);
	$reportoptions = array();

	switch ($mode) {
		case STATS_MODE_GENERAL :
			
			$reportoptions[STATS_REPORT_USER_ACTIVITY_BY_ROLES] = 'User activity';
			$reportoptions[STATS_REPORT_OBJECT_ACTIVITY] = 'Object activity';			
			break;
		
	}

	return $reportoptions;
}
function activity_get_time_options($now,$lastweekend,$lastmonthend,$earliestday,$earliestweek,$earliestmonth) {

    $now = stats_get_base_daily(time());
    $now += 60*60*24;

    $timeoptions = array();

    
    if ($lastweekend - (60*60*24*56) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST2MONTHS] = get_string('nummonths','moodle',2);
    }
    if ($lastweekend - (60*60*24*84) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST3MONTHS] = get_string('nummonths','moodle',3);
    }
    if ($lastweekend - (60*60*24*112) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST4MONTHS] = get_string('nummonths','moodle',4);
    }
    if ($lastweekend - (60*60*24*140) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST5MONTHS] = get_string('nummonths','moodle',5);
    }
    if ($lastweekend - (60*60*24*168) >= $earliestweek) {
        $timeoptions[STATS_TIME_LAST6MONTHS] = get_string('nummonths','moodle',6); // show weeklies up to (including) here
    }
    if (strtotime('-7 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST7MONTHS] = get_string('nummonths','moodle',7);
    }
    if (strtotime('-8 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST8MONTHS] = get_string('nummonths','moodle',8);
    }
    if (strtotime('-9 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST9MONTHS] = get_string('nummonths','moodle',9);
    }
    if (strtotime('-10 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST10MONTHS] = get_string('nummonths','moodle',10);
    }
    if (strtotime('-11 months',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LAST11MONTHS] = get_string('nummonths','moodle',11);
    }
    if (strtotime('-1 year',$lastmonthend) >= $earliestmonth) {
        $timeoptions[STATS_TIME_LASTYEAR] = get_string('lastyear');
    }

    $years = (int)date('y', $now) - (int)date('y', $earliestmonth);
    if ($years > 1) {
        for($i = 2; $i <= $years; $i++) {
            $timeoptions[$i*12+20] = get_string('numyears', 'moodle', $i);
        }
    }

    return $timeoptions;
}
function activity_get_parameters($time, $report, $courseid, $mode, $roleid=0, $groupname) {
    global $CFG, $DB;

    $param = new stdClass();
    $param->params = array();

    if ($time < 10) { // dailies
        // number of days to go back = 7* time
        //if(!empty($groupname)){
        	
		//	 $param->table = 'user_daily';
       // }else{
        	 $param->table = 'daily';
        //}
       
        $param->timeafter = strtotime("-".($time*7)." days",stats_get_base_daily());
    } elseif ($time < 20) { // weeklies
        // number of weeks to go back = time - 10 * 4 (weeks) + base week
	    // if(!empty($groupname)){
// 	    	
	    	// $param->table = 'user_weekly';
		 // }else{
		 	$param->table = 'weekly';
		 // }
        $param->timeafter = strtotime("-".(($time - 10)*4)." weeks",stats_get_base_weekly());
    } else { // monthlies.
        // number of months to go back = time - 20 * months + base month
         // if(!empty($groupname)){
//         	
        // $param->table = 'user_monthly';
        // }else{
        	$param->table = 'monthly';
        // }
		
        $param->timeafter = strtotime("-".($time - 20)." months",stats_get_base_monthly());
    }

    $param->extras = '';

    switch ($report) {
    // ******************** STATS_MODE_GENERAL ******************** //
    
		
		
    case STATS_REPORT_USER_ACTIVITY_BY_ROLES:
        // $param->fields = 'stat1 AS line1, stat2 AS line2';
        // $param->stattype = 'activity';
        // $rolename = $DB->get_field('role','name', array('id'=>$roleid));
        // $param->line1 = $rolename . get_string('statsreads');
        // $param->line2 = $rolename . get_string('statswrites');
        // if ($courseid == SITEID) {
         // $param->extras = 'GROUP BY timeend';
        // }
        
        if(!empty($groupname)){
			$param->fields = 'Sum(statsreads+statswrites) as line1';
	        $param->line1 = get_string('statsuseractivity');
	        $param->stattype = 'activity';
			
        }else{
    	 	$param->fields = 'stat1 AS line1, stat2 AS line2';
	        $param->stattype = 'activity';
	        $rolename = $DB->get_field('role','name', array('id'=>$roleid));
	        $param->line1 = $rolename . get_string('statsreads');
	        $param->line2 = $rolename . get_string('statswrites');
	        if ($courseid == SITEID) {
	            $param->extras = 'GROUP BY timeend';
	        }
        }
     	
	   
    	break;
	case STATS_REPORT_OBJECT_ACTIVITY:
	    // $param->fields = 'statsreads AS line1, statswrites AS line2';
	    // $param->stattype = 'activity';
	    // $rolename = $DB->get_field('role','name', array('id'=>$roleid));
	    // $param->line1 = $rolename . get_string('statsreads');
	    // $param->line2 = $rolename . get_string('statswrites');
	    // if ($courseid == SITEID) {
	        // $param->extras = 'GROUP BY timeend';
	    // }
   		 // 
   		 if(!empty($groupname)){
			$param->fields = 'Sum(statsreads+statswrites) as line1';
	        $param->line1 = get_string('statsuseractivity');
	        $param->stattype = 'activity';
			
        }else{
    	 	$param->fields = 'stat1 AS line1, stat2 AS line2';
	        $param->stattype = 'activity';
	        $rolename = $DB->get_field('role','name', array('id'=>$roleid));
	        $param->line1 = $rolename . get_string('statsreads');
	        $param->line2 = $rolename . get_string('statswrites');
	        if ($courseid == SITEID) {
	            $param->extras = 'GROUP BY timeend';
	        }
        }
        
        break;
        }
    //TODO must add the SITEID reports to the rest of the reports.
    return $param;
}
