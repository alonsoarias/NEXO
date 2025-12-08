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
 * Factory class for calendar type instances.
 *
 * @package    core_calendar
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_calendar;

defined('MOODLE_INTERNAL') || die();

/**
 * Factory class for creating calendar type instances.
 *
 * @package    core_calendar
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class type_factory {

    /** @var array Cache of calendar instances */
    private static $calendars = [];

    /**
     * Returns an instance of a calendar type.
     *
     * @param string|null $type The calendar type to load. If null, uses user preference or Gregorian.
     * @return type_base The calendar type instance
     */
    public static function get_calendar_instance($type = null) {
        // Always return Gregorian calendar since that's the only one we support now
        if (!isset(self::$calendars['gregorian'])) {
            self::$calendars['gregorian'] = new gregorian_calendar();
        }
        return self::$calendars['gregorian'];
    }

    /**
     * Returns a list of available calendar types.
     *
     * @return array List of available calendar types
     */
    public static function get_list_of_calendar_types() {
        return ['gregorian' => 'Gregorian'];
    }
}
