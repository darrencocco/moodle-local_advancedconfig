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
 * Shim layer for admin_setting_config* classes.
 *
 * @package local_advancedconfig\helper
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_advancedconfig\helper;

defined('MOODLE_INTERNAL') || die();

use local_advancedconfig\event\user_updated_config;
use local_advancedconfig\model\config;
use local_advancedconfig\model\setting_definition;

class admin_setting_redirector extends \admin_setting {
    /** @var \admin_setting */
    private $coresetting;
    /** @var setting_definition */
    private $definition;
    /** @var \context */
    private $context;

    public function __construct(\admin_setting $coresetting, setting_definition $definition, \context $context) {
        $this->coresetting = $coresetting;
        // Ugly hacks because admin_setting doesn't encapsulate it's variables.
        $this->name = $coresetting->name;
        $this->visiblename = $coresetting->visiblename;
        $this->description = $coresetting->description;
        $this->defaultsetting = $coresetting->defaultsetting;
        $this->updatedcallback = $coresetting->updatedcallback;
        $this->plugin = $coresetting->plugin;
        $this->nosave = $coresetting->nosave;
        $this->affectsmodinfo = $coresetting->affectsmodinfo;

        $this->definition = $definition;
        $this->context = $context;
    }

    public function __call($name, $arguments) {
        return call_user_func_array([$this->coresetting, $name], $arguments);
    }

    public function __get($name) {
        return $this->coresetting->$name;
    }
    /**
     * Returns current value of this setting
     * @return mixed array or string depending on instance, NULL means not set yet
     */
    public function get_setting() {
        $cache = \cache::make('local_advancedconfig', 'config');
        /** @var config $settings */
        $settings = $cache->get($this->definition->get_fqn());
        return $this->definition->process_from_storage($settings->get_value_single_context($this->context->id));
    }

    /**
     * Store new setting
     *
     * @param mixed $data string or array, must not be NULL
     * @return string empty string if ok, string error message otherwise
     */
    public function write_setting($data) {
        if (!$this->definition->validate_input($data)) {
            return 'Invalid input';
        }
        $cleaneddata = $this->definition->clean_input($data);
        $cache = \cache::make('local_advancedconfig', 'config');
        /** @var config $settings */
        $settings = $cache->get($this->definition->get_fqn());
        $storedvalue = $settings->get_value_single_context($this->context->id);
        if ($storedvalue == $cleaneddata || ($storedvalue === null && $data == '')) {
            return '';
        }
        $event = user_updated_config::create([
            'objectid' => $this->definition->get_fqn(),
            'contextid' => $this->context->id,
            'other' => [
                'definition' => $this->definition,
                'data' => $this->definition->prepare_for_storage($data),
                ],
        ]);
        $event->trigger();
        return '';
    }

    /**
     * Redirects request to shimmed object.
     *
     * @param mixed $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        return $this->coresetting->output_html($data, $query);
    }
}