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
 * Stub file for grade library.
 * Gradebook functionality has been removed from this installation.
 *
 * @package   core_grades
 * @copyright 1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Grade type constants.
define('GRADE_TYPE_NONE', 0);
define('GRADE_TYPE_VALUE', 1);
define('GRADE_TYPE_SCALE', 2);
define('GRADE_TYPE_TEXT', 3);

// Grade update return codes.
define('GRADE_UPDATE_OK', 0);
define('GRADE_UPDATE_FAILED', 1);
define('GRADE_UPDATE_MULTIPLE', 2);
define('GRADE_UPDATE_ITEM_LOCKED', 4);

// Aggregation constants.
define('GRADE_AGGREGATE_MEAN', 0);
define('GRADE_AGGREGATE_WEIGHTED_MEAN', 10);
define('GRADE_AGGREGATE_WEIGHTED_MEAN2', 11);
define('GRADE_AGGREGATE_EXTRACREDIT_MEAN', 12);
define('GRADE_AGGREGATE_MEDIAN', 2);
define('GRADE_AGGREGATE_MIN', 4);
define('GRADE_AGGREGATE_MAX', 6);
define('GRADE_AGGREGATE_MODE', 8);
define('GRADE_AGGREGATE_SUM', 13);

// Display type constants.
define('GRADE_DISPLAY_TYPE_DEFAULT', 0);
define('GRADE_DISPLAY_TYPE_REAL', 1);
define('GRADE_DISPLAY_TYPE_PERCENTAGE', 2);
define('GRADE_DISPLAY_TYPE_LETTER', 3);
define('GRADE_DISPLAY_TYPE_REAL_PERCENTAGE', 12);
define('GRADE_DISPLAY_TYPE_REAL_LETTER', 13);
define('GRADE_DISPLAY_TYPE_LETTER_REAL', 31);
define('GRADE_DISPLAY_TYPE_LETTER_PERCENTAGE', 32);
define('GRADE_DISPLAY_TYPE_PERCENTAGE_LETTER', 23);
define('GRADE_DISPLAY_TYPE_PERCENTAGE_REAL', 21);

// History action constants.
define('GRADE_HISTORY_INSERT', 1);
define('GRADE_HISTORY_UPDATE', 2);
define('GRADE_HISTORY_DELETE', 3);

// Grade file areas.
define('GRADE_FILE_COMPONENT', 'grade');
define('GRADE_FEEDBACK_FILEAREA', 'feedback');

/**
 * Stub - Submit new or update grade.
 * Gradebook functionality removed.
 *
 * @return int Always returns GRADE_UPDATE_OK
 */
function grade_update($source, $courseid, $itemtype, $itemmodule, $iteminstance, $itemnumber, $grades = null,
        $itemdetails = null, $isbulkupdate = false) {
    return GRADE_UPDATE_OK;
}

/**
 * Stub - Check if item is gradable.
 *
 * @return bool Always returns false
 */
function is_gradable(int $courseid, string $itemtype, string $itemmodule, int $iteminstance): bool {
    return false;
}

/**
 * Stub - Update outcomes.
 *
 * @return bool Always returns true
 */
function grade_update_outcomes($source, $courseid, $itemtype, $itemmodule, $iteminstance, $userid, $data) {
    return true;
}

/**
 * Stub - Check if course needs regrading.
 *
 * @return bool Always returns false
 */
function grade_needs_regrade_final_grades($courseid) {
    return false;
}

/**
 * Stub - Check if regrade needs progress bar.
 *
 * @return bool Always returns false
 */
function grade_needs_regrade_progress_bar($courseid) {
    return false;
}

/**
 * Stub - Regrade if required.
 *
 * @return bool Always returns false
 */
function grade_regrade_final_grades_if_required($course, ?callable $callback = null) {
    return false;
}

/**
 * Stub - Get grades.
 *
 * @return stdClass Empty grade info
 */
function grade_get_grades($courseid, $itemtype, $itemmodule, $iteminstance, $userid_or_ids = null) {
    $return = new stdClass();
    $return->items = [];
    $return->outcomes = [];
    $return->errors = [];
    return $return;
}

/**
 * Stub - Get grade setting.
 *
 * @return mixed Default value or null
 */
function grade_get_setting($courseid, $name, $default = null, $resetcache = false) {
    return $default;
}

/**
 * Stub - Get all grade settings.
 *
 * @return stdClass Empty settings object
 */
function grade_get_settings($courseid) {
    $settings = new stdClass();
    $settings->id = $courseid;
    return $settings;
}

/**
 * Stub - Set grade setting.
 */
function grade_set_setting($courseid, $name, $value) {
    // No-op.
}

/**
 * Stub - Format grade value.
 *
 * @return string Empty string or dash
 */
function grade_format_gradevalue(?float $value, &$grade_item, $localized = true, $displaytype = null, $decimals = null) {
    if (is_null($value)) {
        return '-';
    }
    return (string) $value;
}

/**
 * Stub - Get grade letters.
 *
 * @return array Default grade letters
 */
function grade_get_letters($context = null) {
    return ['93' => 'A', '90' => 'A-', '87' => 'B+', '83' => 'B', '80' => 'B-', '77' => 'C+', '73' => 'C', '70' => 'C-', '67' => 'D+', '60' => 'D', '0' => 'F'];
}

/**
 * Stub - Verify idnumber.
 *
 * @return bool Always returns true
 */
function grade_verify_idnumber($idnumber, $courseid, $grade_item = null, $cm = null) {
    return true;
}

/**
 * Stub - Force full regrading.
 */
function grade_force_full_regrading($courseid) {
    // No-op.
}

/**
 * Stub - Force site regrading.
 */
function grade_force_site_regrading() {
    // No-op.
}

/**
 * Stub - Recover history grades.
 *
 * @return bool Always returns false
 */
function grade_recover_history_grades($userid, $courseid) {
    return false;
}

/**
 * Stub - Regrade final grades.
 *
 * @return bool Always returns true
 */
function grade_regrade_final_grades($courseid, $userid = null, $updated_item = null, $progress = null, bool $async = false) {
    return true;
}

/**
 * Stub - Grab course grades.
 */
function grade_grab_course_grades($courseid, $modname = null, $userid = 0) {
    // No-op.
}

/**
 * Stub - Update mod grades.
 *
 * @return bool Always returns true
 */
function grade_update_mod_grades($modinstance, $userid = 0) {
    return true;
}

/**
 * Stub - Remove grade letters.
 */
function remove_grade_letters($context, $showfeedback) {
    // No-op.
}

/**
 * Stub - Remove course grades.
 */
function remove_course_grades($courseid, $showfeedback) {
    // No-op.
}

/**
 * Stub - Grade course category delete.
 */
function grade_course_category_delete($categoryid, $newparentid, $showfeedback) {
    // No-op.
}

/**
 * Stub - Grade uninstalled module.
 */
function grade_uninstalled_module($modname) {
    // No-op.
}

/**
 * Stub - Grade user delete.
 */
function grade_user_delete($userid) {
    // No-op.
}

/**
 * Stub - Grade user unenrol.
 */
function grade_user_unenrol($courseid, $userid) {
    // No-op.
}

/**
 * Stub - Grade course reset.
 *
 * @return bool Always returns true
 */
function grade_course_reset($courseid) {
    return true;
}

/**
 * Convert a number to 5 decimal point float.
 *
 * @param float|null $number The number to convert
 * @return float|null
 */
function grade_floatval(?float $number) {
    if (is_null($number)) {
        return null;
    }
    return round($number, 5);
}

/**
 * Compare two float numbers safely.
 *
 * @param float|null $f1 Float one
 * @param float|null $f2 Float two
 * @return bool True if different
 */
function grade_floats_different(?float $f1, ?float $f2): bool {
    return (grade_floatval($f1) !== grade_floatval($f2));
}

/**
 * Compare two float numbers for equality.
 *
 * @param float|null $f1 Float one
 * @param float|null $f2 Float two
 * @return bool True if equal
 */
function grade_floats_equal(?float $f1, ?float $f2): bool {
    return (grade_floatval($f1) === grade_floatval($f2));
}

/**
 * Stub - Get grade date for user.
 *
 * @return int|null
 */
function grade_get_date_for_user_grade(\stdClass $grade, \stdClass $user): ?int {
    if ($grade->usermodified == $user->id || empty($grade->datesubmitted)) {
        return $grade->dategraded ?? null;
    }
    return $grade->datesubmitted ?? null;
}

/**
 * Stub - Get categories menu.
 *
 * @return array Empty array
 */
function grade_get_categories_menu($courseid, $includenew = false) {
    return [];
}
