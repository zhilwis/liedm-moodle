<?php



class block_agents_for_student extends block_list {
    function init() {
        $this->title = get_string('pluginname', 'block_agents_for_student');
    }

    
	public function get_content() {
  if ($this->content !== null) {
    return $this->content;
  }
 
  $this->content         = new stdClass;
  $this->content->items  = array();
  $this->content->icons  = array();
 /* $this->content->footer = 'Footer here...'; */
 
  $this->content->items[] = html_writer::tag('a', 'Klausti destytojo', array('href' => '../agents/ask_question.php?id='.$_GET['id']));
  $this->content->icons[] = html_writer::empty_tag('img', array('src' => '../agents/images/icons/question.gif', 'class' => 'icon'));

  $this->content->items[] = html_writer::tag('a', 'Priminimų nustatymai', array('href' => '../agents/reminder.php?id='.$_GET['id']));
  $this->content->icons[] = html_writer::empty_tag('img', array('src' => '../agents/images/icons/settings.gif', 'class' => 'icon'));

 
  // Add more list items here
 
  return $this->content;
	}
 
 
    function applicable_formats() {
        return array('all' => true, 'my' => false, 'tag' => false);
    } 
}

