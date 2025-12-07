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
 * Stub file for completion library.
 * Course completion functionality has been removed from this installation.
 *
 * @package core_completion
 * @category completion
 * @copyright 1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Completion constants - kept for compatibility.
define('COMPLETION_ENABLED', 1);
define('COMPLETION_DISABLED', 0);
define('COMPLETION_TRACKING_NONE', 0);
define('COMPLETION_TRACKING_MANUAL', 1);
define('COMPLETION_TRACKING_AUTOMATIC', 2);
define('COMPLETION_INCOMPLETE', 0);
define('COMPLETION_COMPLETE', 1);
define('COMPLETION_COMPLETE_PASS', 2);
define('COMPLETION_COMPLETE_FAIL', 3);
define('COMPLETION_COMPLETE_FAIL_HIDDEN', 4);
define('COMPLETION_UNKNOWN', -1);
define('COMPLETION_GRADECHANGE', -2);
define('COMPLETION_VIEW_REQUIRED', 1);
define('COMPLETION_VIEW_NOT_REQUIRED', 0);
define('COMPLETION_VIEWED', 1);
define('COMPLETION_NOT_VIEWED', 0);
define('COMPLETION_OR', false);
define('COMPLETION_AND', true);
define('COMPLETION_AGGREGATION_ALL', 1);
define('COMPLETION_AGGREGATION_ANY', 2);
define('COMPLETION_SHOW_CONDITIONS', 1);
define('COMPLETION_HIDE_CONDITIONS', 0);

/**
 * Stub function - completion is disabled.
 *
 * @param int $userid User's id
 * @param mixed $course Course object or Course ID
 * @return boolean Always returns false
 */
function completion_can_view_data($userid, $course = null) {
    return false;
}

/**
 * Stub class for completion_info.
 * Course completion functionality has been removed.
 */
class completion_info {
    private $course;
    public $course_id;

    public function __construct($course) {
        $this->course = $course;
        $this->course_id = $course->id ?? 0;
    }

    public static function is_enabled_for_site() {
        return false;
    }

    public function is_enabled($cm = null) {
        return COMPLETION_DISABLED;
    }

    public function get_data($cm, $wholecourse = false, $userid = 0, $unused = null) {
        return (object)[
            'id' => 0,
            'coursemoduleid' => 0,
            'userid' => $userid,
            'completionstate' => 0,
            'viewed' => 0,
            'overrideby' => null,
            'timemodified' => 0,
        ];
    }

    public function has_criteria() {
        return false;
    }

    public function get_criteria($criteriatype = null) {
        return [];
    }

    public function has_activities() {
        return false;
    }

    public function get_activities() {
        return [];
    }

    public function is_course_complete($user_id) {
        return false;
    }

    public function update_state($cm, $possibleresult = COMPLETION_UNKNOWN, $userid = 0, $override = false, $isbulkupdate = false) {
        // No-op.
    }

    public function set_module_viewed($cm, $userid = 0) {
        // No-op.
    }

    public static function get_aggregation_methods() {
        return [
            COMPLETION_AGGREGATION_ALL => 'All',
            COMPLETION_AGGREGATION_ANY => 'Any',
        ];
    }
}

/**
 * Stub function for completion aggregation.
 */
function completion_cron_aggregate($method, $data, &$state) {
    // No-op.
}

/**
 * Stub function for aggregating completions.
 */
function aggregate_completions(int $coursecompletionid, bool $mtraceprogress = false) {
    // No-op.
}
