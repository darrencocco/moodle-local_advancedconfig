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
 * Mostly lifted from $CFG->dirroot/admin/settings.php
 *
 * @copyright 2017 Monash University (http://www.monash.edu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$section = required_param('section', PARAM_SAFEDIR);
$contextid = required_param('context', PARAM_INT);
$return = optional_param('return', '', PARAM_ALPHA);
$adminediting = optional_param('adminedit', -1, PARAM_BOOL);

/// no guest autologin
require_login(0, false);
$context = context::instance_by_id($contextid);
$PAGE->set_context($context);
$PAGE->set_url('/local/advancedconfig/conf.php', array('section' => $section, 'context' => $contextid));
$PAGE->set_pagetype('admin-setting-' . $section);
$PAGE->set_pagelayout('admin');
$PAGE->navigation->clear_cache();
navigation_node::require_admin_tree();

$adminroot = \local_advancedconfig\helper\admin_tree::get_advancedconfig_tree($context,true); // need all settings
$settingspage = $adminroot->locate($section, true);

if (empty($settingspage) or !($settingspage instanceof admin_settingpage)) {
    if (moodle_needs_upgrading()) {
        redirect(new moodle_url('/admin/index.php'));
    } else {
        print_error('sectionerror', 'admin', "$CFG->wwwroot/$CFG->admin/");
    }
    die;
}

if (!($settingspage->check_access())) {
    print_error('accessdenied', 'admin');
    die;
}

/// WRITING SUBMITTED DATA (IF ANY) -------------------------------------------------------------------------------

$statusmsg = '';
$errormsg  = '';

if ($data = data_submitted() and confirm_sesskey()) {
    if (admin_write_settings($data)) {
        redirect($PAGE->url, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
    }

    if (empty($adminroot->errors)) {
        switch ($return) {
            case 'site': redirect("$CFG->wwwroot/");
            case 'admin': redirect("$CFG->wwwroot/$CFG->admin/");
        }
        redirect($PAGE->url);
    } else {
        $errormsg = get_string('errorwithsettings', 'admin');
        $firsterror = reset($adminroot->errors);
    }
    $settingspage = $adminroot->locate($section, true);
}

if ($PAGE->user_allowed_editing() && $adminediting != -1) {
    $USER->editing = $adminediting;
}

/// print header stuff ------------------------------------------------------------
if (empty($SITE->fullname)) {
    $PAGE->set_title($settingspage->visiblename);
    $PAGE->set_heading($settingspage->visiblename);

    echo $OUTPUT->header();
    echo $OUTPUT->box(get_string('configintrosite', 'admin'));

    if ($errormsg !== '') {
        echo $OUTPUT->notification($errormsg);

    } else if ($statusmsg !== '') {
        echo $OUTPUT->notification($statusmsg, 'notifysuccess');
    }

    // ---------------------------------------------------------------------------------------------------------------

    echo '<form action="' . $PAGE->url . '" method="post" id="adminsettings">';
    echo '<div class="settingsform clearfix">';
    echo html_writer::input_hidden_params($PAGE->url);
    echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
    echo '<input type="hidden" name="return" value="'.$return.'" />';
    // HACK to prevent browsers from automatically inserting the user's password into the wrong fields.
    if (moodle_major_version() < '3.2') {
        echo prevent_form_autofill_password();
    }

    echo $settingspage->output_html();

    echo '<div class="form-buttons"><input class="form-submit" type="submit" value="'.get_string('savechanges','admin').'" /></div>';

    echo '</div>';
    echo '</form>';

} else {
    if ($PAGE->user_allowed_editing()) {
        $url = clone($PAGE->url);
        if ($PAGE->user_is_editing()) {
            $caption = get_string('blockseditoff');
            $url->param('adminedit', 'off');
        } else {
            $caption = get_string('blocksediton');
            $url->param('adminedit', 'on');
        }
        $buttons = $OUTPUT->single_button($url, $caption, 'get');
        $PAGE->set_button($buttons);
    }

    $visiblepathtosection = array_reverse($settingspage->visiblepath);

    $PAGE->set_title("$SITE->shortname: " . implode(": ",$visiblepathtosection));
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();

    if ($errormsg !== '') {
        echo $OUTPUT->notification($errormsg);

    } else if ($statusmsg !== '') {
        echo $OUTPUT->notification($statusmsg, 'notifysuccess');
    }

    // ---------------------------------------------------------------------------------------------------------------

    echo '<form action="' . $PAGE->url . '" method="post" id="adminsettings">';
    echo '<div class="settingsform clearfix">';
    echo html_writer::input_hidden_params($PAGE->url);
    echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
    echo '<input type="hidden" name="return" value="'.$return.'" />';
    // HACK to prevent browsers from automatically inserting the user's password into the wrong fields.
    if (moodle_major_version() < '3.2') {
        echo prevent_form_autofill_password();
    }
    echo $OUTPUT->heading($settingspage->visiblename);

    echo $settingspage->output_html();

    if ($settingspage->show_save()) {
        echo '<div class="form-buttons"><input class="form-submit btn btn-primary" type="submit" value="'.get_string('savechanges','admin').'" /></div>';
    }

    echo '</div>';
    echo '</form>';
}

$PAGE->requires->yui_module('moodle-core-formchangechecker',
    'M.core_formchangechecker.init',
    array(array(
        'formid' => 'adminsettings'
    ))
);
$PAGE->requires->string_for_js('changesmadereallygoaway', 'moodle');

echo $OUTPUT->footer();
