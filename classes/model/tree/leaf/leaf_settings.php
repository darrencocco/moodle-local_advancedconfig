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
 * Basic implementation of leaf_settings interface.
 *
 * @package local_advancedconfig\model\tree\leaf
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_advancedconfig\model\tree\leaf;

defined('MOODLE_INTERNAL') || die();

use admin_setting;
use local_advancedconfig\model\tree\branch;
use local_advancedconfig\model\tree\interfaces\leaf_settings as interface_leaf_settings;

class leaf_settings extends branch implements interface_leaf_settings {
    private $settings;


    /**
     * leaf_settings constructor.
     * @param string $parent
     * @param string $name
     * @param \lang_string $langstring
     * @param string|string[] $capability
     * @param admin_setting[] $settings
     */
    public function __construct($parent, $name, \lang_string $langstring, $capability, array $settings) {
        parent::__construct($parent, $name, $langstring, $capability);
        $this->settings = $settings;
    }

    /**
     * @return admin_setting[]
     */
    public function get_page_settings() {
        return $this->settings;
    }
}