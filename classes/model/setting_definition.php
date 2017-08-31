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

use local_advancedconfig\model\setting_definition\validate\validator;
use local_advancedconfig\model\setting_definition\input;

abstract class setting_definition {
    private $component;
    private $name;
    private $basictype;
    private $inputtype;
    private $default;

    /**
     * setting_definition constructor.
     * @param string $component
     * @param string $name
     * @param validator $basictype
     * @param input $inputtype
     * @param mixed $default
     */
    public function __construct($component, $name, validator $basictype, input $inputtype, $default) {
        $this->component = $component;
        $this->name = $name;
        $this->basictype = $basictype;
        $this->inputtype = $inputtype;
        $this->default = $default;
    }

    /**
     * Converts the configuration data into a state
     * ready to be stored in the DB.
     *
     * @param mixed $object
     * @return string
     */
    abstract public function prepare_for_storage($object);

    /**
     * Override this function with anything that needs
     * to be done as part of the configuration saving
     * process.
     */
    public function post_storage_action() {
    }

    /**
     *
     * @param string $string
     */
    abstract public function process_from_storage($string);

    /**
     * Validates the input.
     *
     * @param mixed $input
     * @return boolean
     */
    public function validate_input($input) {
        return $this->basictype->validate($input);
    }

    public function get_input_type() {
        return $this->inputtype->get_type();
    }

    public function required_capability() {
        return 'moodle/site:config';
    }

    protected function validate() {
        return true;
    }

    public function get_component() {
        return $this->component;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_fqn() {
        return $this->component . '/' . $this->name;
    }
}