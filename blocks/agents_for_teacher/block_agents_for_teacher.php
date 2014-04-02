<?php



class block_agents_for_teacher extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_agents_for_teacher');
    }

    
	public function get_content() {
  if ($this->content !== null) {
    return $this->content;
  }
 
  $this->content         = new stdClass;
  $this->content->items  = array();
  $this->content->icons  = array();
 /* $this->content->footer = 'Footer here...'; */
 
  $this->content->items[] = html_writer::tag('a', 'Įtraukti klausimą į DUK', array('href' => '../agents/question.php?id='.$_GET['id']));
  $this->content->icons[] = html_writer::empty_tag('img', array('src' => '../agents/images/icons/add.gif', 'class' => 'icon'));

  $this->content->items[] = html_writer::tag('a', 'Aktyvumo ataskaita', array('href' => '../agents/activity_report.php?id='.$_GET['id']));
  $this->content->icons[] = html_writer::empty_tag('img', array('src' => '../agents/images/icons/report.gif', 'class' => 'icon'));

  $this->content->items[] = html_writer::tag('a', 'Priminimų nustatymai', array('href' => '../agents/agents_settings.php?id='.$_GET['id']));
  $this->content->icons[] = html_writer::empty_tag('img', array('src' => '../agents/images/icons/settings.gif', 'class' => 'icon'));

 
  // Add more list items here
 
  return $this->content;
	}
 
 
    function applicable_formats() {
        return array('all' => true, 'my' => false, 'tag' => false);
    } 
}

