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
namespace local_advancedconfig;

defined('MOODLE_INTERNAL') || die();

use local_advancedconfig\helper\classes;
use local_advancedconfig\model\setting_definition;
use local_advancedconfig\model\settings;
use local_advancedconfig\model\tree;
use local_advancedconfig\model\tree\branch;

class scanner {
    /**
     * @return setting_definition[]
     */
    public static function scan_settings() {
        $plugins = classes::find_classes_in_plugins('*', '/settings', 'local_advancedconfig\\model\\settings', true);
        $settings = [];
        foreach ($plugins as $plugin) {
            foreach ($plugin as $class) {
                /** @var settings $settingset */
                $settingset = new $class();
                foreach ($settingset->settings_defined() as $setting) {
                    $settings[$setting->get_fqn()] = $setting;
                }
            }
        }
        return $settings;
    }

    /**
     * Returns a tree of which their leaves define either
     * references to a series of config leafSettings, a link to
     * an "external page" that defines a single setting or
     * a link to an "external page" that does not feed back
     * to a setting.
     * @return branch[]
     */
    public static function scan_tree() {
        $plugins = classes::find_classes_in_plugins('*', '/settings', 'local_advancedconfig\\model\\tree', true);
        /** @var branch[] $treedata */
        $treedata = [];
        foreach ($plugins as $plugin) {
            foreach ($plugin as $class) {
                /** @var tree $branches */
                $branches = new $class();
                foreach ($branches->get_branches() as $branch) {
                    $treedata[$branch->get_name()] = $branch;
                }
            }
        }
        return $treedata;
    }
}