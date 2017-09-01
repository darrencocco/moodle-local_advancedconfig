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
 * Helper functions for building admin trees.
 *
 * @package local_advancedconfig\helper
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_advancedconfig\helper;

defined('MOODLE_INTERNAL') || die();

use local_advancedconfig\model\admin_tree_root;
use local_advancedconfig\model\setting_definition;
use local_advancedconfig\model\tree\branch;
use local_advancedconfig\model\tree\interfaces\leaf_settings;
use local_advancedconfig\scanner;

class admin_tree {
    /**
     * Returns a fully populated advancedconfig settings
     * tree.
     *
     * @param \context $context
     * @param bool $fulltree
     * @return admin_tree_root
     */
    public static function get_advancedconfig_tree (\context $context, $fulltree = false) {
        $root = new admin_tree_root($fulltree);
        self::append_to_admin_tree($context, $root, $fulltree);
        return $root;
    }

    /**
     * Searches through all settings defined through advancedconfig and
     * appends them to the root node.
     *
     * @param \context $context
     * @param \parentable_part_of_admin_tree $root
     * @param boolean $fulltree
     */
    public static function append_to_admin_tree(\context $context, \parentable_part_of_admin_tree $root, $fulltree) {
        $branches = scanner::scan_tree();
        $definedsettings = null;
        if ($fulltree) {
            $definedsettings = scanner::scan_settings();
        }
        while (count($branches) > 0) {
            foreach ($branches as $name => $branch) {
                $existingnode = $root->locate($branch->get_name());
                if ($existingnode) {
                    if ($fulltree && $branch instanceof leaf_settings) {
                        foreach ($branch->get_page_settings() as $moduledefinition) {
                            self::add_admin_setting($context, $existingnode, $moduledefinition, $definedsettings);
                        }
                    }
                    unset($branches[$name]);
                } else if (!array_key_exists($branch->get_parent(), $branches)) {
                    if ($root->locate($branch->get_parent())) {
                        $root->add($branch->get_parent(),
                            self::polyfill_admin_tree_builder($context, $branch, $fulltree, $definedsettings));
                    } else {
                        $root->add('root',
                            self::polyfill_admin_tree_builder($context, $branch, $fulltree, $definedsettings));
                    }
                    unset($branches[$name]);
                }
            }
        }
    }

    /**
     * Returns an appropriate piece of the admin tree based in the passed
     * in branch.
     *
     * @param \context $context
     * @param branch $branch
     * @param boolean $fulltree
     * @param setting_definition[] $definedsettings
     * @return \part_of_admin_tree
     */
    private static function polyfill_admin_tree_builder(\context $context, branch $branch, $fulltree, $definedsettings) {
        if ($branch instanceof \local_advancedconfig\model\tree\interfaces\leaf_settings) {
            $settingpage = new \admin_settingpage(
                $branch->get_name(), $branch->get_langstring(), $branch->get_capability(), false, $context);
            if ($fulltree) {
                foreach ($branch->get_page_settings() as $moduledefinition) {
                    self::add_admin_setting($context, $settingpage, $moduledefinition, $definedsettings);
                }
            }
            return $settingpage;
        } else {
            return new \admin_category($branch->get_name(), $branch->get_langstring());
        }
    }

    /**
     * Appends an admin setting to a admin tree using the
     * admin_setting_redirector to intercept reading and writing.
     *
     * @param \context $context
     * @param \admin_settingpage $settingpage
     * @param \admin_setting $setting
     * @param setting_definition[] $definedsettings
     */
    private static function add_admin_setting(
        \context $context, \admin_settingpage $settingpage, \admin_setting $setting, $definedsettings) {

        $settingpage->add(new admin_setting_redirector(
            $setting,
            $definedsettings[$setting->plugin . '/' .$setting->name],
            $context));
    }
}