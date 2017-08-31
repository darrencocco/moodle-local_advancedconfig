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

use local_advancedconfig\dao\setting;
use local_advancedconfig\event\user_updated_config;
use local_advancedconfig\model\setting_definition;

class config_observer {
    public static function write(user_updated_config $event) {
        $data = $event->get_data()['other'];
        /** @var setting_definition $definition */
        $definition = $data['definition'];
        $input = $data['data'];
        has_capability($definition->required_capability(), $event->get_context(), $event->userid);
        if ($definition->validate_input($input)) {
            setting::write($definition, $event->get_context(), $input);
        }
        \cache_helper::invalidate_by_definition('local_advancedconfig', 'config', [], $definition->get_fqn());
    }
}