<?php
function xmldb_local_advancedconfig_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2017101100) {

        // Changing type of field config on table local_advconf_config to text.
        $table = new xmldb_table('local_advconf_config');
        $field = new xmldb_field('config', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'context');

        // Launch change of type for field config.
        $dbman->change_field_type($table, $field);

        // Advancedconfig savepoint reached.
        upgrade_plugin_savepoint(true, 2017101100, 'local', 'advancedconfig');
    }

    return true;
}