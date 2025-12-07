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
 * Stub class for core_course_category - course functionality removed.
 *
 * @package    core
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Stub class for course category management.
 * Course functionality has been removed from this installation.
 */
class core_course_category {

    /** @var int Category id */
    public $id;
    /** @var string Category name */
    public $name;
    /** @var int Parent category id */
    public $parent;
    /** @var int Sort order */
    public $sortorder;
    /** @var int Visible */
    public $visible = 1;
    /** @var string Path */
    public $path;
    /** @var int Depth */
    public $depth;

    /**
     * Constructor.
     */
    protected function __construct() {
    }

    /**
     * Check if this is a simple site (single category).
     * @return bool Always returns true for simplified installation.
     */
    public static function is_simple_site(): bool {
        return true;
    }

    /**
     * Check if user can view the category.
     * @param object|int $category Category object or id
     * @return bool
     */
    public static function can_view_category($category): bool {
        return true;
    }

    /**
     * Check if user can view course info.
     * @param object $course Course object
     * @return bool
     */
    public static function can_view_course_info($course): bool {
        return false;
    }

    /**
     * Get user's top category.
     * @return core_course_category|null
     */
    public static function user_top() {
        return self::get_default();
    }

    /**
     * Called when role assignment changes.
     * @param int $roleid
     * @param context $context
     */
    public static function role_assignment_changed($roleid, $context): void {
        // No-op - course functionality removed.
    }

    /**
     * Called when user enrolment changes.
     * @param int $courseid
     * @param int $userid
     * @param int $status
     */
    public static function user_enrolment_changed($courseid, $userid, $status): void {
        // No-op - course functionality removed.
    }

    /**
     * Make categories list for select elements.
     * @param string $requiredcapability
     * @param int $excludeid
     * @param string $separator
     * @return array
     */
    public static function make_categories_list($requiredcapability = '', $excludeid = 0, $separator = ' / '): array {
        global $DB;
        $categories = [];
        $records = $DB->get_records('course_categories', null, 'sortorder', 'id, name, parent, depth');
        foreach ($records as $record) {
            $categories[$record->id] = $record->name;
        }
        return $categories;
    }

    /**
     * Get category by id.
     * @param int $id Category id
     * @param int $strictness IGNORE_MISSING or MUST_EXIST
     * @return core_course_category|null
     */
    public static function get($id, $strictness = MUST_EXIST) {
        global $DB;
        $record = $DB->get_record('course_categories', ['id' => $id], '*', $strictness);
        if (!$record) {
            return null;
        }
        $category = new self();
        foreach ($record as $key => $value) {
            $category->$key = $value;
        }
        return $category;
    }

    /**
     * Get multiple categories.
     * @param array $ids Category ids
     * @return array
     */
    public static function get_many(array $ids): array {
        $categories = [];
        foreach ($ids as $id) {
            $cat = self::get($id, IGNORE_MISSING);
            if ($cat) {
                $categories[$id] = $cat;
            }
        }
        return $categories;
    }

    /**
     * Get default category.
     * @return core_course_category
     */
    public static function get_default() {
        global $DB;
        $record = $DB->get_record('course_categories', ['parent' => 0], '*', IGNORE_MULTIPLE);
        if (!$record) {
            // Create default category if none exists.
            $record = new stdClass();
            $record->name = 'Miscellaneous';
            $record->parent = 0;
            $record->sortorder = 10000;
            $record->visible = 1;
            $record->depth = 1;
            $record->path = '';
            $record->id = $DB->insert_record('course_categories', $record);
            $record->path = '/' . $record->id;
            $DB->update_record('course_categories', $record);
        }
        $category = new self();
        foreach ($record as $key => $value) {
            $category->$key = $value;
        }
        return $category;
    }

    /**
     * Create a new category.
     * @param stdClass|array $data Category data
     * @return core_course_category
     */
    public static function create($data) {
        global $DB;
        $data = (object)$data;
        if (empty($data->parent)) {
            $data->parent = 0;
        }
        if (empty($data->sortorder)) {
            $data->sortorder = 10000;
        }
        if (!isset($data->visible)) {
            $data->visible = 1;
        }
        $data->depth = 1;
        $data->path = '';
        $data->id = $DB->insert_record('course_categories', $data);
        $data->path = '/' . $data->id;
        $DB->update_record('course_categories', $data);

        $category = new self();
        foreach ($data as $key => $value) {
            $category->$key = $value;
        }
        return $category;
    }

    /**
     * Update category.
     * @param array $data
     */
    public function update($data): void {
        global $DB;
        $data = (array)$data;
        $data['id'] = $this->id;
        $DB->update_record('course_categories', (object)$data);
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Search courses - returns empty as courses are removed.
     * @param array $search
     * @param array $options
     * @return array
     */
    public static function search_courses($search, $options = []): array {
        return ['courses' => [], 'totalcount' => 0];
    }

    /**
     * Get courses - returns empty as courses are removed.
     * @param array $options
     * @return array
     */
    public function get_courses($options = []): array {
        return [];
    }
}
