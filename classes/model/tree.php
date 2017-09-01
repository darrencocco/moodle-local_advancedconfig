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
 * Public interface for other plugins to implement
 * for defining admin settings tree layouts and
 * admin page layout.
 *
 * @package local_advancedconfig\model
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_advancedconfig\model;

defined('MOODLE_INTERNAL') || die();

use local_advancedconfig\model\tree\branch;

/**
 * Interface tree
 *
 * Implement interface in the $plugin/classes/setting/
 * directory, you may implement this interface in multiple
 * classes.
 *
 * P.S. This can be implemented alongside the settings interface
 * in the same class.
 *
 * @api
 * @package local_advancedconfig\model
 */
interface tree {

    public static function get_instance();

    /**
     * @return branch[]
     */
    public function get_branches();
}