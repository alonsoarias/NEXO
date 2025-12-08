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
 * NEXO Dashboard page.
 *
 * @package    core
 * @subpackage my
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../config.php');

redirect_if_major_upgrade_required();

// Check if guest user.
if (isguestuser()) {
    redirect(new moodle_url('/'));
}

require_login();

$context = context_user::instance($USER->id);

$PAGE->set_context($context);
$PAGE->set_url('/my/index.php');
$PAGE->set_pagelayout('mydashboard');
$PAGE->add_body_class('limitedwidth');
$PAGE->set_pagetype('my-index');
$PAGE->set_title(get_string('myhome'));
$PAGE->set_heading(fullname($USER));

// Get the user's profile picture.
$userpicture = new user_picture($USER);
$userpicture->size = 100;

echo $OUTPUT->header();

// Dashboard content.
echo html_writer::start_div('dashboard-container');

// Welcome section.
echo html_writer::start_div('welcome-section mb-4');
echo html_writer::tag('h2', get_string('welcome') . ', ' . fullname($USER) . '!', ['class' => 'mb-3']);
echo html_writer::end_div();

// Quick links section.
echo html_writer::start_div('quick-links-section');
echo html_writer::tag('h3', get_string('quicklinks', 'block_admin_bookmarks'), ['class' => 'mb-3']);

$quicklinks = [
    [
        'url' => new moodle_url('/user/profile.php'),
        'title' => get_string('profile'),
        'icon' => 'i/user'
    ],
    [
        'url' => new moodle_url('/user/preferences.php'),
        'title' => get_string('preferences'),
        'icon' => 'i/settings'
    ],
    [
        'url' => new moodle_url('/calendar/view.php'),
        'title' => get_string('calendar', 'calendar'),
        'icon' => 'i/calendar'
    ],
    [
        'url' => new moodle_url('/user/files.php'),
        'title' => get_string('privatefiles'),
        'icon' => 'i/files'
    ],
];

// Add admin link if user is admin.
if (is_siteadmin()) {
    $quicklinks[] = [
        'url' => new moodle_url('/admin/index.php'),
        'title' => get_string('administrationsite'),
        'icon' => 'i/settings'
    ];
}

echo html_writer::start_div('row');
foreach ($quicklinks as $link) {
    echo html_writer::start_div('col-md-3 col-sm-6 mb-3');
    echo html_writer::start_tag('a', [
        'href' => $link['url'],
        'class' => 'card h-100 text-decoration-none'
    ]);
    echo html_writer::start_div('card-body text-center');
    echo $OUTPUT->pix_icon($link['icon'], '', 'moodle', ['class' => 'icon-large mb-2']);
    echo html_writer::tag('h5', $link['title'], ['class' => 'card-title']);
    echo html_writer::end_div();
    echo html_writer::end_tag('a');
    echo html_writer::end_div();
}
echo html_writer::end_div();

echo html_writer::end_div(); // quick-links-section.

echo html_writer::end_div(); // dashboard-container.

echo $OUTPUT->footer();
