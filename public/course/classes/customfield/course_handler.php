<?php
// Stub class - course functionality removed.
namespace core_course\customfield;

defined('MOODLE_INTERNAL') || die();

class course_handler {
    public static function create($itemid = 0) {
        return new self();
    }

    public static function reset_caches(): void {
        // No-op.
    }
}
