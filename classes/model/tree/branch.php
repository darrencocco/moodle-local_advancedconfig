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
namespace local_advancedconfig\model\tree;

defined('MOODLE_INTERNAL') || die();

class branch {
    /** @var string */
    private $parent;
    /** @var string */
    private $name;
    /** @var \lang_string */
    private $langstring;

    /**
     * branch constructor.
     * @param string $parent
     * @param string $name
     * @param \lang_string $langstring
     */
    public function __construct($parent, $name, \lang_string $langstring) {
        $this->parent = $parent;
        $this->name = $name;
        $this->langstring = $langstring;
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
}