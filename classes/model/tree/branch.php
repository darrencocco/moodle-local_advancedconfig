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
 * Basic implementation of branch interface.
 *
 * @package local_advancedconfig\model\tree
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_advancedconfig\model\tree;

defined('MOODLE_INTERNAL') || die();

use local_advancedconfig\model\tree\interfaces\branch as branch_interface;

class branch implements branch_interface {
    /** @var string */
    private $parent;
    /** @var string */
    private $name;
    /** @var \lang_string */
    private $langstring;
    /** @var string */
    private $capability;

    /**
     * branch constructor.
     * @param string $parent
     * @param string $name
     * @param \lang_string $langstring
     * @param string|string[] $capability
     */
    public function __construct($parent, $name, \lang_string $langstring, $capability) {
        $this->parent = $parent;
        $this->name = $name;
        $this->langstring = $langstring;
        $this->capability = $capability;
    }

    /**
     * @return string
     */
    public function get_parent() {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * @return \lang_string
     */
    public function get_langstring() {
        return $this->langstring;
    }

    /**
     * @return string
     */
    public function get_capability() {
        return $this->capability;
    }
}