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
namespace local_advancedconfig\dao;

defined('MOODLE_INTERNAL') || die();

use local_advancedconfig\model\setting_definition;

class setting {
    public static function write(setting_definition $setting, \context $context, $value) {
        global $DB;
        $setting->post_storage_action();

        $sql = 'SELECT lac_n.id AS name_id, lac_conf.id AS config_id
                  FROM {local_advconf_component} lac_c
                  JOIN {local_advconf_name} lac_n ON lac_c.id = lac_n.component
             LEFT JOIN {local_advconf_config} lac_conf ON lac_conf.name = lac_n.id AND lac_conf.context = :context
                 WHERE lac_c.component = :component AND lac_n.name = :name';
        $record = $DB->get_record_sql($sql, [
            'component' => $setting->get_component(),
            'name' => $setting->get_name(),
            'context' => $context->id
        ]);

        if (isset($record->config_id)) {
            $DB->update_record('local_advconf_config', (object)[
                'id' => $record->config_id,
                'config' => $value,
            ]);
        } else {
            $DB->insert_record('local_advconf_config', (object)[
                'name' => $record->name_id,
                'context' => $context->id,
                'config' => $value,
            ]);
        }
    }
}