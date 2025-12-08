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
 * Load all plugins into the admin tree.
 *
* Please note that is file is always loaded last - it means that you can inject entries into other categories too.
*
* @package    core
* @copyright  2007 Petr Skoda {@link http://skodak.org}
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$ADMIN->add('modules', new admin_category('customfieldsettings', new lang_string('customfields', 'core_customfield')));
$ADMIN->add('modules', new admin_category('blocksettings', new lang_string('blocks')));
$ADMIN->add('modules', new admin_category('authsettings', new lang_string('authentication', 'admin')));
$ADMIN->add('modules', new admin_category('editorsettings', new lang_string('editors', 'editor')));
$ADMIN->add('modules', new admin_category('antivirussettings', new lang_string('antiviruses', 'antivirus')));
$ADMIN->add('modules', new admin_category('mlbackendsettings', new lang_string('mlbackendsettings', 'admin')));
$ADMIN->add('modules', new admin_category('filtersettings', new lang_string('managefilters')));
$ADMIN->add('modules', new admin_category('mediaplayers', new lang_string('type_media_plural', 'plugin')));
$ADMIN->add('modules', new admin_category('fileconverterplugins', new lang_string('type_fileconverter_plural', 'plugin')));
$ADMIN->add('modules', new admin_category('dataformatsettings', new lang_string('dataformats')));
$ADMIN->add('modules', new admin_category('repositorysettings', new lang_string('repositories', 'repository')));
$ADMIN->add('modules', new admin_category('plagiarism', new lang_string('plagiarism', 'plagiarism')));
$ADMIN->add('modules', new admin_category('reportplugins', new lang_string('reports')));
$ADMIN->add('modules', new admin_category('searchplugins', new lang_string('search', 'admin')));
$ADMIN->add('modules', new admin_category('tools', new lang_string('tools', 'admin')));
$ADMIN->add('modules', new admin_category('cache', new lang_string('caching', 'cache')));
$ADMIN->add('cache', new admin_category('cachestores', new lang_string('cachestores', 'cache')));
$ADMIN->add('modules', new admin_category('sms', new lang_string('sms', 'core_sms')));
$ADMIN->add('modules', new admin_category('localplugins', new lang_string('localplugins')));


if ($hassiteconfig) {
    /* @var admin_root $ADMIN */
    $ADMIN->locate('modules')->set_sorting(true);

    $ADMIN->add('modules', new admin_page_pluginsoverview());

    // Custom fields.
    $temp = new admin_settingpage('managecustomfields', new lang_string('managecustomfields', 'core_admin'));
    $temp->add(new admin_setting_managecustomfields());
    $ADMIN->add('customfieldsettings', $temp);
    $plugins = core_plugin_manager::instance()->get_plugins_of_type('customfield');
    core_collator::asort_objects_by_property($plugins, 'displayname');
    foreach ($plugins as $plugin) {
        /** @var \core\plugininfo\customfield $plugin */
        $plugin->load_settings($ADMIN, 'customfieldsettings', $hassiteconfig);
    }

    // blocks
    $ADMIN->add('blocksettings', new admin_page_manageblocks());
    $plugins = core_plugin_manager::instance()->get_plugins_of_type('block');
    core_collator::asort_objects_by_property($plugins, 'displayname');
    foreach ($plugins as $plugin) {
        /** @var \core\plugininfo\block $plugin */
        $plugin->load_settings($ADMIN, 'blocksettings', $hassiteconfig);
    }

    // authentication plugins
    $temp = new admin_settingpage('manageauths', new lang_string('authsettings', 'admin'));
    $temp->add(new admin_setting_manageauths());
    $temp->add(new admin_setting_heading('manageauthscommonheading', new lang_string('commonsettings', 'admin'), ''));
    $temp->add(new admin_setting_special_registerauth());
    $temp->add(new admin_setting_configcheckbox('authloginviaemail', new lang_string('authloginviaemail', 'core_auth'), new lang_string('authloginviaemail_desc', 'core_auth'), 0));
    $temp->add(new admin_setting_configcheckbox('allowaccountssameemail',
                    new lang_string('allowaccountssameemail', 'core_auth'),
                    new lang_string('allowaccountssameemail_desc', 'core_auth'), 0));
    $temp->add(new admin_setting_configcheckbox('authpreventaccountcreation', new lang_string('authpreventaccountcreation', 'admin'), new lang_string('authpreventaccountcreation_help', 'admin'), 0));
    $temp->add(new admin_setting_configcheckbox('loginpageautofocus', new lang_string('loginpageautofocus', 'admin'), new lang_string('loginpageautofocus_help', 'admin'), 0));
    $temp->add(new admin_setting_configselect('guestloginbutton', new lang_string('guestloginbutton', 'auth'),
                                              new lang_string('showguestlogin', 'auth'), '1', array('0'=>new lang_string('hide'), '1'=>new lang_string('show'))));
    $options = array(0 => get_string('no'), 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 10 => 10, 20 => 20, 50 => 50);
    $temp->add(new admin_setting_configselect('limitconcurrentlogins',
        new lang_string('limitconcurrentlogins', 'core_auth'),
        new lang_string('limitconcurrentlogins_desc', 'core_auth'), 0, $options));
    $temp->add(new admin_setting_configtext('alternateloginurl', new lang_string('alternateloginurl', 'auth'),
                                            new lang_string('alternatelogin', 'auth', htmlspecialchars(get_login_url(), ENT_COMPAT)), ''));
    $temp->add(new admin_setting_configcheckbox('showloginform', new lang_string('showloginform', 'core_auth'),
                                                new lang_string('showloginform_desc', 'core_auth'), 1));
    $temp->add(new admin_setting_configtext('forgottenpasswordurl', new lang_string('forgottenpasswordurl', 'auth'),
                                            new lang_string('forgottenpassword', 'auth'), '', PARAM_URL));
    $temp->add(new admin_setting_confightmleditor('auth_instructions', new lang_string('instructions', 'auth'),
                                                new lang_string('authinstructions', 'auth'), ''));
    $setting = new admin_setting_configtext('allowemailaddresses', new lang_string('allowemailaddresses', 'admin'),
        new lang_string('configallowemailaddresses', 'admin'), '', PARAM_NOTAGS);
    $setting->set_force_ltr(true);
    $temp->add($setting);
    $setting = new admin_setting_configtext('denyemailaddresses', new lang_string('denyemailaddresses', 'admin'),
        new lang_string('configdenyemailaddresses', 'admin'), '', PARAM_NOTAGS);
    $setting->set_force_ltr(true);
    $temp->add($setting);
    $temp->add(new admin_setting_configcheckbox('verifychangedemail', new lang_string('verifychangedemail', 'admin'), new lang_string('configverifychangedemail', 'admin'), 1));

    // ReCaptcha.
    $temp->add(new admin_setting_configselect('enableloginrecaptcha',
        new lang_string('auth_loginrecaptcha', 'auth'),
        new lang_string('auth_loginrecaptcha_desc', 'auth'),
        0,
        [
            new lang_string('no'),
            new lang_string('yes'),
        ],
    ));


    // Forgot password ReCaptcha.
    $temp->add(new admin_setting_configcheckbox(
        'enableforgotpasswordrecaptcha',
        new lang_string('auth_forgotpasswordrecaptcha', 'auth'),
        new lang_string('auth_forgotpasswordrecaptcha_desc', 'auth'),
        0,
    ));

    $setting = new admin_setting_configtext('recaptchapublickey', new lang_string('recaptchapublickey', 'admin'), new lang_string('configrecaptchapublickey', 'admin'), '', PARAM_NOTAGS);
    $setting->set_force_ltr(true);
    $temp->add($setting);
    $setting = new admin_setting_configtext('recaptchaprivatekey', new lang_string('recaptchaprivatekey', 'admin'), new lang_string('configrecaptchaprivatekey', 'admin'), '', PARAM_NOTAGS);
    $setting->set_force_ltr(true);
    $temp->add($setting);
    $ADMIN->add('authsettings', $temp);

    // Toggle password visiblity icon.
    $temp->add(new admin_setting_configselect('loginpasswordtoggle',
        new lang_string('auth_loginpasswordtoggle', 'auth'),
        new lang_string('auth_loginpasswordtoggle_desc', 'auth'),
        TOGGLE_SENSITIVE_SMALL_SCREENS_ONLY,
        [
            TOGGLE_SENSITIVE_DISABLED => get_string('disabled', 'admin'),
            TOGGLE_SENSITIVE_ENABLED => get_string('enabled', 'admin'),
            TOGGLE_SENSITIVE_SMALL_SCREENS_ONLY => get_string('smallscreensonly', 'admin'),
        ],
    ));

    $temp = new admin_externalpage('authtestsettings', get_string('testsettings', 'core_auth'), new moodle_url("/auth/test_settings.php"), 'moodle/site:config', true);
    $ADMIN->add('authsettings', $temp);

    $plugins = core_plugin_manager::instance()->get_plugins_of_type('auth');
    core_collator::asort_objects_by_property($plugins, 'displayname');
    foreach ($plugins as $plugin) {
        /** @var \core\plugininfo\auth $plugin */
        $plugin->load_settings($ADMIN, 'authsettings', $hassiteconfig);
    }

/// Editor plugins
    $temp = new admin_settingpage('manageeditors', new lang_string('editorsettings', 'editor'));
    $temp->add(new \core_admin\admin\admin_setting_plugin_manager(
        'editor',
        \core_admin\table\editor_management_table::class,
        'editorsui',
        get_string('editorsettings', 'editor'),
    ));
    $ADMIN->add('editorsettings', $temp);
    $plugins = core_plugin_manager::instance()->get_plugins_of_type('editor');
    core_collator::asort_objects_by_property($plugins, 'displayname');
    foreach ($plugins as $plugin) {
        /** @var \core\plugininfo\editor $plugin */
        $plugin->load_settings($ADMIN, 'editorsettings', $hassiteconfig);
    }

    // Antivirus plugins.
    $temp = new admin_settingpage('manageantiviruses', new lang_string('antivirussettings', 'antivirus'));
    $temp->add(new admin_setting_manageantiviruses());

    // Status check.
    $temp->add(new admin_setting_heading('antivirus/statuschecks', new lang_string('statuschecks'), ''));
    $temp->add(new admin_setting_check('antivirus/checkantivirus', new \core\check\environment\antivirus()));

    // Common settings.
    $temp->add(new admin_setting_heading('antiviruscommonsettings', new lang_string('antiviruscommonsettings', 'antivirus'), ''));

    // Alert email.
    $temp->add(
        new admin_setting_configtext(
            'antivirus/notifyemail',
            new lang_string('notifyemail', 'antivirus'),
            new lang_string('notifyemail_help', 'antivirus'),
            '',
            PARAM_EMAIL
        )
    );

    // Notify level.
    $temp->add(new admin_setting_configselect('antivirus/notifylevel',
        get_string('notifylevel', 'antivirus'), '', core\antivirus\scanner::SCAN_RESULT_ERROR, [
            core\antivirus\scanner::SCAN_RESULT_ERROR => get_string('notifylevelerror', 'antivirus'),
            core\antivirus\scanner::SCAN_RESULT_FOUND => get_string('notifylevelfound', 'antivirus')
        ]),
    );

    // Threshold for check displayed on the /report/status/index.php page.
    $url = new moodle_url('/report/status/index.php');
    $link = html_writer::link($url, get_string('pluginname', 'report_status'));
    $temp->add(
        new admin_setting_configduration(
            'antivirus/threshold',
            new lang_string('threshold', 'antivirus'),
            get_string('threshold_desc', 'antivirus', $link),
            20 * MINSECS
        )
    );

    // Enable quarantine.
    $temp->add(
        new admin_setting_configcheckbox(
            'antivirus/enablequarantine',
            new lang_string('enablequarantine', 'antivirus'),
            new lang_string('enablequarantine_help', 'antivirus',
            \core\antivirus\quarantine::DEFAULT_QUARANTINE_FOLDER),
            0
        )
    );

    // Quarantine time.
    $temp->add(
        new admin_setting_configduration(
            'antivirus/quarantinetime',
            new lang_string('quarantinetime', 'antivirus'),
            new lang_string('quarantinetime_desc', 'antivirus'),
            \core\antivirus\quarantine::DEFAULT_QUARANTINE_TIME
        )
    );

    $ADMIN->add('antivirussettings', $temp);
    $plugins = core_plugin_manager::instance()->get_plugins_of_type('antivirus');
    core_collator::asort_objects_by_property($plugins, 'displayname');
    foreach ($plugins as $plugin) {
        /* @var \core\plugininfo\antivirus $plugin */
        $plugin->load_settings($ADMIN, 'antivirussettings', $hassiteconfig);
    }

    // Machine learning backend plugins.
    $plugins = core_plugin_manager::instance()->get_plugins_of_type('mlbackend');
    foreach ($plugins as $plugin) {
        $plugin->load_settings($ADMIN, 'mlbackendsettings', $hassiteconfig);
    }

/// Filter plugins

    $ADMIN->add('filtersettings', new admin_page_managefilters());

    // "filtersettings" settingpage
    $temp = new admin_settingpage('commonfiltersettings', new lang_string('commonfiltersettings', 'admin'));
    if ($ADMIN->fulltree) {
        $items = array();
        $items[] = new admin_setting_configselect('filteruploadedfiles', new lang_string('filteruploadedfiles', 'admin'), new lang_string('configfilteruploadedfiles', 'admin'), 0,
                array('0' => new lang_string('none'), '1' => new lang_string('allfiles'), '2' => new lang_string('htmlfilesonly')));
        $items[] = new admin_setting_configcheckbox('filtermatchoneperpage', new lang_string('filtermatchoneperpage', 'admin'), new lang_string('configfiltermatchoneperpage', 'admin'), 0);
        $items[] = new admin_setting_configcheckbox('filtermatchonepertext', new lang_string('filtermatchonepertext', 'admin'), new lang_string('configfiltermatchonepertext', 'admin'), 0);
        $items[] = new admin_setting_configcheckbox('filternavigationwithsystemcontext',
                new lang_string('filternavigationwithsystemcontext', 'admin'),
                new lang_string('configfilternavigationwithsystemcontext', 'admin'), 1);
        foreach ($items as $item) {
            $item->set_updatedcallback('reset_text_filters_cache');
            $temp->add($item);
        }
    }
    $ADMIN->add('filtersettings', $temp);

    $plugins = core_plugin_manager::instance()->get_plugins_of_type('filter');
    core_collator::asort_objects_by_property($plugins, 'displayname');
    foreach ($plugins as $plugin) {
        /** @var \core\plugininfo\filter $plugin */
        $plugin->load_settings($ADMIN, 'filtersettings', $hassiteconfig);
    }

    // Media players.
    $temp = new admin_settingpage('managemediaplayers', new lang_string('managemediaplayers', 'media'));
    $temp->add(new admin_setting_heading('mediaformats', get_string('mediaformats', 'core_media'),
        format_text(get_string('mediaformats_desc', 'core_media'), FORMAT_MARKDOWN)));
    $temp->add(new \core_admin\admin\admin_setting_plugin_manager(
        'media',
        \core_admin\table\media_management_table::class,
        'managemediaplayers',
        new lang_string('managemediaplayers', 'core_media'),
    ));
    $temp->add(new admin_setting_heading('managemediaplayerscommonheading', new lang_string('commonsettings', 'admin'), ''));
    $temp->add(new admin_setting_configtext('media_default_width',
        new lang_string('defaultwidth', 'core_media'), new lang_string('defaultwidthdesc', 'core_media'),
        640, PARAM_INT, 10));
    $temp->add(new admin_setting_configtext('media_default_height',
        new lang_string('defaultheight', 'core_media'), new lang_string('defaultheightdesc', 'core_media'),
        360, PARAM_INT, 10));
    $ADMIN->add('mediaplayers', $temp);

    // Convert plugins.
    $temp = new admin_settingpage('managefileconverterplugins', new lang_string('type_fileconvertermanage', 'plugin'));
    $temp->add(new admin_setting_manage_fileconverter_plugins());
    $ADMIN->add('fileconverterplugins', $temp);

    $plugins = core_plugin_manager::instance()->get_plugins_of_type('fileconverter');
    core_collator::asort_objects_by_property($plugins, 'displayname');
    foreach ($plugins as $plugin) {
        /** @var \core\plugininfo\media $plugin */
        $plugin->load_settings($ADMIN, 'fileconverterplugins', $hassiteconfig);
    }

    $plugins = core_plugin_manager::instance()->get_plugins_of_type('media');
    core_collator::asort_objects_by_property($plugins, 'displayname');
    foreach ($plugins as $plugin) {
        /** @var \core\plugininfo\media $plugin */
        $plugin->load_settings($ADMIN, 'mediaplayers', $hassiteconfig);
    }

    // Data format settings.
    $temp = new admin_settingpage('managedataformats', new lang_string('managedataformats'));
    $temp->add(new admin_setting_managedataformats());
    $ADMIN->add('dataformatsettings', $temp);

    $plugins = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
    core_collator::asort_objects_by_property($plugins, 'displayname');
    foreach ($plugins as $plugin) {
        /** @var \core\plugininfo\dataformat $plugin */
        $plugin->load_settings($ADMIN, 'dataformatsettings', $hassiteconfig);
    }

    // repository setting
    require_once("$CFG->dirroot/repository/lib.php");
    $managerepo = new lang_string('manage', 'repository');
    $url = $CFG->wwwroot.'/'.$CFG->admin.'/repository.php';

    // Add main page (with table)
    $temp = new admin_page_managerepositories();
    $ADMIN->add('repositorysettings', $temp);

    // Add common settings page
    $temp = new admin_settingpage('managerepositoriescommon', new lang_string('commonrepositorysettings', 'repository'));
    $temp->add(new admin_setting_configtext('repositorycacheexpire', new lang_string('cacheexpire', 'repository'), new lang_string('configcacheexpire', 'repository'), 120, PARAM_INT));
    $temp->add(new admin_setting_configtext('repositorygetfiletimeout', new lang_string('getfiletimeout', 'repository'), new lang_string('configgetfiletimeout', 'repository'), 30, PARAM_INT));
    $temp->add(new admin_setting_configtext('repositorysyncfiletimeout', new lang_string('syncfiletimeout', 'repository'), new lang_string('configsyncfiletimeout', 'repository'), 1, PARAM_INT));
    $temp->add(new admin_setting_configtext('repositorysyncimagetimeout', new lang_string('syncimagetimeout', 'repository'), new lang_string('configsyncimagetimeout', 'repository'), 3, PARAM_INT));
    $temp->add(new admin_setting_configcheckbox('repositoryallowexternallinks', new lang_string('allowexternallinks', 'repository'), new lang_string('configallowexternallinks', 'repository'), 1));
    $temp->add(new admin_setting_configcheckbox('legacyfilesinnewcourses', new lang_string('legacyfilesinnewcourses', 'admin'), new lang_string('legacyfilesinnewcourses_help', 'admin'), 0));
    $temp->add(new admin_setting_configcheckbox('legacyfilesaddallowed', new lang_string('legacyfilesaddallowed', 'admin'), new lang_string('legacyfilesaddallowed_help', 'admin'), 1));
    $ADMIN->add('repositorysettings', $temp);
    $ADMIN->add('repositorysettings', new admin_externalpage('repositorynew',
        new lang_string('addplugin', 'repository'), $url, 'moodle/site:config', true));
    $ADMIN->add('repositorysettings', new admin_externalpage('repositorydelete',
        new lang_string('deleterepository', 'repository'), $url, 'moodle/site:config', true));
    $ADMIN->add('repositorysettings', new admin_externalpage('repositorycontroller',
        new lang_string('manage', 'repository'), $url, 'moodle/site:config', true));
    $ADMIN->add('repositorysettings', new admin_externalpage('repositoryinstancenew',
        new lang_string('createrepository', 'repository'), $url, 'moodle/site:config', true));
    $ADMIN->add('repositorysettings', new admin_externalpage('repositoryinstanceedit',
        new lang_string('editrepositoryinstance', 'repository'), $url, 'moodle/site:config', true));
    $plugins = core_plugin_manager::instance()->get_plugins_of_type('repository');
    core_collator::asort_objects_by_property($plugins, 'displayname');
    foreach ($plugins as $plugin) {
        /** @var \core\plugininfo\repository $plugin */
        $plugin->load_settings($ADMIN, 'repositorysettings', $hassiteconfig);
    }
}

// Plagiarism plugin settings
if ($hassiteconfig && !empty($CFG->enableplagiarism)) {
    $ADMIN->add('plagiarism', new admin_externalpage('manageplagiarismplugins', new lang_string('manageplagiarism', 'plagiarism'),
        $CFG->wwwroot . '/' . $CFG->admin . '/plagiarism.php'));

    $plugins = core_plugin_manager::instance()->get_plugins_of_type('plagiarism');
    core_collator::asort_objects_by_property($plugins, 'displayname');
    foreach ($plugins as $plugin) {
        /** @var \core\plugininfo\plagiarism $plugin */
        $plugin->load_settings($ADMIN, 'plagiarism', $hassiteconfig);
    }
}

// Comments report, note this page is really just a means to delete comments so check that.
$ADMIN->add('reports', new admin_externalpage('comments', new lang_string('comments'), $CFG->wwwroot . '/comment/index.php',
    'moodle/comment:delete'));

// Now add reports
$pages = array();
foreach (core_component::get_plugin_list('report') as $report => $plugindir) {
    $settings_path = "$plugindir/settings.php";
    if (file_exists($settings_path)) {
        $settings = new admin_settingpage('report' . $report,
                new lang_string('pluginname', 'report_' . $report), 'moodle/site:config');
        include($settings_path);
        if ($settings) {
            $pages[] = $settings;
        }
    }
}
$ADMIN->add('reportplugins', new admin_externalpage('managereports', new lang_string('reportsmanage', 'admin'),
                                                    $CFG->wwwroot . '/' . $CFG->admin . '/reports.php'));
core_collator::asort_objects_by_property($pages, 'visiblename');
foreach ($pages as $page) {
    $ADMIN->add('reportplugins', $page);
}

if ($hassiteconfig) {
    // Global Search engine plugins.
    $temp = new admin_settingpage('manageglobalsearch', new lang_string('globalsearchmanage', 'admin'));

    $pages = array();
    $engines = array();
    foreach (core_component::get_plugin_list('search') as $engine => $plugindir) {
        $engines[$engine] = new lang_string('pluginname', 'search_' . $engine);
        $settingspath = "$plugindir/settings.php";
        if (file_exists($settingspath)) {
            $settings = new admin_settingpage('search' . $engine,
                    new lang_string('pluginname', 'search_' . $engine), 'moodle/site:config');
            include($settingspath);
            if ($settings) {
                $pages[] = $settings;
            }
        }
    }

    // Setup status.
    $temp->add(new admin_setting_searchsetupinfo());

    // Search engine selection.
    $temp->add(new admin_setting_heading('searchengineheading', new lang_string('searchengine', 'admin'), ''));
    $searchengineselect = new admin_setting_configselect('searchengine',
            new lang_string('selectsearchengine', 'admin'), '', 'simpledb', $engines);
    $searchengineselect->set_validate_function(function(string $value): string {
        global $CFG;

        // Check nobody's setting the indexing and query-only server to the same one.
        if (isset($CFG->searchenginequeryonly) && $CFG->searchenginequeryonly === $value) {
            return get_string('searchenginequeryonlysame', 'admin');
        } else {
            return '';
        }
    });
    $temp->add($searchengineselect);
    $temp->add(new admin_setting_heading('searchoptionsheading', new lang_string('searchoptions', 'admin'), ''));
    $temp->add(new admin_setting_configcheckbox('searchindexwhendisabled',
            new lang_string('searchindexwhendisabled', 'admin'), new lang_string('searchindexwhendisabled_desc', 'admin'),
            0));
    $temp->add(new admin_setting_configduration('searchindextime',
            new lang_string('searchindextime', 'admin'), new lang_string('searchindextime_desc', 'admin'),
            600));
    $temp->add(new admin_setting_heading('searchcoursesheading', new lang_string('searchablecourses', 'admin'), ''));
    $options = [
        0 => new lang_string('searchallavailablecourses_off', 'admin'),
        1 => new lang_string('searchallavailablecourses_on', 'admin')
    ];
    $temp->add(new admin_setting_configselect('searchallavailablecourses',
            new lang_string('searchallavailablecourses', 'admin'),
            new lang_string('searchallavailablecoursesdesc', 'admin'),
            0, $options));
    $temp->add(new admin_setting_configcheckbox('searchincludeallcourses',
        new lang_string('searchincludeallcourses', 'admin'), new lang_string('searchincludeallcourses_desc', 'admin'),
        0));

    // Search display options.
    $temp->add(new admin_setting_heading('searchdisplay', new lang_string('searchdisplay', 'admin'), ''));
    $temp->add(new admin_setting_configcheckbox('searchenablecategories',
        new lang_string('searchenablecategories', 'admin'),
        new lang_string('searchenablecategories_desc', 'admin'),
        0));
    $options = [];
    foreach (\core_search\manager::get_search_area_categories() as $category) {
        $options[$category->get_name()] = $category->get_visiblename();
    }
    $temp->add(new admin_setting_configselect('searchdefaultcategory',
        new lang_string('searchdefaultcategory', 'admin'),
        new lang_string('searchdefaultcategory_desc', 'admin'),
        \core_search\manager::SEARCH_AREA_CATEGORY_ALL, $options));
    $temp->add(new admin_setting_configcheckbox('searchhideallcategory',
        new lang_string('searchhideallcategory', 'admin'),
        new lang_string('searchhideallcategory_desc', 'admin'),
        0));

    // Top result options.
    $temp->add(new admin_setting_heading('searchtopresults', new lang_string('searchtopresults', 'admin'), ''));
    // Max Top results.
    $options = range(0, 10);
    $temp->add(new admin_setting_configselect('searchmaxtopresults',
        new lang_string('searchmaxtopresults', 'admin'),
        new lang_string('searchmaxtopresults_desc', 'admin'),
        3, $options));
    // Teacher roles.
    $options = [];
    foreach (role_get_names() as $role) {
        $options[$role->id] = $role->localname;
    }
    $temp->add(new admin_setting_configmultiselect('searchteacherroles',
        new lang_string('searchteacherroles', 'admin'),
        new lang_string('searchteacherroles_desc', 'admin'),
        [], $options));

    $temp->add(new admin_setting_heading('searchmanagement', new lang_string('searchmanagement', 'admin'),
            new lang_string('searchmanagement_desc', 'admin')));

    // Get list of search engines including those with alternate settings.
    $searchenginequeryonlyselect = new admin_setting_configselect('searchenginequeryonly',
            new lang_string('searchenginequeryonly', 'admin'),
            new lang_string('searchenginequeryonly_desc', 'admin'), '', function() use($engines) {
                $options = ['' => new lang_string('searchenginequeryonly_none', 'admin')];
                foreach ($engines as $name => $display) {
                    $options[$name] = $display;

                    $classname = '\search_' . $name . '\engine';
                    $engine = new $classname;
                    if ($engine->has_alternate_configuration()) {
                        $options[$name . '-alternate'] =
                                new lang_string('searchenginealternatesettings', 'admin', $display);
                    }
                }
                return $options;
            });
    $searchenginequeryonlyselect->set_validate_function(function(string $value): string {
        global $CFG;

        // Check nobody's setting the indexing and query-only server to the same one.
        if (isset($CFG->searchengine) && $CFG->searchengine === $value) {
            return get_string('searchenginequeryonlysame', 'admin');
        } else {
            return '';
        }
    });
    $temp->add($searchenginequeryonlyselect);
    $temp->add(new admin_setting_configcheckbox('searchbannerenable',
            new lang_string('searchbannerenable', 'admin'), new lang_string('searchbannerenable_desc', 'admin'),
            0));
    $temp->add(new admin_setting_confightmleditor('searchbanner',
            new lang_string('searchbanner', 'admin'), '', ''));

    $ADMIN->add('searchplugins', $temp);
    $ADMIN->add('searchplugins', new admin_externalpage('searchareas', new lang_string('searchareas', 'admin'),
        new moodle_url('/admin/searchareas.php')));

    core_collator::asort_objects_by_property($pages, 'visiblename');
    foreach ($pages as $page) {
        $ADMIN->add('searchplugins', $page);
    }
}

/// Add all admin tools
if ($hassiteconfig) {
    $settingspage = new admin_settingpage('toolsmanagement', new lang_string('toolsmanage', 'admin'));
    $ADMIN->add('tools', $settingspage);
    $settingspage->add(new \core_admin\admin\admin_setting_plugin_manager(
        'tool',
        \core_admin\table\tool_plugin_management_table::class,
        'managetools',
        new lang_string('toolsmanage', 'admin')
    ));
}

// Now add various admin tools.
$plugins = core_plugin_manager::instance()->get_plugins_of_type('tool');
core_collator::asort_objects_by_property($plugins, 'displayname');
foreach ($plugins as $plugin) {
    /** @var \core\plugininfo\tool $plugin */
    $plugin->load_settings($ADMIN, null, $hassiteconfig);
}

// Now add the Cache plugins
if ($hassiteconfig) {
    $ADMIN->add('cache', new admin_externalpage('cacheconfig', new lang_string('cacheconfig', 'cache'), $CFG->wwwroot .'/cache/admin.php'));
    $ADMIN->add('cache', new admin_externalpage('cachetestperformance', new lang_string('testperformance', 'cache'), $CFG->wwwroot . '/cache/testperformance.php'));
    $ADMIN->add('cache', new admin_externalpage('cacheusage',
            new lang_string('cacheusage', 'cache'), $CFG->wwwroot . '/cache/usage.php'));
    $ADMIN->locate('cachestores')->set_sorting(true);
    foreach (core_component::get_plugin_list('cachestore') as $plugin => $path) {
        $settingspath = $path.'/settings.php';
        if (file_exists($settingspath)) {
            $settings = new admin_settingpage('cachestore_'.$plugin.'_settings', new lang_string('pluginname', 'cachestore_'.$plugin), 'moodle/site:config');
            include($settingspath);
            $ADMIN->add('cachestores', $settings);
        }
    }
}

// SMS plugins.
if ($hassiteconfig) {
    $ADMIN->add(
        'sms',
        new admin_externalpage(
            'smsgateway',
            new lang_string('manage_sms_gateways', 'core_sms'),
            $CFG->wwwroot . '/sms/sms_gateways.php',
        ),
    );
    foreach (core_component::get_plugin_list('smsgateway') as $plugin => $path) {
        $settingspath = $path . '/settings.php';
        if (file_exists($settingspath)) {
            $settings = new admin_settingpage(
                'smsgateway_' . $plugin . '_settings',
                new lang_string('pluginname', 'smsgateway_' . $plugin),
                'moodle/site:config',
            );
            include($settingspath);
            $ADMIN->add('smsgateway', $settings);
        }
    }
}

/// Add all local plugins - must be always last!
if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_externalpage('managelocalplugins', new lang_string('localpluginsmanage'),
                                                        $CFG->wwwroot . '/' . $CFG->admin . '/localplugins.php'));
}

// Extend settings for each local plugin. Note that their settings may be in any part of the
// settings tree and may be visible not only for administrators.
$plugins = core_plugin_manager::instance()->get_plugins_of_type('local');
core_collator::asort_objects_by_property($plugins, 'displayname');
foreach ($plugins as $plugin) {
    /** @var \core\plugininfo\local $plugin */
    $plugin->load_settings($ADMIN, null, $hassiteconfig);
}
