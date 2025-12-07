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
 * NEXO frontpage.
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

// Redirect logged-in users to homepage if required.
$redirect = optional_param('redirect', 1, PARAM_BOOL);

$urlparams = array();
if (!empty($CFG->defaulthomepage) &&
        ($CFG->defaulthomepage == HOMEPAGE_MY || $CFG->defaulthomepage == HOMEPAGE_MYCOURSES) &&
        $redirect === 0
) {
    $urlparams['redirect'] = 0;
}
$PAGE->set_url('/', $urlparams);
$PAGE->set_pagelayout('frontpage');
$PAGE->add_body_class('limitedwidth');

// Prevent caching of this page to stop confusion when changing page after making AJAX changes.
$PAGE->set_cacheable(false);

// Require login if forcelogin is enabled.
if (!empty($CFG->forcelogin)) {
    require_login();
}

$hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', context_system::instance());

// If the site is currently under maintenance, then print a message.
if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
    print_maintenance_message();
}

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());

if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
}

// If site registration needs updating, redirect.
\core\hub\registration::registration_reminder('/index.php');

$homepage = get_home_page();
if ($homepage != HOMEPAGE_SITE) {
    if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
        set_user_preference('user_home_page_preference', HOMEPAGE_SITE);
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && $redirect === 1) {
        redirect($CFG->wwwroot .'/my/');
    } else if ($homepage == HOMEPAGE_URL) {
        redirect(get_default_home_page_url());
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_USER)) {
        $frontpagenode = $PAGE->settingsnav->find('frontpage', null);
        if ($frontpagenode) {
            $frontpagenode->add(
                get_string('makethismyhome'),
                new moodle_url('/', array('setdefaulthome' => true)),
                navigation_node::TYPE_SETTING);
        } else {
            $frontpagenode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
            $frontpagenode->force_open();
            $frontpagenode->add(get_string('makethismyhome'),
                new moodle_url('/', array('setdefaulthome' => true)),
                navigation_node::TYPE_SETTING);
        }
    }
}

$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$PAGE->set_title(get_string('home'));
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

// Print custom info if configured.
if (!empty($CFG->customfrontpageinclude)) {
    include($CFG->customfrontpageinclude);
}

echo $OUTPUT->footer();
