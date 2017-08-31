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
namespace local_advancedconfig\model;

defined('MOODLE_INTERNAL') || die();

class config implements \cacheable_object {
    /** @var string */
    private $plugin;

    /** @var string */
    private $name;

    /** @var string[] */
    private $children;

    /**
     * container constructor.
     * @param string $plugin
     * @param string $name
     * @param string[] $children
     */
    public function __construct($plugin, $name, array $children) {
        $this->plugin = $plugin;
        $this->name = $name;
        $this->children = $children;
    }

    /**
     * @param string $contextpath
     * @return string
     */
    public function get_value($contextpath) {
        $contextids = array_reverse(explode('/', $contextpath));
        foreach ($contextids as $contextid) {
            if (array_key_exists($contextid, $this->children)) {
                return $this->children[$contextid];
            }
        }
        return '';
    }

    /**
     * @param string $contextid
     * @return string|null
     */
    public function get_value_single_context($contextid) {
        if (array_key_exists($contextid, $this->children)) {
            return $this->children[$contextid];
        } else {
            return null;
        }
    }

    /**
     * Prepares the object for caching. Works like the __sleep method.
     *
     * @return mixed The data to cache, can be anything except a class that implements the cacheable_object... that would
     *      be dumb.
     */
    public function prepare_to_cache() {
        return (object)[
            'plugin' => $this->plugin,
            'name' => $this->name,
            'children' => $this->children];
    }

    /**
     * Takes the data provided by prepare_to_cache and reinitialises an instance of the associated from it.
     *
     * @param mixed $data
     * @return object The instance for the given data.
     */
    public static function wake_from_cache($data) {
        return new config($data->plugin, $data->name, $data->children);
    }
}