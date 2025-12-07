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
 * Hook listener callbacks.
 *
 * @package    core
 * @copyright  2023 Safat Shahin <safat.shahin@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$callbacks = [
    // Hooks related to deleted components (group, course, courseformat, completion, enrol) have been removed.
    [
        'hook' => \core_user\hook\before_user_updated::class,
        'callback' => \core_communication\hook_listener::class . '::update_user_room_memberships',
    ],
    [
        'hook' => \core_user\hook\before_user_deleted::class,
        'callback' => \core_communication\hook_listener::class . '::delete_user_room_memberships',
    ],
    [
        'hook' => \core\hook\access\after_role_assigned::class,
        'callback' => \core_communication\hook_listener::class . '::update_user_membership_for_role_changes',
    ],
    [
        'hook' => \core\hook\access\after_role_unassigned::class,
        'callback' => \core_communication\hook_listener::class . '::update_user_membership_for_role_changes',
    ],
    [
        'hook' => \core\hook\output\before_standard_footer_html_generation::class,
        'callback' => \core_userfeedback::class . '::before_standard_footer_html_generation',
    ],
    [
        'hook' => \core\hook\output\after_standard_main_region_html_generation::class,
        'callback' => \core_message\hook_callbacks::class . '::add_messaging_widget',
        'priority' => 0,
    ],
    [
        'hook' => \core\hook\task\after_failed_task_max_delay::class,
        'callback' => core\task\failed_task_callbacks::class . '::send_failed_task_max_delay_message',
    ],
    [
        'hook' => \core\hook\di_configuration::class,
        'callback' => [\core\router\hook_callbacks::class, 'provide_di_configuration'],
    ],
    [
        'hook' => \core_files\hook\before_file_created::class,
        'callback' => [\core_files\redactor\hook_listener::class, 'file_redaction_handler'],
    ],
];
