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
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use local_advancedconfig\context_config;

class behat_local_advancedconfig extends behat_base {
    /**
     * @Given /^the following advanced config values are set as admin:$/
     * @param TableNode $table
     */
    public function the_following_advanced_config_settings_are_set_by_admin(TableNode $table) {
        $configs = $table->getColumnsHash();
        if (count($configs) == 0) {
            return;
        }
        foreach ($configs as $config) {
            // |category|component|name|value|
            $categoryname = $config['category'];
            $component = $config['component'];
            $name = $config['name'];
            $value = $config['value'];
            if ($categoryname === '0') {
                $context = context_system::instance();
            } else {
                $context = coursecat::get($this->get_category_id($categoryname))->get_context();
            }
            context_config::set_config($context, $component, $name, $value);
        }
    }

    /**
     * @Then /^the config setting "(?P<name>(?:[^"]|\\")*)" from "(?P<component>(?:[^"]|\\")*)" in the "(?P<category>(?:[^"]|\\")*)" category should be "(?P<value>(?:[^"]|\\")*)"$/
     * @param string $category
     * @param string $component
     * @param string $name
     * @param string $value
     * @throws ExpectationException
     */
    public function config_setting_should_be($category, $component, $name, $value) {
        if ($category === '0') {
            $context = context_system::instance();
        } else {
            $context = coursecat::get($this->get_category_id($category))->get_context();
        }
        $retrievedvalue = context_config::get_config($context, $component, $name);
        if ($retrievedvalue !== $value) {
            throw new ExpectationException("The config did not match $value", $this->getSession());
        }
    }

    private function get_category_id($idnumber) {
        global $DB;
        try {
            return $DB->get_field('course_categories', 'id', array('idnumber' => $idnumber), MUST_EXIST);
        } catch (dml_missing_record_exception $ex) {
            throw new ExpectationException(sprintf("There is no category in the database with the idnumber '%s'", $idnumber));
        }
    }
}