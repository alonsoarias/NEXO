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
 * Admin category page - displays admin settings categories.
 *
 * @package    core_admin
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');

$category = required_param('category', PARAM_SAFEDIR);

require_login(0, false);
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/admin/category.php', array('category' => $category));
$PAGE->set_pagetype('admin-category-' . $category);
$PAGE->set_pagelayout('admin');
$PAGE->navigation->clear_cache();
navigation_node::require_admin_tree();

$adminroot = admin_get_root();
$categorynode = $adminroot->locate($category, true);

if (empty($categorynode) or !($categorynode instanceof admin_category)) {
    if (moodle_needs_upgrading()) {
        redirect(new moodle_url('/admin/index.php'));
    } else {
        throw new \moodle_exception('categoryerror', 'admin', "$CFG->wwwroot/$CFG->admin/");
    }
}

if (!$categorynode->check_access()) {
    throw new \moodle_exception('accessdenied', 'admin');
}

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
if ($hassiteconfig && $PAGE->context instanceof \context_system) {
    $PAGE->add_header_action($OUTPUT->render_from_template('core_admin/header_search_input', [
        'action' => new moodle_url('/admin/search.php'),
    ]));
}

$PAGE->set_title(implode(moodle_page::TITLE_SEPARATOR, $categorynode->visiblepath));
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($categorynode->visiblename);

// Get all children of this category.
$children = $categorynode->get_children();
if (!empty($children)) {
    echo html_writer::start_tag('div', array('class' => 'admin-category-list'));
    foreach ($children as $child) {
        if (!$child->check_access()) {
            continue;
        }

        $url = null;
        $name = $child->visiblename;

        if ($child instanceof admin_category) {
            $url = new moodle_url('/admin/category.php', array('category' => $child->name));
        } else if ($child instanceof admin_settingpage) {
            $url = new moodle_url('/admin/settings.php', array('section' => $child->name));
        } else if ($child instanceof admin_externalpage) {
            $url = $child->url;
        }

        if ($url) {
            echo html_writer::start_tag('div', array('class' => 'admin-category-item card mb-2'));
            echo html_writer::start_tag('div', array('class' => 'card-body'));
            echo html_writer::link($url, $name, array('class' => 'admin-category-link'));
            echo html_writer::end_tag('div');
            echo html_writer::end_tag('div');
        }
    }
    echo html_writer::end_tag('div');
} else {
    echo $OUTPUT->notification(get_string('nocontent', 'admin'), 'info');
}

echo $OUTPUT->footer();
