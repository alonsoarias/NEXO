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
 * This file is executed right after the install.xml
 *
 * @package   core_install
 * @category  upgrade
 * @copyright 2009 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Main post-install tasks to be executed after the BD schema is available
 *
 * This is a stripped-down version for NEXO without courses.
 */
function xmldb_main_install() {
    global $CFG, $DB, $OUTPUT;

    // Make sure system context exists
    $syscontext = context_system::instance(0, MUST_EXIST, false);
    if ($syscontext->id != SYSCONTEXTID) {
        throw new moodle_exception('generalexceptionmessage', 'error', '', 'Unexpected new system context id!');
    }

    $defaults = array(
        'rolesactive'           => '0', // marks fully set up system
        'auth'                  => 'manual',
        'theme'                 => theme_config::DEFAULT_THEME,
        'filter_multilang_converted' => 1,
        'siteidentifier'        => random_string(32).get_host_from_url($CFG->wwwroot),
        'sessiontimeout'        => 8 * 60 * 60, // Must be present during roles installation.
        'stringfilters'         => '',
        'filterall'             => 0,
        'texteditors'           => 'tiny,textarea',
        'antiviruses'           => '',
        'media_plugins_sortorder' => 'videojs,youtube',
    );
    foreach($defaults as $key => $value) {
        set_config($key, $value);
    }

    // Create guest record
    if ($DB->record_exists('user', array())) {
        throw new moodle_exception('generalexceptionmessage', 'error', '', 'Can not create default users, users already exist.');
    }
    $guest = new stdClass();
    $guest->auth        = 'manual';
    $guest->username    = 'guest';
    $guest->password    = hash_internal_user_password('guest');
    $guest->firstname   = get_string('guestuser');
    $guest->lastname    = ' ';
    $guest->email       = 'root@localhost';
    $guest->description = get_string('guestuserinfo');
    $guest->confirmed   = 1;
    $guest->lang        = $CFG->lang;
    $guest->timemodified= time();
    $guest->id = $DB->insert_record('user', $guest);
    if ($guest->id != 1) {
        echo $OUTPUT->notification('Unexpected id generated for the Guest account. Your database configuration or clustering setup may not be fully supported', 'notifyproblem');
    }
    set_config('siteguest', $guest->id);
    context_user::instance($guest->id);

    // Now create admin user
    $admin = new stdClass();
    $admin->auth         = 'manual';
    $admin->firstname    = get_string('admin');
    $admin->lastname     = get_string('user');
    $admin->username     = 'admin';
    $admin->password     = 'adminsetuppending';
    $admin->email        = '';
    $admin->confirmed    = 1;
    $admin->lang         = $CFG->lang;
    $admin->maildisplay  = 1;
    $admin->timemodified = time();
    $admin->lastip       = CLI_SCRIPT ? '0.0.0.0' : getremoteaddr();
    $admin->id = $DB->insert_record('user', $admin);

    if ($admin->id != 2) {
        echo $OUTPUT->notification('Unexpected id generated for the Admin account. Your database configuration or clustering setup may not be fully supported', 'notifyproblem');
    }
    if ($admin->id != ($guest->id + 1)) {
        echo $OUTPUT->notification('Nonconsecutive id generated for the Admin account. Your database configuration or clustering setup may not be fully supported.', 'notifyproblem');
    }

    set_config('siteadmins', $admin->id);
    context_user::instance($admin->id);

    // Install the roles system - only user-related roles
    $managerrole = create_role('', 'manager', '', 'manager');
    $guestrole   = create_role('', 'guest', '', 'guest');
    $userrole    = create_role('', 'user', '', 'user');

    // Now is the correct moment to install capabilities
    update_capabilities('moodle');

    // Default allow role matrices
    foreach ($DB->get_records('role') as $role) {
        foreach (array('assign', 'override', 'switch', 'view') as $type) {
            $function = "core_role_set_{$type}_allowed";
            $allows = get_default_role_archetype_allows($type, $role->archetype);
            foreach ($allows as $allowid) {
                $function($role->id, $allowid);
            }
        }
    }

    // Set up the context levels where you can assign each role
    set_role_contextlevels($managerrole, get_default_contextlevels('manager'));
    set_role_contextlevels($guestrole,   get_default_contextlevels('guest'));
    set_role_contextlevels($userrole,    get_default_contextlevels('user'));

    // Init theme, JS and template revisions
    set_config('themerev', time());
    set_config('jsrev', time());
    set_config('templaterev', time());

    // GD is now required
    set_config('gdversion', 2);

    // Install licenses
    require_once($CFG->libdir . '/licenselib.php');
    license_manager::install_licenses();

    // Init profile pages defaults
    if ($DB->record_exists('my_pages', array())) {
        throw new moodle_exception('generalexceptionmessage', 'error', '', 'Can not create default profile pages, records already exist.');
    }
    $mypage = new stdClass();
    $mypage->userid = NULL;
    $mypage->name = '__default';
    $mypage->private = 0;
    $mypage->sortorder  = 0;
    $DB->insert_record('my_pages', $mypage);
    $mypage->private = 1;
    $DB->insert_record('my_pages', $mypage);

    // Create default core site admin presets
    require_once($CFG->dirroot . '/admin/presets/classes/helper.php');
    \core_adminpresets\helper::create_default_presets();
}
