<?php
// Stub class - course functionality removed.
namespace core_course\hook;

defined('MOODLE_INTERNAL') || die();

class before_course_viewed implements \core\hook\described_hook {
    private $course;

    public function __construct($course) {
        $this->course = $course;
    }

    public function get_course() {
        return $this->course;
    }

    public static function get_hook_description(): string {
        return 'Hook dispatched before a course is viewed';
    }

    public static function get_hook_tags(): array {
        return [];
    }
}
