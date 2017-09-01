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
 * This is a direct copy of the changes suggested by Marina Glancy
 * in https://github.com/marinaglancy/moodle/commit/98e83ddbe80228ebef45a9bb65e218ee9371f566
 *
 * I would like to thank her for the work and for pointing me to it.
 *
 * @package    local_advancedconfig\helper
 * @copyright  2017 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_advancedconfig\helper;

defined('MOODLE_INTERNAL') || die();

class classes extends \core_component {
    /**
     * Get a list of all the plugins of a given type that define a certain class
     * in a certain file. The plugin component names and class names are returned.
     *
     * @param string $plugintype the type of plugin, e.g. 'mod' or 'report'.
     * @param string $class the part of the name of the class after the
     *      frankenstyle prefix. e.g 'thing' if you are looking for classes with
     *      names like report_courselist_thing. If you are looking for classes with
     *      the same name as the plugin name (e.g. qtype_multichoice) then pass ''.
     *      Frankenstyle namespaces are also supported.
     * @param string $file the name of file within the plugin that defines the class.
     * @return array with frankenstyle plugin names as keys (e.g. 'report_courselist', 'mod_forum')
     *      and the class names as values (e.g. 'report_courselist_thing', 'qtype_multichoice').
     */
    public static function get_plugin_list_with_class($plugintype, $class, $file = null) {
        global $CFG; // Necessary in case it is referenced by included PHP scripts.
        if ($class) {
            $suffix = '_' . $class;
        } else {
            $suffix = '';
        }
        $pluginclasses = array();
        $plugins = self::get_plugin_list($plugintype);
        foreach ($plugins as $plugin => $fulldir) {
            // Try class in frankenstyle namespace.
            if ($class) {
                $classname = '\\' . $plugintype . '_' . $plugin . '\\' . $class;
                if (class_exists($classname, true)) {
                    $pluginclasses[$plugintype . '_' . $plugin] = $classname;
                    continue;
                }
            }
            // Try autoloading of class with frankenstyle prefix.
            $classname = $plugintype . '_' . $plugin . $suffix;
            if (class_exists($classname, true)) {
                $pluginclasses[$plugintype . '_' . $plugin] = $classname;
                continue;
            }
            // Fall back to old file location and class name.
            if ($file and file_exists("$fulldir/$file")) {
                include_once("$fulldir/$file");
                if (class_exists($classname, false)) {
                    $pluginclasses[$plugintype . '_' . $plugin] = $classname;
                    continue;
                }
            }
        }
        return $pluginclasses;
    }
    /**
     * Checks if given class in autoloaded location is a real class
     *
     * @param string $classname
     * @param string $classfile
     * @param string $parentclass optional name of the parent class
     * @param bool $nonabstractonly if true will return only non-abstract classes
     * @return bool
     */
    protected static function check_class($classname, $classfile, $parentclass = null, $nonabstractonly = false) {
        global $CFG;
        if (!class_exists($classname, false)) {
            if (!file_exists($classfile)) {
                return false;
            }
            // Some deprecated event classes show debugging message on loading the file, this will hide such debugging messages.
            // TODO MDL-46214: think of better way of deprecating events.
            $debuglevel          = $CFG->debug;
            $debugdisplay        = $CFG->debugdisplay;
            $debugdeveloper      = $CFG->debugdeveloper;
            $CFG->debug          = 0;
            $CFG->debugdisplay   = false;
            $CFG->debugdeveloper = false;
            require_once($classfile);
            // Now enable developer debugging as event information has been retrieved.
            $CFG->debug          = $debuglevel;
            $CFG->debugdisplay   = $debugdisplay;
            $CFG->debugdeveloper = $debugdeveloper;
            if (!class_exists($classname, false)) {
                return false;
            }
        }
        if ($parentclass || $nonabstractonly) {
            $testclass = new \ReflectionClass($classname);
            if ($parentclass &&
                !(interface_exists($parentclass) && $testclass->implementsInterface($parentclass)) &&
                !(class_exists($parentclass) && $testclass->isSubclassOf($parentclass))) {
                    return false;
            }
            if ($nonabstractonly && $testclass->isAbstract()) {
                return false;
            }
        }
        return true;
    }
    /**
     * Resolves the plugintype and returns the list of plugins
     *
     * This is a help method to resolve user input
     *
     * @param string|array $plugintype ('mod', 'mod_assign', '*', or array of several names/types)
     * @param bool $indexedbytype adds nesting level in the return array
     * @return array if $indexedbytype==false: pluginfullname=>plugindir
     *          if $indexbytype==true: plugintype=>pluginname=>plugindir
     */
    protected static function resolve_plugin_type($plugintype, $indexedbytype = false) {
        $plugins = array();
        if (is_array($plugintype)) {
            // Argument $plugintype is an array, call self recursively for all elements.
            foreach ($plugintype as $oneplugintype) {
                $list = self::resolve_plugin_type($oneplugintype, $indexedbytype);
                if ($indexedbytype) {
                    foreach ($list as $type => $subplugins) {
                        $plugins[$type] = isset($plugins[$type]) ? $plugins[$type] : array();
                        foreach ($subplugins as $plugin => $dir) {
                            $plugins[$type][$plugin] = $dir;
                        }
                    }
                } else {
                    $plugins = array_merge($plugins, $list);
                }
            }
            return $plugins;
        }
        if ($plugintype === '*') {
            // All plugin types were requested.
            $plugins = self::$plugins;
        } else if (array_key_exists($plugintype, self::$plugins)) {
            // Plugin type was requested.
            $plugins = array($plugintype => self::$plugins[$plugintype]);
        } else {
            // Individual plugin was requested.
            list($type, $plugin) = self::normalize_component($plugintype);
            if (!empty(self::$plugins[$type][$plugin])) {
                $plugins = array($type => array($plugin => self::$plugins[$type][$plugin]));
            }
        }
        if ($indexedbytype || empty($plugins)) {
            return $plugins;
        }
        $flatlist = array();
        foreach ($plugins as $type => $subplugins) {
            foreach ($subplugins as $name => $dir) {
                $flatlist[$type.'_'.$name] = $dir;
            }
        }
        return $flatlist;
    }
    /**
     * Resolves the component identifiers and returns the list of components with non-empty directories.
     *
     * @param string|array $componenttype ('core', 'core_course', '*', or array of several subsystems)
     * @return string[] array of componentname=>componentdir
     */
    protected static function resolve_subsystem_type($componenttype) {
        global $CFG;
        $components = array();
        if (is_array($componenttype)) {
            // Argument $componenttype is an array, call self recursively for all elements.
            foreach ($componenttype as $type) {
                $components = array_merge($components, self::resolve_subsystem_type($type));
            }
            return $components;
        }
        if ($componenttype === '*') {
            // All components were requested.
            $components['core'] = $CFG->libdir;
            foreach (self::$subsystems as $subsystem => $dir) {
                if ($dir) {
                    $components['core_'.$subsystem] = $dir;
                }
            }
        } else if ($componenttype === 'core') {
            $components['core'] = $CFG->libdir;
        } else {
            // Individual component was requested.
            list($type, $plugin) = self::normalize_component($componenttype);
            if (($type === 'core') && !empty(self::$subsystems[$plugin])) {
                $components['core_'.$plugin] = self::$subsystems[$plugin];
            }
        }
        return $components;
    }
    /**
     * Returns the list of classes in plugins or core subsystems in the specified autoloaded location
     *
     * This function does not recurse into subfolders
     *
     * @param array $resolvedcomponentslist array componenttype=>componentdir where we need to search
     * @param string $relativepath relative path inside /classes/ directory
     * @param string $parentclass optional name of the parent class
     * @param bool $nonabstractonly if true will return only non-abstract classes
     * @return string[][] list of class names componenttype=>classfilepath=>classname
     */
    protected static function find_classes_in_components($resolvedcomponentslist, $relativepath = '',
                                                         $parentclass = null, $nonabstractonly = false) {
        self::init();
        $relativepath = rtrim(DIRECTORY_SEPARATOR . 'classes'. DIRECTORY_SEPARATOR .
            ltrim($relativepath, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
        foreach ($resolvedcomponentslist as $component => $dir) {
            if ($dir) {
                $dirs[$dir . $relativepath] = $component;
            }
        }
        $classes = array();
        foreach (self::$classmap as $classname => $classfile) {
            $classdir = dirname($classfile);
            if (isset($dirs[$classdir]) && self::check_class($classname, $classfile, $parentclass, $nonabstractonly)) {
                $fullcomponentname = $dirs[$classdir];
                if (!isset($classes[$fullcomponentname])) {
                    $classes[$fullcomponentname] = array();
                }
                $classes[$fullcomponentname][$classfile] = $classname;
            }
        }
        return $classes;
    }
    /**
     * Finds all classes in plugins in the specified autoloaded location.
     *
     * This function does not recurse into subfolders.
     *
     * Examples:
     *   core_component::find_classes_in_plugins('mod', 'event', 'core\base\event', true);
     *   core_component::find_classes_in_plugins('mod_forum', 'event');
     *   core_component::find_classes_in_plugins(array('mod', 'block'), 'reporting');
     *   core_component::find_classes_in_plugins('*', 'navigation');
     *
     * @param string|array $plugintype one of the following:
     *          plugin type, i.e. 'mod', 'block' for all plugins of this type;
     *          plugin name, i.e. 'mod_assign', 'block_course_overview', etc.;
     *          '*' for any of above;
     *          can also be an array or individual identifiers (list of plugin names or list of plugin types)
     * @param string $relativepath relative path inside /classes/ directory
     * @param string $parentclass optional name of the parent class
     * @param bool $nonabstractonly if true will return only non-abstract classes
     * @return string[][] list of class names pluginname=>classfilepath=>classname
     */
    public static function find_classes_in_plugins($plugintype, $relativepath = '', $parentclass = null, $nonabstractonly = false) {
        $resolvedcomponentslist = self::resolve_plugin_type($plugintype, true);
        $flatlist = array();
        foreach ($resolvedcomponentslist as $type => $subplugins) {
            foreach ($subplugins as $name => $dir) {
                $flatlist[$type.'_'.$name] = $dir;
            }
        }
        return self::find_classes_in_components($flatlist, $relativepath, $parentclass, $nonabstractonly);
    }
    /**
     * Finds all classes in core or core subsystems in the specified autoloaded location.
     *
     * This function does not recurse into subfolders.
     *
     * Examples:
     *   core_component::find_classes_in_subsystems('core', 'event', 'core\base\event', true);
     *   core_component::find_classes_in_subsystems('*');
     *   core_component::find_classes_in_subsystems(array('core_course', 'core_availability')); // If it ever makes sense.
     *
     * @param string|array $componenttype one of the following:
     *          'core' for only lib/classes location;
     *          subsystem name, i.e. 'core_course', 'core_availability', etc.;
     *          '*' for any of above;
     *          can also be an array or individual subsystem names
     * @param string $relativepath relative path inside /classes/ directory
     * @param string $parentclass optional name of the parent class
     * @param bool $nonabstractonly if true will return only non-abstract classes
     * @return string[][] list of class names componenttype=>classfilepath=>classname
     */
    public static function find_classes_in_subsystems($componenttype, $relativepath = '',
                                                      $parentclass = null, $nonabstractonly = false) {
        $resolvedcomponentslist = self::resolve_subsystem_type($componenttype);
        return self::find_classes_in_components($resolvedcomponentslist, $relativepath, $parentclass, $nonabstractonly);
    }
}