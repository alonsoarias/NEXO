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
 * NEXO frontpage - simplified without courses.
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!file_exists('./config.php')) {
    header('Location: install.php');
    die;
}

require_once('config.php');
require_once($CFG->libdir .'/filelib.php');

redirect_if_major_upgrade_required();

$PAGE->set_url('/');
$PAGE->set_pagelayout('frontpage');
$PAGE->add_body_class('limitedwidth');
$PAGE->set_cacheable(false);

$hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', context_system::instance());

// If the site is currently under maintenance, then print a message.
if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
    print_maintenance_message();
}

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());

if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
}

// NEXO: Dashboard (/my/) has been removed, all users see the homepage.

$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$PAGE->set_title(get_string('home'));
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

// Front page content.
echo html_writer::start_div('frontpage-container');

// Site summary if available.
if (!empty($SITE->summary)) {
    echo html_writer::div(format_text($SITE->summary, FORMAT_HTML), 'site-summary mb-4');
}

// Login prompt for non-logged users.
if (!isloggedin()) {
    echo html_writer::start_div('login-prompt text-center my-5');
    echo html_writer::tag('h2', get_string('loginto', 'moodle', $SITE->fullname), ['class' => 'mb-4']);
    echo html_writer::start_div('login-buttons');
    echo $OUTPUT->single_button(
        new moodle_url('/login/index.php'),
        get_string('login'),
        'get',
        ['class' => 'btn-lg']
    );
    echo html_writer::end_div();
    echo html_writer::end_div();
}

echo html_writer::end_div(); // frontpage-container.

echo $OUTPUT->footer();
