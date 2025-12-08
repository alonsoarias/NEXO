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
 * Gregorian calendar type implementation.
 *
 * @package    core_calendar
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_calendar;

defined('MOODLE_INTERNAL') || die();

/**
 * Gregorian calendar type implementation.
 *
 * @package    core_calendar
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gregorian_calendar extends type_base {

    /**
     * Returns the name of the calendar.
     *
     * @return string the calendar name
     */
    public function get_name() {
        return 'gregorian';
    }

    /**
     * Returns a list of all the possible days for all months.
     *
     * @return array the days
     */
    public function get_days() {
        $days = [];
        for ($i = 1; $i <= 31; $i++) {
            $days[$i] = $i;
        }
        return $days;
    }

    /**
     * Returns a list of all the months.
     *
     * @return array the months
     */
    public function get_months() {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 15, 2000), '%B');
        }
        return $months;
    }

    /**
     * Returns the minimum year for the calendar.
     *
     * @return int The minimum year
     */
    public function get_min_year() {
        return 1900;
    }

    /**
     * Returns the maximum year for the calendar.
     *
     * @return int The maximum year
     */
    public function get_max_year() {
        return 2050;
    }

    /**
     * Returns a list of all the years.
     *
     * @param int|null $minyear
     * @param int|null $maxyear
     * @return array the years
     */
    public function get_years($minyear = null, $maxyear = null) {
        if ($minyear === null) {
            $minyear = $this->get_min_year();
        }
        if ($maxyear === null) {
            $maxyear = $this->get_max_year();
        }

        $years = [];
        for ($i = $minyear; $i <= $maxyear; $i++) {
            $years[$i] = $i;
        }
        return $years;
    }

    /**
     * Returns a multidimensional array with information for day, month, year
     * and the order they are displayed when selecting a date.
     *
     * @param int $minyear
     * @param int $maxyear
     * @return array the date order
     */
    public function get_date_order($minyear = null, $maxyear = null) {
        $dateorder = [
            'day' => $this->get_days(),
            'month' => $this->get_months(),
            'year' => $this->get_years($minyear, $maxyear)
        ];
        return $dateorder;
    }

    /**
     * Returns the number of days in a week.
     *
     * @return int the number of days in a week
     */
    public function get_num_weekdays() {
        return 7;
    }

    /**
     * Returns an indexed list of all the names of the weekdays.
     *
     * @return array the weekdays
     */
    public function get_weekdays() {
        return [
            0 => get_string('sunday', 'calendar'),
            1 => get_string('monday', 'calendar'),
            2 => get_string('tuesday', 'calendar'),
            3 => get_string('wednesday', 'calendar'),
            4 => get_string('thursday', 'calendar'),
            5 => get_string('friday', 'calendar'),
            6 => get_string('saturday', 'calendar')
        ];
    }

    /**
     * Returns the index of the starting week day.
     *
     * @return int the starting week day index
     */
    public function get_starting_weekday() {
        global $CFG;

        if (isset($CFG->calendar_startwday)) {
            return (int) $CFG->calendar_startwday;
        }
        return 0; // Sunday
    }

    /**
     * Returns the number of days in a given month.
     *
     * @param int $year
     * @param int $month
     * @return int the number of days in the month
     */
    public function get_num_days_in_month($year, $month) {
        return (int) date('t', mktime(0, 0, 0, $month, 1, $year));
    }

    /**
     * Get the day of the week for the given date.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return int the day of the week (0 for Sunday, 6 for Saturday)
     */
    public function get_weekday($year, $month, $day) {
        return (int) date('w', mktime(12, 0, 0, $month, $day, $year));
    }

    /**
     * Converts the provided timestamp to an array of date info.
     *
     * @param int $timestamp the timestamp
     * @param int|float|string $timezone
     * @return array the date info
     */
    public function timestamp_to_date_array($timestamp, $timezone = 99) {
        $tz = \core_date::get_user_timezone($timezone);
        $date = new \DateTime('@' . $timestamp);
        $date->setTimezone(new \DateTimeZone($tz));

        return [
            'seconds' => (int) $date->format('s'),
            'minutes' => (int) $date->format('i'),
            'hours' => (int) $date->format('G'),
            'mday' => (int) $date->format('j'),
            'wday' => (int) $date->format('w'),
            'mon' => (int) $date->format('n'),
            'year' => (int) $date->format('Y'),
            'yday' => (int) $date->format('z'),
            'weekday' => $date->format('l'),
            'month' => $date->format('F'),
            0 => $timestamp
        ];
    }

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
    public function timestamp_to_date_string($timestamp, $format = '', $timezone = 99, $fixday = true, $fixhour = true) {
        global $CFG;

        if (empty($format)) {
            $format = get_string('strftimedaydatetime', 'langconfig');
        }

        $tz = \core_date::get_user_timezone($timezone);

        $date = new \DateTime('@' . $timestamp);
        $date->setTimezone(new \DateTimeZone($tz));

        // Convert strftime format to date format
        $datestring = strftime_compat($format, $timestamp, $tz);

        // Fix the day formatting
        if ($fixday) {
            $dayofmonth = $date->format('j');
            $datestring = str_replace(sprintf('%02d', $dayofmonth), ltrim(sprintf('%02d', $dayofmonth), '0'), $datestring);
        }

        // Fix the hour formatting
        if ($fixhour) {
            $hourofday = $date->format('g');
            $datestring = str_replace(sprintf('%02d', $hourofday), ltrim(sprintf('%02d', $hourofday), '0'), $datestring);
        }

        return $datestring;
    }

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
    public function convert_to_gregorian($year, $month, $day, $hour = 0, $minute = 0) {
        return [
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'hour' => $hour,
            'minute' => $minute
        ];
    }

    /**
     * Convert from Gregorian format.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return array the converted date
     */
    public function convert_from_gregorian($year, $month, $day) {
        return [
            'year' => $year,
            'month' => $month,
            'day' => $day
        ];
    }
}

/**
 * Compatibility function for strftime which is deprecated in PHP 8.1+
 *
 * @param string $format The format string
 * @param int $timestamp The timestamp
 * @param string $timezone The timezone
 * @return string The formatted date string
 */
function strftime_compat($format, $timestamp, $timezone) {
    $date = new \DateTime('@' . $timestamp);
    $date->setTimezone(new \DateTimeZone($timezone));

    // Map strftime format codes to date format codes
    $mapping = [
        '%a' => 'D',    // Abbreviated weekday name
        '%A' => 'l',    // Full weekday name
        '%d' => 'd',    // Day of the month, 2 digits with leading zeros
        '%e' => 'j',    // Day of the month without leading zeros
        '%j' => 'z',    // Day of the year
        '%u' => 'N',    // ISO-8601 day of the week
        '%w' => 'w',    // Day of the week
        '%U' => 'W',    // Week number
        '%V' => 'W',    // ISO-8601 week number
        '%W' => 'W',    // Week number
        '%b' => 'M',    // Abbreviated month name
        '%B' => 'F',    // Full month name
        '%h' => 'M',    // Abbreviated month name (same as %b)
        '%m' => 'm',    // Month number with leading zeros
        '%C' => '',     // Century (will need special handling)
        '%g' => 'y',    // Two-digit year
        '%G' => 'Y',    // Four-digit year
        '%y' => 'y',    // Two-digit year
        '%Y' => 'Y',    // Four-digit year
        '%H' => 'H',    // Hour (24-hour format) with leading zeros
        '%k' => 'G',    // Hour (24-hour format) without leading zeros
        '%I' => 'h',    // Hour (12-hour format) with leading zeros
        '%l' => 'g',    // Hour (12-hour format) without leading zeros
        '%M' => 'i',    // Minutes with leading zeros
        '%p' => 'A',    // AM/PM
        '%P' => 'a',    // am/pm
        '%r' => 'h:i:s A', // 12-hour time
        '%R' => 'H:i',  // 24-hour time without seconds
        '%S' => 's',    // Seconds with leading zeros
        '%T' => 'H:i:s', // 24-hour time with seconds
        '%X' => 'H:i:s', // Time
        '%z' => 'O',    // Timezone offset
        '%Z' => 'T',    // Timezone abbreviation
        '%c' => 'D M j H:i:s Y', // Preferred date and time
        '%D' => 'm/d/y', // Date
        '%F' => 'Y-m-d', // Date (ISO 8601)
        '%s' => 'U',    // Unix timestamp
        '%x' => 'd/m/Y', // Preferred date representation
        '%n' => "\n",   // Newline
        '%t' => "\t",   // Tab
        '%%' => '%',    // Literal %
    ];

    $result = $format;
    foreach ($mapping as $strftime => $dateformat) {
        if (strpos($result, $strftime) !== false) {
            if ($dateformat === '') {
                // Special handling for century
                if ($strftime === '%C') {
                    $century = (int) floor($date->format('Y') / 100);
                    $result = str_replace($strftime, $century, $result);
                }
            } else {
                $result = str_replace($strftime, $date->format($dateformat), $result);
            }
        }
    }

    return $result;
}
