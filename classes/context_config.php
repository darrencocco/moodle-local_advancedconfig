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
 * API for end users.
 * @api
 * @package local_advancedconfig
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_advancedconfig;

defined('MOODLE_INTERNAL') || die();

use local_advancedconfig\model\plugin_settings;

class context_config {
    /**
     * Get the valid configuration for the context.
     *
     * @api
     * @param \context $context
     * @param string $plugin
     * @param string $name
     * @return string|\stdClass
     */
    public static function get_config(\context $context, $plugin, $name = null) {
        if ($context->id === \context_system::instance()->id) {
            return \get_config($plugin, $name);
        }
        $configcache = \cache::make('local_advancedconfig', 'config');
        if ($name !== null) {
            return ($configcache->get($plugin . '/' . $name))->get_value($context->path);
        } else {
            $pluginsettingscache = \cache::make('local_advancedconfig', 'pluginsettings');
            /** @var plugin_settings $settingsavailable */
            $settingsavailable = $pluginsettingscache->get($plugin);
            $results = new \stdClass();
            foreach ($settingsavailable->get_settings() as $setting) {
                $results->$setting = ($configcache->get($plugin . '/' . $setting))->get_value($context->path);
            }
            return $results;
        }
    }

    /**
     * Placeholder
     *
     * TODO: Write me!
     *
     * @param $context
     * @param $plugin
     * @param $name
     * @param $value
     * @return bool
     */
    public static function set_config($context, $plugin, $name, $value) {
        if ($context == \context_system::instance()) {
            return \set_config($name, $value, $plugin);
        }
    }
}