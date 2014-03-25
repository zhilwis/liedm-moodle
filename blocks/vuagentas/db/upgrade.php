<?php

defined('MOODLE_INTERNAL') || die();


/**
 * Block vuagentas upgrade function.
 * @param string $oldversion the version we are upgrading from.
 */
function xmldb_block_vuagentas_upgrade($oldversion) {
    global $CFG, $DB;
    
        $dbman = $DB->get_manager();
        
        
        if ($oldversion < 2013011301) {

        // Define table block_vuagentas to be created.
        $table = new xmldb_table('block_vuagentas');

        // Adding fields to table block_vuagentas.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sections', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('resources', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('total', XMLDB_TYPE_INTEGER, '3', null, null, null, null);

        // Adding keys to table block_vuagentas.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('course', XMLDB_KEY_UNIQUE, array('course'));

        // Conditionally launch create table for block_vuagentas.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Vuagentas savepoint reached.
        upgrade_block_savepoint(true, 2013011301, 'vuagentas');
    }
    
     if ($oldversion < 2013011302) {

        // Rename field modules on table block_vuagentas to NEWNAMEGOESHERE.
        $table = new xmldb_table('block_vuagentas');
        $field = new xmldb_field('resources', XMLDB_TYPE_TEXT, null, null, null, null, null, 'sections');

        // Launch rename field modules.
        $dbman->rename_field($table, $field, 'modules');

        // Vuagentas savepoint reached.
        upgrade_block_savepoint(true, 2013011302, 'vuagentas');
    }
    
    if ($oldversion < 2013011305) {
        upgrade_block_savepoint(true, 2013011305, 'vuagentas');
    }
    
}