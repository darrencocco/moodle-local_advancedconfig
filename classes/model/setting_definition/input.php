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
 * Eventually used for web services information.
 *
 * @package local_advancedconfig\model\setting_definition
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_advancedconfig\model\setting_definition;

defined('MOODLE_INTERNAL') || die();

class input {
    const TEXT = 0;
    const HTML = 1;

    private $type;
    private $datasource;

    public function __construct($type, $datasource = null) {
        $this->type = $type;
        $this->datasource = $datasource;
    }

    /**
     * @return integer
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function get_data_source() {
        return $this->datasource;
    }


}