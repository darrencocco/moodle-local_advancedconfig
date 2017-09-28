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
 * Cache data loader for configuration items.
 *
 * @package local_advancedconfig\dao
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_advancedconfig\dao;

defined('MOODLE_INTERNAL') || die();

use cache_definition;
use local_advancedconfig\model\config as container_model;

class config implements \cache_data_source {

    /** @var config */
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
            self::$instance = new config();
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
        $keycomponents = explode('/', $key);
        $sql = 'SELECT lac_conf.context, lac_c.component, lac_n.name, lac_n.id AS nameid, lac_conf.config
                  FROM {local_advconf_config} lac_conf
                  JOIN {local_advconf_name} lac_n ON lac_conf.name = lac_n.id
                  JOIN {local_advconf_component} lac_c ON lac_n.component = lac_c.id
                 WHERE lac_c.component = :component AND lac_n.name = :name';
        $records = $DB->get_records_sql($sql, ['component' => $keycomponents[0], 'name' => $keycomponents[1]]);
        $contextmaps = [];
        $nameid = null;
        foreach ($records as $record) {
            if(is_null($nameid)) {
                $nameid = $record->nameid;
            }
            $contextmaps[$record->context] = $record->config;
        }
        return new container_model($keycomponents[0], $keycomponents[1], $nameid, $contextmaps);
    }

    /**
     * Loads several keys for the cache.
     *
     * @param array $keys An array of keys each of which will be string|int.
     * @return array An array of matching data items.
     */
    public function load_many_for_cache(array $keys) {
        $records = [];
        foreach ($keys as $key) {
            $records[$key] = $this->load_for_cache($key);
        }
        return $records;
    }
}