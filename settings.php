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
 * This is where the plugin does its plugging in stuff.
 *
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
// An upgrade has occurred so we must rescan for setting definitions.
if (get_config('local_advancedconfig', 'lasthash') !== $CFG->allversionshash) {
    cache_helper::purge_by_definition('local_advancedconfig', 'childclassmap');
    \local_advancedconfig\helper\register_setting::update_db();
    set_config('lasthash', $CFG->allversionshash, 'local_advancedconfig');
}
if ($ADMIN) {
    if ($ADMIN->locate('localplugins')) {
        $temp = new admin_settingpage('local_advancedconfig', new lang_string('pluginname', 'local_advancedconfig'));

        $temp->add(new admin_setting_configtext(
            'local_advancedconfig/lasthash',
            new lang_string('lasthash', 'local_advancedconfig'),
            '',
            ''));

        $ADMIN->add('localplugins', $temp);
    }

    \local_advancedconfig\helper\admin_tree::append_to_admin_tree($PAGE->context, $ADMIN, $ADMIN->fulltree);
}