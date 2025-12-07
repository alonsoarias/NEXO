<?php
// Stub class - course functionality removed.
namespace core_course\hook;

defined('MOODLE_INTERNAL') || die();

class after_course_created {
    private $course;

    public function __construct($course) {
        $this->course = $course;
    }

    public function get_course() {
        return $this->course;
    }
}
