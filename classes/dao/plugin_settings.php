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

use cache_definition;
use \local_advancedconfig\model\plugin_settings as pluginsettings_model;

class plugin_settings implements \cache_data_source {

    /** @var plugin_settings */
    protected static $instance = null;

    /**
     * Returns an instance of the data source class that the cache can use for loading data using the other methods
     * specified by this interface.
     *
     * @param cache_definition $definition
     * @return object
     */
    public static function get_instance_for_cache(cache_definition $definition) {
        if (is_null(self::$instance)) {
            self::$instance = new plugin_settings();
        }
        return self::$instance;
    }

    /**
     * Loads the data for the key provided ready formatted for caching.
     *
     * @param string|int $key The key to load.
     * @return mixed What ever data should be returned, or false if it can't be loaded.
     */
    public function load_for_cache($key) {
        global $DB;
        $sql = 'SELECT name
                  FROM {local_advancedconfig_name} lac_n
                  JOIN {local_advconf_component} lac_c ON lac_c.id = lac_n.component
                 WHERE lac_c.component = :componentname';
        $settings = $DB->get_field_sql($sql, ['componentname' => $key]);
        return new pluginsettings_model($key, $settings);
    }

    /**
     * Loads several keys for the cache.
     *
     * @param array $keys An array of keys each of which will be string|int.
     * @return array An array of matching data items.
     */
    public function load_many_for_cache(array $keys) {
        global $DB;
        list($sqlsnippet, $params) = $DB->get_in_or_equal($keys);
        $sql = "SELECT lac_n.id, lac_c.component, lac_n.name
                  FROM {local_advconf_name} lac_n
                  JOIN {local_advconf_component} lac_c ON lac_c.id = lac_n.component
                 WHERE lac_c.component $sqlsnippet";
        $settingrecords = $DB->get_records_sql($sql, $params);
        $recordcollator = array_fill_keys($keys, []);
        foreach ($settingrecords as $record) {
            $recordcollator[$record->component][] = $record->name;
        }
        $returncollator = [];
        foreach ($recordcollator as $component => $settings) {
            $returncollator[$component] = new pluginsettings_model($component, $settings);
        }
        return $returncollator;
    }
}