<?php
/**
 *
 * @package    block_vuagentas
 * @copyright  2014 onwards Tomas Vitkauskas
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/vuagentas/lib.php');


class block_vuagentas extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_vuagentas');
    }

    function get_content() {
        global $CFG, $OUTPUT, $course, $PAGE;
//require_once($CFG->dirroot.'/config.php');
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->items = array();
        $this->content->icons = array();
        
        $currentcontext = $this->page->context->get_course_context(false);

        if (! empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }
        
        if (empty($currentcontext)) {
            return $this->content;
        }
        if ($this->page->course->id == SITEID) {
            $this->context->text .= "site context";
        }
        
        $this->content->text .= vuagentas_get_course_data($course->id);
        $context = context_module::instance($course->id);
        $canmanage = has_capability('block/vuagentas:checkrating', $context);
        
        if($canmanage){
            $options = array('sesskey'=>sesskey());
            $options['cid'] = $course->id;
            $address = new moodle_url('/blocks/vuagentas/info.php', $options);
            $this->content->text .= $OUTPUT->single_button($address, get_string('recount', 'block_vuagentas'));
        }
 
        return $this->content;
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => false,
                     'site-index' => false,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => false, 
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
          return true;
    }
    
    function has_config() {return true;}

    public function cron() {
        vuagentas_cron();
        return true;
    }
}