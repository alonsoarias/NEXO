<?php
// Stub class - course functionality removed.
namespace core_course\hook;

defined('MOODLE_INTERNAL') || die();

class before_course_deleted implements \core\hook\stoppable_hook {
    private $course;
    private bool $stopped = false;

    public function __construct($course) {
        $this->course = $course;
    }

    public function get_course() {
        return $this->course;
    }

    public function isPropagationStopped(): bool {
        return $this->stopped;
    }

    public function stop(): void {
        $this->stopped = true;
    }
}
