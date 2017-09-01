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
 * Database maintenance functions.
 *
 * @package local_advancedconfig\helper
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_advancedconfig\helper;

defined('MOODLE_INTERNAL') || die();

use local_advancedconfig\model\setting_definition;
use local_advancedconfig\scanner;

class register_setting {
    /**
     * Scans for setting definitions and updates the database.
     *
     * Removes fields when definitions are gone, adds new ones
     * where necessary.
     */
    public static function update_db() {
        global $DB;
        $settings = scanner::scan_settings();
        $sql = 'SELECT lac_n.id, lac_c.component, lac_n.name
                  FROM {local_advconf_name} lac_n
                  JOIN {local_advconf_component} lac_c ON lac_n.component = lac_c.id';
        $records = $DB->get_records_sql($sql);
        $stuff = [];
        foreach ($records as $record) {
            $stuff[$record->component . '/' . $record->name] = $record->id;
        }
        /** @var setting_definition[] $create */
        $create = array_diff_key($settings, $stuff);
        $delete = array_diff_key($stuff, $settings);

        foreach ($create as $setting) {
            $componentid = $DB->get_field('local_advconf_component', 'id', ['component' => $setting->get_component()]);
            if (!$componentid) {
                $componentid = $DB->insert_record('local_advconf_component', (object)['component' => $setting->get_component()]);
            }
            $DB->insert_record('local_advconf_name', (object)[
                'component' => $componentid,
                'name' => $setting->get_name(),
            ]);
        }
        $DB->delete_records_list('local_advconf_name', 'id', $delete);
    }
}