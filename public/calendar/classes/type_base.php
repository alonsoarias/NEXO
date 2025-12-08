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
 * Base class for calendar types.
 *
 * @package    core_calendar
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_calendar;

defined('MOODLE_INTERNAL') || die();

/**
 * Base class for calendar type implementations.
 *
 * @package    core_calendar
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class type_base {

    /**
     * Returns the name of the calendar.
     *
     * @return string the calendar name
     */
    abstract public function get_name();

    /**
     * Returns a list of all the possible days for all months.
     *
     * @return array the days
     */
    abstract public function get_days();

    /**
     * Returns a list of all the months.
     *
     * @return array the months
     */
    abstract public function get_months();

    /**
     * Returns the minimum year for the calendar.
     *
     * @return int The minimum year
     */
    abstract public function get_min_year();

    /**
     * Returns the maximum year for the calendar.
     *
     * @return int The maximum year
     */
    abstract public function get_max_year();

    /**
     * Returns a list of all the years.
     *
     * @param int|null $minyear
     * @param int|null $maxyear
     * @return array the years
     */
    abstract public function get_years($minyear = null, $maxyear = null);

    /**
     * Returns a multidimensional array with information for day, month, year
     * and the order they are displayed when selecting a date.
     *
     * @param int $minyear
     * @param int $maxyear
     * @return array the date order
     */
    abstract public function get_date_order($minyear = null, $maxyear = null);

    /**
     * Returns the number of days in a week.
     *
     * @return int the number of days in a week
     */
    abstract public function get_num_weekdays();

    /**
     * Returns an indexed list of all the names of the weekdays.
     *
     * @return array the weekdays
     */
    abstract public function get_weekdays();

    /**
     * Returns the index of the starting week day.
     *
     * @return int the starting week day index
     */
    abstract public function get_starting_weekday();

    /**
     * Returns the number of days in a given month.
     *
     * @param int $year
     * @param int $month
     * @return int the number of days in the month
     */
    abstract public function get_num_days_in_month($year, $month);

    /**
     * Get the day of the week for the given date.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return int the day of the week (0 for Sunday, 6 for Saturday)
     */
    abstract public function get_weekday($year, $month, $day);

    /**
     * Converts the provided timestamp to an array of date info.
     *
     * @param int $timestamp the timestamp
     * @param int|float|string $timezone
     * @return array the date info
     */
    abstract public function timestamp_to_date_array($timestamp, $timezone = 99);

    /**
     * Converts the provided timestamp to a formatted date string.
     *
     * @param int $timestamp the timestamp
     * @param string $format the format string
     * @param int|float|string $timezone
     * @param bool $fixday
     * @param bool $fixhour
     * @return string the formatted date
     */
    abstract public function timestamp_to_date_string($timestamp, $format = '', $timezone = 99, $fixday = true, $fixhour = true);

    /**
     * Convert a date to Gregorian format.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @return array the Gregorian date
     */
    abstract public function convert_to_gregorian($year, $month, $day, $hour = 0, $minute = 0);

    /**
     * Convert from Gregorian format.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return array the converted date
     */
    abstract public function convert_from_gregorian($year, $month, $day);
}
