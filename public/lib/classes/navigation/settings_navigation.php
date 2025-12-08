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

namespace core\navigation;

use admin_category;
use admin_externalpage;
use admin_settingpage;
use core\context\system as context_system;
use core\context\user as context_user;
use core\context_helper;
use core\output\pix_icon;
use core\url;
use moodle_page;
use part_of_admin_tree;

/**
 * Class used to manage the settings option for the current page
 *
 * This class is used to manage the settings options in a tree format (recursively)
 * and was created initially for use with the settings blocks.
 *
 * @package   core
 * @category  navigation
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_navigation extends navigation_node {
    /** @var \core\context the current context */
    protected $context;
    /** @var moodle_page the moodle page that the navigation belongs to */
    protected $page;
    /** @var string contains administration section navigation_nodes */
    protected $adminsection;
    /** @var bool A switch to see if the navigation node is initialised */
    protected $initialised = false;
    /** @var array An array of users that the nodes can extend for. */
    protected $userstoextendfor = [];
    /** @var navigation_cache **/
    protected $cache;

    /**
     * Sets up the object with basic settings and preparse it for use
     *
     * @param moodle_page $page
     */
    public function __construct(moodle_page &$page) {
        if (during_initial_install()) {
            return;
        }
        $this->page = $page;
        // Initialise the main navigation. It is most important that this is done before we try anything.
        $this->page->navigation->initialise();

        // Initialise the navigation cache.
        $this->cache = new navigation_cache(self::CACHE_NAME);
        $this->children = new navigation_node_collection();
    }

    /**
     * Initialise the settings navigation based on the current context
     *
     * This function initialises the settings navigation tree for a given context
     * by calling supporting functions to generate major parts of the tree.
     *
     */
    public function initialise() {
        global $SESSION;

        if (during_initial_install()) {
            return false;
        } else if ($this->initialised) {
            return true;
        }
        $this->id = 'settingsnav';
        $this->context = $this->page->context;

        $context = $this->context;
        if ($context->contextlevel == CONTEXT_BLOCK) {
            $this->load_block_settings();
            $context = $context->get_parent_context();
            $this->context = $context;
        }

        // Load user settings for user context.
        if ($context->contextlevel == CONTEXT_USER) {
            $this->load_user_settings();
        }

        $adminsettings = false;
        if (isloggedin() && !isguestuser() && (!isset($SESSION->load_navigation_admin) || $SESSION->load_navigation_admin)) {
            $isadminpage = $this->is_admin_tree_needed();

            if (has_capability('moodle/site:configview', context_system::instance())) {
                if (has_capability('moodle/site:config', context_system::instance())) {
                    // Make sure this works even if config capability changes on the fly
                    // and also make it fast for admin right after login.
                    $SESSION->load_navigation_admin = 1;
                    if ($isadminpage) {
                        $adminsettings = $this->load_administration_settings();
                    }
                } else if (!isset($SESSION->load_navigation_admin)) {
                    $adminsettings = $this->load_administration_settings();
                    $SESSION->load_navigation_admin = (int)($adminsettings->children->count() > 0);
                } else if ($SESSION->load_navigation_admin) {
                    if ($isadminpage) {
                        $adminsettings = $this->load_administration_settings();
                    }
                }

                // Print empty navigation node, if needed.
                if ($SESSION->load_navigation_admin && !$isadminpage) {
                    if ($adminsettings) {
                        // Do not print settings tree on pages that do not need it, this helps with performance.
                        $adminsettings->remove();
                        $adminsettings = false;
                    }
                    $siteadminnode = $this->add(
                        get_string('administrationsite'),
                        new url('/admin/search.php'),
                        self::TYPE_SITE_ADMIN,
                        null,
                        'siteadministration'
                    );
                    $siteadminnode->id = 'expandable_branch_' . $siteadminnode->type . '_' .
                            clean_param($siteadminnode->key, PARAM_ALPHANUMEXT);
                    $siteadminnode->requiresajaxloading = 'true';
                }
            }
        }

        if ($context->contextlevel == CONTEXT_SYSTEM && $adminsettings) {
            $adminsettings->force_open();
        }

        // At this point we give any local plugins the ability to extend/tinker with the navigation settings.
        $this->load_local_plugin_settings();

        foreach ($this->children as $key => $node) {
            if ($node->nodetype == self::NODETYPE_BRANCH && $node->children->count() == 0) {
                // Site administration is shown as link.
                if (!empty($SESSION->load_navigation_admin) && ($node->type === self::TYPE_SITE_ADMIN)) {
                    continue;
                }
                $node->remove();
            }
        }
        $this->initialised = true;
    }

    /**
     * Override the parent function so that we can add preceeding hr's and set a
     * root node class against all first level element
     *
     * @param string $text text to be used for the link.
     * @param string|url $url url for the new node
     * @param int $type the type of node navigation_node::TYPE_*
     * @param string $shorttext
     * @param string|int $key a key to access the node by.
     * @param pix_icon $icon An icon that appears next to the node.
     * @return navigation_node with the new node added to it.
     */
    #[\Override]
    public function add($text, $url = null, $type = null, $shorttext = null, $key = null, ?pix_icon $icon = null) {
        $node = parent::add($text, $url, $type, $shorttext, $key, $icon);
        $node->add_class('root_node');
        return $node;
    }

    /**
     * This function allows the user to add something to the start of the settings
     * navigation, which means it will be at the top of the settings navigation block
     *
     * @param string $text text to be used for the link.
     * @param string|url $url url for the new node
     * @param int $type the type of node navigation_node::TYPE_*
     * @param string $shorttext
     * @param string|int $key a key to access the node by.
     * @param pix_icon $icon An icon that appears next to the node.
     * @return navigation_node $node with the new node added to it.
     */
    public function prepend($text, $url = null, $type = null, $shorttext = null, $key = null, ?pix_icon $icon = null) {
        $children = $this->children;
        $childrenclass = get_class($children);
        $this->children = new $childrenclass();
        $node = $this->add($text, $url, $type, $shorttext, $key, $icon);
        foreach ($children as $child) {
            $this->children->add($child);
        }
        return $node;
    }

    /**
     * Does this page require loading of full admin tree or is
     * it enough rely on AJAX?
     *
     * @return bool
     */
    protected function is_admin_tree_needed() {
        if (self::$loadadmintree) {
            // Usually external admin page or settings page.
            return true;
        }

        if ($this->page->pagelayout === 'admin' || strpos($this->page->pagetype, 'admin-') === 0) {
            // Admin settings tree is intended for system level settings and management only, use navigation for the rest!
            if ($this->page->context->contextlevel != CONTEXT_SYSTEM) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Load the site administration tree
     *
     * This function loads the site administration tree by using the lib/adminlib library functions
     *
     * @param navigation_node $referencebranch A reference to a branch in the settings
     *      navigation tree
     * @param part_of_admin_tree $adminbranch The branch to add, if null generate the admin
     *      tree and start at the beginning
     * @return mixed A key to access the admin tree by
     */
    protected function load_administration_settings(
        ?navigation_node $referencebranch = null,
        ?part_of_admin_tree $adminbranch = null,
    ) {
        global $CFG;

        // Check if we are just starting to generate this navigation.
        if ($referencebranch === null) {
            // Require the admin lib then get an admin structure.
            if (!function_exists('admin_get_root')) {
                require_once($CFG->dirroot . '/lib/adminlib.php');
            }
            $adminroot = admin_get_root(false, false);
            // This is the active section identifier.
            $this->adminsection = $this->page->url->param('section');

            // Disable the navigation from automatically finding the active node.
            navigation_node::$autofindactive = false;
            $referencebranch = $this->add(
                get_string('administrationsite'),
                '/admin/search.php',
                self::TYPE_SITE_ADMIN,
                null,
                'root',
            );
            foreach ($adminroot->children as $adminbranch) {
                $this->load_administration_settings($referencebranch, $adminbranch);
            }
            navigation_node::$autofindactive = true;

            // Use the admin structure to locate the active page.
            if (!$this->contains_active_node() && $current = $adminroot->locate($this->adminsection, true)) {
                $currentnode = $this;
                while (($pathkey = array_pop($current->path)) !== null && $currentnode) {
                    $currentnode = $currentnode->get($pathkey);
                }
                if ($currentnode) {
                    $currentnode->make_active();
                }
            } else {
                $this->scan_for_active_node($referencebranch);
            }
            return $referencebranch;
        } else if ($adminbranch->check_access()) {
            // We have a reference branch that we can access and is not hidden `hurrah`
            // Now we need to display it and any children it may have.
            $url = null;
            $icon = null;

            if ($adminbranch instanceof \core_admin\local\settings\linkable_settings_page) {
                if (empty($CFG->linkadmincategories) && $adminbranch instanceof admin_category) {
                    $url = null;
                } else {
                    $url = $adminbranch->get_settings_page_url();
                }
            }

            // Add the branch.
            $reference = $referencebranch->add(
                $adminbranch->visiblename,
                $url,
                self::TYPE_SETTING,
                null,
                $adminbranch->name,
                $icon,
            );

            if ($adminbranch->is_hidden()) {
                if (
                    (
                        $adminbranch instanceof admin_externalpage
                        || $adminbranch instanceof admin_settingpage
                    )
                    && $adminbranch->name == $this->adminsection
                ) {
                    $reference->add_class('hidden');
                } else {
                    $reference->display = false;
                }
            }

            // Check if we are generating the admin notifications and whether notificiations exist.
            if ($adminbranch->name === 'adminnotifications' && admin_critical_warnings_present()) {
                $reference->add_class('criticalnotification');
            }
            // Check if this branch has children.
            if (
                $reference
                && isset($adminbranch->children)
                && is_array($adminbranch->children)
                && count($adminbranch->children) > 0
            ) {
                foreach ($adminbranch->children as $branch) {
                    // Generate the child branches as well now using this branch as the reference.
                    $this->load_administration_settings($reference, $branch);
                }
            } else {
                $reference->icon = new pix_icon('i/settings', '');
            }
        }
    }

    /**
     * This function recursivily scans nodes until it finds the active node or there
     * are no more nodes.
     * @param navigation_node $node
     */
    protected function scan_for_active_node(navigation_node $node) {
        if (!$node->check_if_active() && $node->children->count() > 0) {
            foreach ($node->children as &$child) {
                $this->scan_for_active_node($child);
            }
        }
    }

    /**
     * Gets a navigation node given an array of keys that represent the path to
     * the desired node.
     *
     * @param array $path
     * @return navigation_node|false
     */
    protected function get_by_path(array $path) {
        $node = $this->get(array_shift($path));
        foreach ($path as $key) {
            $node->get($key);
        }
        return $node;
    }

    /**
     * Return the current moodle_page object.
     *
     * @return moodle_page
     */
    public function get_page(): moodle_page {
        return $this->page;
    }

    /**
     * Load the user settings.
     *
     * @return navigation_node|false
     */
    protected function load_user_settings() {
        global $USER, $CFG;

        if (isguestuser() || !isloggedin()) {
            return false;
        }

        $navusers = $this->page->navigation->get_extending_users();

        if (count($this->userstoextendfor) > 0 || count($navusers) > 0) {
            $usernode = null;
            foreach ($this->userstoextendfor as $userid) {
                if ($userid == $USER->id) {
                    continue;
                }
                $node = $this->generate_user_settings($userid, 'userviewingsettings');
                if (is_null($usernode)) {
                    $usernode = $node;
                }
            }
            foreach ($navusers as $user) {
                if ($user->id == $USER->id) {
                    continue;
                }
                $node = $this->generate_user_settings($user->id, 'userviewingsettings');
                if (is_null($usernode)) {
                    $usernode = $node;
                }
            }
            $this->generate_user_settings($USER->id);
        } else {
            $usernode = $this->generate_user_settings($USER->id);
        }
        return $usernode;
    }

    /**
     * Extends the settings navigation for the given user.
     *
     * @param int $userid
     */
    public function extend_for_user($userid) {
        if (!in_array($userid, $this->userstoextendfor)) {
            $this->userstoextendfor[] = $userid;
            if ($this->initialised) {
                $this->generate_user_settings($userid, 'userviewingsettings');
                $children = [];
                foreach ($this->children as $child) {
                    $children[] = $child;
                }
                array_unshift($children, array_pop($children));
                $this->children = new navigation_node_collection();
                foreach ($children as $child) {
                    $this->children->add($child);
                }
            }
        }
    }

    /**
     * Generate user settings navigation.
     *
     * @param int $userid The user id to load for
     * @param string $gstitle The string to pass to get_string for the branch title
     * @return navigation_node|false
     */
    protected function generate_user_settings($userid, $gstitle = 'usercurrentsettings') {
        global $DB, $CFG, $USER;

        $systemcontext = context_system::instance();
        $currentuser = ($USER->id == $userid);

        if ($currentuser) {
            $user = $USER;
            $usercontext = context_user::instance($user->id);
        } else {
            $select = context_helper::get_preload_record_columns_sql('ctx');
            $sql = "SELECT u.*, $select
                      FROM {user} u
                      JOIN {context} ctx ON u.id = ctx.instanceid
                     WHERE u.id = :userid AND ctx.contextlevel = :contextlevel";
            $params = ['userid' => $userid, 'contextlevel' => CONTEXT_USER];
            $user = $DB->get_record_sql($sql, $params, IGNORE_MISSING);
            if (!$user) {
                return false;
            }
            context_helper::preload_from_record($user);

            // Check that the user can view the profile.
            $usercontext = context_user::instance($user->id);
            $canviewuser = has_capability('moodle/user:viewdetails', $usercontext);

            // Reduce possibility of "browsing" userbase at site level.
            if ($CFG->forceloginforprofiles && !has_coursecontact_role($user->id) && !$canviewuser) {
                return false;
            }
        }

        $userfullname = fullname($user, true);

        // Add a user setting branch.
        if ($gstitle == 'usercurrentsettings') {
            $mainpage = $this->add(get_string('usercurrentsettings', 'moodle'), null, self::TYPE_CONTAINER, null, $gstitle);
        } else if ($gstitle == 'userviewingsettings') {
            $mainpage = $this->add(
                get_string('userviewingsettings', 'moodle', $userfullname),
                null,
                self::TYPE_CONTAINER,
                null,
                $gstitle . $user->id,
            );
        } else {
            $mainpage = $this->add(get_string($gstitle, 'moodle'), null, self::TYPE_CONTAINER, null, $gstitle);
        }

        $mainpage->id = 'usersettings';

        if ($currentuser) {
            // Preferences page.
            $url = new url('/user/preferences.php');
            $preferences = $mainpage->add(get_string('preferences', 'moodle'), $url, self::TYPE_SETTING, null, 'mypreferences');

            if (!empty($CFG->enableblogs)) {
                $blogs = $mainpage->add(get_string('blogs', 'blog'), null, self::TYPE_CONTAINER, null, 'blogs');
                $blogs->add(
                    get_string('preferences', 'blog'),
                    new url('/blog/preferences.php'),
                    self::TYPE_SETTING,
                    null,
                    'blogpreferences'
                );
                if ($CFG->useexternalblogs) {
                    $blogs->add(
                        get_string('externalblogs', 'blog'),
                        new url('/blog/external_blogs.php'),
                        self::TYPE_SETTING,
                        null,
                        'blogexternal'
                    );
                }
            }

            // Let plugins hook into user settings.
            $pluginsfunction = get_plugins_with_function('extend_navigation_user_settings', 'lib.php');
            foreach ($pluginsfunction as $plugintype => $plugins) {
                foreach ($plugins as $pluginfunction) {
                    $pluginfunction($mainpage, $user, $usercontext, null, null);
                }
            }
        }

        return $mainpage;
    }

    /**
     * This function loads the block settings navigation.
     */
    protected function load_block_settings() {
        global $CFG;

        $blockcontext = $this->page->context;
        $block = $this->page->blockinstance;
        if ($block->user_can_edit() || $block->user_can_addto($this->page)) {
            $str = new \lang_string('blocksettings', 'block');
            $blocksettings = $this->add($str, null, self::TYPE_SETTING, null, 'blocksettings');
            $blocksettings->force_open();

            if ($block->user_can_edit()) {
                $str = new \lang_string('configureblock', 'block');
                $url = new url('/admin/block.php', ['block' => $block->instance->id, 'action' => 'config']);
                $blocksettings->add($str, $url, self::TYPE_SETTING, null, 'configureblock');
            }

            $pluginname = $block->name();
            $capabilities = \block_base::fetch_capabilities($pluginname, $blockcontext);
            if (!empty($capabilities)) {
                $str = get_string('permissions', 'role');
                $url = new url('/admin/roles/permissions.php', ['contextid' => $blockcontext->id]);
                $blocksettings->add($str, $url, self::TYPE_SETTING, null, 'permissions');
            }
            $str = get_string('checkpermissions', 'role');
            $url = new url('/admin/roles/check.php', ['contextid' => $blockcontext->id]);
            $blocksettings->add($str, $url, self::TYPE_SETTING, null, 'checkpermissions');
        }
    }

    /**
     * This function gives local plugins an opportunity to modify navigation.
     */
    protected function load_local_plugin_settings() {
        foreach (get_plugin_list_with_function('local', 'extend_settings_navigation') as $function) {
            $function($this, $this->context);
        }
    }

    /**
     * Clears the navigation cache.
     */
    public function clear_cache() {
        $this->cache->clear();
    }
}

// Alias this class to the old name.
// This file will be autoloaded by the legacyclasses autoload system.
// In future all uses of this class will be corrected and the legacy references will be removed.
class_alias(settings_navigation::class, \settings_navigation::class);
