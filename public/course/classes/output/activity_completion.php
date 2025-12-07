<?php
// Stub class - course functionality removed.
namespace core_course\output;

defined('MOODLE_INTERNAL') || die();

class activity_completion implements \renderable, \templatable {
    public function __construct($cm = null, $completiondetails = []) {
    }

    public function export_for_template(\renderer_base $output) {
        return new \stdClass();
    }
}
