<?php

/**
 *
 * @package   block_vuagentas
 * @copyright 2014 Tomas Vitkauskas
 */

defined('MOODLE_INTERNAL') || die();

function vuagentas_get_course_sections($course){
    $modinfo = get_fast_modinfo($course);
    $sections = $modinfo->get_sections();
    return $sections;
}

function vuagentas_get_record($course, $force = false){
    GLOBAL $DB;
    
    $result = $DB->get_record("block_vuagentas", array( 'course' => $course));
    if(!$result || $force){
        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_sections();
        $modules = get_array_of_activities($course);
        $sectionss = serialize($sections);
        $moduless = serialize($modules);
        if($result && $force){
            //$DB->set_debug(true);
            $update = new stdClass();
            $update->id = $result->id;
            $update->sections = $sectionss;
            $update->modules = $moduless;
            $DB->update_record('block_vuagentas', $update);
            $id = $result->id;
        } else {
            $id = $DB->insert_record("block_vuagentas", array( 'course' => $course, 'sections' => $sectionss, 'modules' => $moduless, 'total' => null));
        }
        $mod = new stdClass();
        $mod->id = $id;
        $mod->course = $course;
        $mod->sections = $sections;
        $mod->modules = $modules;
        $mod->total = "";
        return $mod;
    } else {
        $result->sections = unserialize($result->sections);
        $result->modules = unserialize($result->modules);
        return $result;
    }
}

function vuagentas_get_rerating($course){
    GLOBAL $OUTPUT;
    $data = vuagentas_get_record($course, true);
    $data = vuagentas_check_rating($data);
    if($data->total == null){
        return get_string( 'ratingnull2', 'block_vuagentas') . $OUTPUT->help_icon("ratingnull2", 'block_vuagentas');
    } else {
        return $data;
    }
}

function vuagentas_get_course_data($course){
    GLOBAL $OUTPUT;
    $data = vuagentas_get_record($course);
    $data = vuagentas_check_rating($data);
    if($data->total == null){
        return get_string( 'ratingnull', 'block_vuagentas');
    } else {
        return "Įvertinimas: ". vuagentas_get_star($data->total);
    }
    
}

function vuagentas_check_rating($data){
    $sectionscount = count($data->sections)-1;
    $config = get_config('vuagentas');
    if($sectionscount >= $config->sekcijos){
        
        //$data->err = "";
        $ratinvalue = (
                       ($config->forum + $config->chat + $config->glossary + $config->sekcijos) 
                       + ($config->page * $sectionscount)
                       + ($config->link * $sectionscount)
                       + ($config->ppt * $sectionscount)
                       + ($config->testai * $sectionscount)
                       + ($config->video * $sectionscount)
                       );
        $ratingunit = 100/$ratinvalue;
        $rating = $ratingunit;
        $redata = vuagentas_check_forum_chat_glossary($data->modules, $ratingunit, $config->forum, $config->chat, $config->glossary);
        if(!empty($redata)){
        $rating += $redata->rating;
        $data->err[0] = $redata->err;
        }
        foreach($data->sections as $key => $value){
            if($key != 0){
                $reddata = vuagentas_check_page_link_ppt_video($value, $data->modules, $ratingunit, $config->page, $config->link, $config->ppt, $config->testai, $config->video, $key);
                $rating += $reddata->rating;
                if(!empty($reddata->err))
                $data->err[$key] = $reddata->err;
            }
        }
        $rrating = round($rating, 0);
        if($data->total != $rrating){
            vuagentas_update_rating($data->id, $rrating);
            $data->total = $rrating;
        }
    } 
    
    return $data;
}

function vuagentas_check_forum_chat_glossary($data, $ratingunit, $forum, $chat, $glossary){
    GLOBAL $OUTPUT;
    $fforum = 0; $cchat = 0; $gglossary = 0;
    $rating = new stdClass();
    $rating->rating = 0;
    
    foreach($data as $value){
        if($fforum != $forum){
            if($value->mod == 'forum'){ $rating->rating += $ratingunit; $fforum++;}
        }
        if($cchat != $chat){
            if($value->mod == 'chat'){ $rating->rating += $ratingunit; $cchat++;}
        }
        if($gglossary != $glossary){
            if($value->mod == 'glossary'){ $rating->rating += $ratingunit; $gglossary++;}
        }
    }
    if($fforum != $forum){
        $err[] = get_string('noforum', 'block_vuagentas'). $OUTPUT->help_icon("noforum", 'block_vuagentas') ."<br />";  
    }
    if($cchat != $chat){
        $err[] = get_string('nochat', 'block_vuagentas'). $OUTPUT->help_icon("nochat", 'block_vuagentas') . "<br />";
    }
    if($gglossary != $glossary){
        $err[] = get_string('noglossary', 'block_vuagentas'). $OUTPUT->help_icon("noglossary", 'block_vuagentas') . "<br />";
    }
    if(!empty($err)){
        $rating->err = $err;
    }
    //echo $dataer;
    return $rating;
}

function vuagentas_check_page_link_ppt_video($data, $check, $ratingunit, $page, $link, $ppt, $test, $video, $section){
    GLOBAL $DB, $OUTPUT;
    $rating = 0; $ppage = 0; $llink = 0; $pppt = 0; $ttest = 0; $vvideo = 0;
    $rating = new stdClass();
    foreach ($data as $value) {
        if($ppage != $page){ 
            if($check[$value]->mod == 'page'){ $rating->rating += $ratingunit; $ppage++; }
        }
        if($ttest != $test){ 
            if($check[$value]->mod == 'quiz'){ $rating->rating += $ratingunit; $ttest++; }
        }
        if($pppt != $ppt){ 
            if($check[$value]->mod == 'resource' && $check[$value]->icon == 'f/powerpoint-24'){ $rating->rating += $ratingunit; $pppt++; }
        }
        if($vvideo != $video && $check[$value]->mod == 'url'){
            $url = $DB->get_record('url', array('id'=>$check[$value]->id));
            if(preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url->externalurl)){
                $rating->rating += $ratingunit; $vvideo++;
            }
        }
        if($llink != $link && $check[$value]->mod == 'url'){
            $url = $DB->get_record('url', array('id'=>$check[$value]->id));
            if(!preg_match("#http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]#", $url->externalurl) && !preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url->externalurl)){
                $rating->rating += $ratingunit; $llink++;
            }
        }
    }
        if($ppage != $page){ 
            
            $err[] = get_string('nopage', 'block_vuagentas') .  $OUTPUT->help_icon("nopage", 'block_vuagentas') . "<br>";
        }
        if($ttest != $test){ 
            
            $err[] = get_string('noquiz', 'block_vuagentas') .  $OUTPUT->help_icon("noquiz", 'block_vuagentas') . "<br>";
        }
        if($pppt != $ppt){ 
            $err[] = get_string('noppt', 'block_vuagentas') . $OUTPUT->help_icon("noppt", 'block_vuagentas') . "<br />";
        }
        if($vvideo != $video){
            $err[] = get_string('novideo', 'block_vuagentas') . $OUTPUT->help_icon("novideo", 'block_vuagentas') . "<br />";
        }
        if($llink != $link){
            $err[] = get_string('noexternallink', 'block_vuagentas') . $OUTPUT->help_icon("noexternallink", 'block_vuagentas') . "<br />";
        }
        
        if(!empty($err)){
            $rating->err = $err;
        }
    
    return $rating;
}

function vuagentas_update_rating($id, $rating){
    GLOBAL $DB;
    $data = new stdClass();
    $data->id = $id;
    $data->total = $rating;
    $DB->update_record('block_vuagentas', $data);
}

function vuagentas_cron(){
    $courses = get_courses();
    foreach ($courses as $key => $value) {
       $data = vuagentas_get_record($key, true);
       vuagentas_check_rating($data);
    }
}

function vuagentas_get_star($rating){
    GLOBAL $CFG;
    $avg = $rating / 10;
    $avg = round($avg);
    $res = '<img src="'.
            $CFG->wwwroot.'/blocks/vuagentas/graphic/star.php?rating='.
        $avg.'" alt="'.$avg.'"/><br/>';
    return $res;
}