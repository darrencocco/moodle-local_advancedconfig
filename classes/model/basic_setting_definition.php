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
 * Filler class for settings that do not need special processing
 * for storing and retrieving settings.
 *
 * @package local_advancedconfig\model
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_advancedconfig\model;

defined('MOODLE_INTERNAL') || die();

class basic_setting_definition extends setting_definition {

    /**
     * Converts the configuration data into a state
     * ready to be stored in the DB.
     *
     * @param mixed $object
     * @return string
     */
    public function prepare_for_storage($object) {
        return $object;
    }

    /**
     *
     * @param string $string
     * @return string
     */
    public function process_from_storage($string) {
        return $string;
    }
}