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
 * Scanners for interfaces implemented by other plugins.
 *
 * @package local_advancedconfig
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_advancedconfig;

defined('MOODLE_INTERNAL') || die();

use local_advancedconfig\dao\child_classes;
use local_advancedconfig\helper\classes;
use local_advancedconfig\model\setting_definition;
use local_advancedconfig\model\settings;
use local_advancedconfig\model\tree;
use local_advancedconfig\model\tree\branch;

class scanner {
    /**
     * Scans for classes implementing the settings interface.
     *
     * @return setting_definition[]
     */
    public static function scan_settings() {
        $cache = \cache::make('local_advancedconfig', 'childclassmap');
        // Stopgap for MDL-42012 and MDL-43356.
        if ($cache instanceof \cache_disabled) {
            $childscanner = child_classes::get_instance_for_cache(new \cache_definition());
            $plugins = $childscanner->load_for_cache('local_advancedconfig\\model\\settings');
        } else {
            $plugins = $cache->get('local_advancedconfig\\model\\settings');
        }
        $settings = [];
        foreach ($plugins as $plugin) {
            foreach ($plugin as $class) {
                /** @var settings $settingset */
                $settingset = $class::get_instance();
                foreach ($settingset->settings_defined() as $setting) {
                    $settings[$setting->get_fqn()] = $setting;
                }
            }
        }
        return $settings;
    }

    /**
     * Returns a tree of which their leaves define either
     * references to a series of config settings, a link to
     * an "external page" that defines a single setting or
     * a link to an "external page" that does not feed back
     * to a setting.
     *
     * @return branch[]
     */
    public static function scan_tree() {
        $cache = \cache::make('local_advancedconfig', 'childclassmap');
        // Stopgap for MDL-42012 and MDL-43356.
        if ($cache instanceof \cache_disabled) {
            $childscanner = child_classes::get_instance_for_cache(new \cache_definition());
            $plugins = $childscanner->load_for_cache('local_advancedconfig\\model\\tree');
        } else {
            $plugins = $cache->get('local_advancedconfig\\model\\tree');
        }
        /** @var branch[] $treedata */
        $treedata = [];
        foreach ($plugins as $plugin) {
            foreach ($plugin as $class) {
                /** @var tree $branches */
                $branches = $class::get_instance();
                foreach ($branches->get_branches() as $branch) {
                    $treedata[$branch->get_name()] = $branch;
                }
            }
        }
        return $treedata;
    }
}