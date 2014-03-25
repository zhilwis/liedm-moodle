<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Vartotojas
 * Date: 6/10/13
 * Time: 2:20 PM
 * To change this template use File | Settings | File Templates.
 */

require_once("$CFG->libdir/formslib.php");


class select_form extends moodleform{

    function definition() {
        $mform =& $this->_form;

        $mform->addElement('date_selector', 'timestart', get_string('from', 'report_ataskaita3'));
        $mform->addElement('date_selector', 'timefinish', get_string('to', 'report_ataskaita3'));

        $students = $this->_customdata['students'];
        $mform->addElement('select', 'students_select', get_string('students', 'report_ataskaita3'), $students);
        $mform->getElement('students_select')->setMultiple(true);
        $mform->getElement('students_select')->setSelected(array_keys($students));

        $modules = $this->_customdata['modules'];
        $mform->addElement('select', 'selected_modules', get_string('modules', 'report_ataskaita3'), $modules);
        //$mform->getElement('selected_modules')->setMultiple(true);
        $this->add_action_buttons($cancel=false, $submitlabel="Vykdyti");
    }
}
