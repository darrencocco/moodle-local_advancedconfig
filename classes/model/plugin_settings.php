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
 * Model for plugin setting lists.
 *
 * @package local_advancedconfig\model
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_advancedconfig\model;

defined('MOODLE_INTERNAL') || die();

/**
 * Class plugin_settings
 *
 * Used to cache a list of all known config settings under a specific plugin.
 * Used in calls to get_config("plugin name");
 *
 * @package local_advancedconfig\model
 */
class plugin_settings implements \cacheable_object {

    /** @var string */
    private $plugin;

    /** @var string[] */
    private $settings;

    public function __construct($plugin, array $settings) {
        $this->plugin = $plugin;
        $this->settings = $settings;
    }

    /**
     * @return string[]
     */
    public function get_settings() {
        return $this->settings;
    }

    /**
     * @return string
     */
    public function get_plugin() {
        return $this->plugin;
    }



    /**
     * Prepares the object for caching. Works like the __sleep method.
     *
     * @return mixed The data to cache, can be anything except a class that implements the cacheable_object... that would
     *      be dumb.
     */
    public function prepare_to_cache() {
        return (object)[
            'plugin' => $this->get_plugin(),
            'settings' => $this->get_settings(),
        ];
    }

    /**
     * Takes the data provided by prepare_to_cache and reinitialises an instance of the associated from it.
     *
     * @param mixed $data
     * @return object The instance for the given data.
     */
    public static function wake_from_cache($data) {
        return new plugin_settings($data->plugin, $data->settings);
    }
}