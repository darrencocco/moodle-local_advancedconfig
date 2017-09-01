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
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

/**
 * Gets called when the category settings tree is being built,
 * adds any valid settings in an "Advanced Category" plugin.
 *
 * @param $navigation
 * @param $context
 */
function local_advancedconfig_extend_navigation_category_settings($navigation, $context) {
    append_children($navigation, \local_advancedconfig\helper\admin_tree::get_advancedconfig_tree($context), $context);
}

/**
 * Mostly lifted from settings_navigation::load_administration_settings
 *
 * @param navigation_node $parent
 * @param part_of_admin_tree $branch
 * @param context $context
 */
function append_children(\navigation_node $parent, \part_of_admin_tree $branch, \context $context) {
    // We have a reference branch that we can access and is not hidden `hurrah`
    // Now we need to display it and any children it may have
    if (!$branch->check_access()) {
        // ACCESS DENIED!!!
        return;
    }
    global $CFG;
    $url = null;
    $icon = null;
    if ($branch instanceof admin_settingpage) {
        $url = new moodle_url('/local/advancedconfig/conf.php', array('section' => $branch->name, 'context' => $context->id));
    } else if ($branch instanceof admin_externalpage) {
        $url = $branch->url;
    } else if (!empty($CFG->linkadmincategories) && $branch instanceof admin_category) {
        $url = new moodle_url('/'.$CFG->admin.'/category.php', array('category' => $branch->name));
    }

    // Add the branch
    $reference = $parent->add($branch->visiblename, $url, navigation_node::TYPE_SETTING, null, $branch->name, $icon);

    if ($branch->is_hidden()) {
        if (($branch instanceof admin_externalpage || $branch instanceof admin_settingpage) && $branch->name == $parent->adminsection) {
            $reference->add_class('hidden');
        } else {
            $reference->display = false;
        }
    }

    // Check if this branch has children
    if ($reference && isset($branch->children) && is_array($branch->children) && count($branch->children)>0) {
        foreach ($branch->children as $branch) {
            // Generate the child branches as well now using this branch as the reference
            append_children($reference, $branch, $context);
        }
    } else {
        $reference->icon = new pix_icon('i/settings', '');
    }
}