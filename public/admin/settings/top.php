<?php

// This is the first file read by the lib/adminlib.php script
// We use it to create the categories in correct order,
// since they need to exist *before* settingpages and externalpages
// are added to them.

$systemcontext = context_system::instance();
$hassiteconfig = has_capability('moodle/site:config', $systemcontext);

$ADMIN->add('root', new admin_externalpage('adminnotifications', new lang_string('notifications'), "$CFG->wwwroot/$CFG->admin/index.php"));

// General site settings category.
$ADMIN->add('root', new admin_category('general', new lang_string('general', 'admin')));

// Front page settings - site name configuration.
if ($hassiteconfig) {
    $temp = new admin_settingpage('frontpagesettings', new lang_string('frontpagesettings', 'admin'));
    $temp->add(new admin_setting_sitesettext('fullname', new lang_string('fullsitename'), '', null, PARAM_TEXT, 50));
    $temp->add(new admin_setting_sitesettext('shortname', new lang_string('shortsitename'), '', null, PARAM_TEXT, 50));
    $temp->add(new admin_setting_special_frontpagedesc());
    $ADMIN->add('general', $temp);
}

// Hidden upgrade script.
$ADMIN->add('root', new admin_externalpage('upgradesettings', new lang_string('upgradesettings', 'admin'), "$CFG->wwwroot/$CFG->admin/upgradesettings.php", 'moodle/site:config', true));

// Users.
$ADMIN->add('root', new admin_category('users', new lang_string('users', 'admin')));

// Plugins.
$ADMIN->add('root', new admin_category('modules', new lang_string('plugins', 'admin')));

// AI as subcategory of Plugins.
$ADMIN->add('modules', new admin_category('ai', new lang_string('ai', 'ai')));

// Appearance (includes language and themes).
$ADMIN->add('root', new admin_category('appearance', new lang_string('appearance', 'admin')));

// Server (includes location, email, tasks, messaging, etc.).
$ADMIN->add('root', new admin_category('server', new lang_string('server', 'admin')));

// Advanced features under Server.
if ($hassiteconfig) {
    $optionalsubsystems = new admin_settingpage('optionalsubsystems', new lang_string('advancedfeatures', 'admin'));
    $ADMIN->add('server', $optionalsubsystems);
}

// Messaging as subcategory of Server.
$ADMIN->add('server', new admin_category('messaging', new lang_string('messagingcategory', 'admin')));

// Security.
$ADMIN->add('root', new admin_category('security', new lang_string('security', 'admin')));

// Reports.
$ADMIN->add('root', new admin_category('reports', new lang_string('reports')));

// Development.
$ADMIN->add('root', new admin_category('development', new lang_string('development', 'admin')));

// Hidden unsupported category.
$ADMIN->add('root', new admin_category('unsupported', new lang_string('unsupported', 'admin'), true));

// Hidden search script.
$ADMIN->add('root', new admin_externalpage('search', new lang_string('search', 'admin'), "$CFG->wwwroot/$CFG->admin/search.php", 'moodle/site:configview', true));
