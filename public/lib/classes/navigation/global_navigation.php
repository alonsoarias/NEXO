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

use core\context\system as context_system;
use core\context\user as context_user;
use core\context_helper;
use core\output\pix_icon;
use core\url;
use moodle_page;
use stdClass;

/**
 * The global navigation class used for... the global navigation
 *
 * This class is used by PAGE to store the global navigation for the site
 * and is then used by the settings nav and navbar to save on processing and DB calls
 *
 * @package   core
 * @category  navigation
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class global_navigation extends navigation_node {
    /** @var moodle_page The Moodle page this navigation object belongs to. */
    protected $page;
    /** @var bool switch to let us know if the navigation object is initialised*/
    protected $initialised = false;
    /** @var navigation_node[] An array for containing  root navigation nodes */
    protected $rootnodes = [];
    /** @var array An array of stdClasses for users that the navigation is extended for */
    protected $extendforuser = [];
    /** @var navigation_cache */
    protected $cache;
    /** @var int userid to allow parent to see child's profile page navigation */
    protected $useridtouseforparentchecks = 0;

    /**
     * Constructs a new global navigation
     *
     * @param moodle_page $page The page this navigation object belongs to
     */
    public function __construct(moodle_page $page) {
        global $CFG;

        if (during_initial_install()) {
            return;
        }

        $homepage = get_home_page();
        if ($homepage == HOMEPAGE_MY) {
            // We are using the users my moodle for the root element.
            $properties = [
                'key' => 'myhome',
                'type' => navigation_node::TYPE_SYSTEM,
                'text' => get_string('myhome'),
                'action' => new url('/my/'),
                'icon' => new pix_icon('i/dashboard', ''),
            ];
        } else {
            // We are using the site home for the root element.
            $properties = [
                'key' => 'home',
                'type' => navigation_node::TYPE_SYSTEM,
                'text' => get_string('home'),
                'action' => new url('/'),
                'icon' => new pix_icon('i/home', ''),
            ];
        }

        // Use the parents constructor.... good good reuse.
        parent::__construct($properties);
        $this->showinflatnavigation = true;

        // Initalise and set defaults.
        $this->page = $page;
        $this->forceopen = true;
        $this->cache = new navigation_cache(self::CACHE_NAME);
    }

    /**
     * Mutator to set userid to allow parent to see child's profile
     * page navigation. See MDL-25805 for initial issue. Linked to it
     * is an issue explaining why this is a REALLY UGLY HACK thats not
     * for you to use!
     *
     * @param int $userid userid of profile page that parent wants to navigate around.
     */
    public function set_userid_for_parent_checks($userid) {
        $this->useridtouseforparentchecks = $userid;
    }


    /**
     * Initialises the navigation object.
     *
     * This causes the navigation object to look at the current state of the page
     * that it is associated with and then load the appropriate content.
     *
     * This should only occur the first time that the navigation structure is utilised
     * which will normally be either when the navbar is called to be displayed or
     * when a block makes use of it.
     *
     * @return bool
     */
    public function initialise() {
        global $CFG, $USER;
        // Check if it has already been initialised.
        if ($this->initialised || during_initial_install()) {
            return true;
        }
        $this->initialised = true;

        // Set up the base root nodes.
        $this->rootnodes = [];
        $defaulthomepage = get_home_page();
        if ($defaulthomepage == HOMEPAGE_SITE) {
            // The home element should be my moodle because the root element is the site.
            if (isloggedin() && !isguestuser()) {
                if (!empty($CFG->enabledashboard)) {
                    // Only add dashboard to home if it's enabled.
                    $this->rootnodes['home'] = $this->add(
                        get_string('myhome'),
                        new url('/my/'),
                        self::TYPE_SETTING,
                        null,
                        'myhome',
                        new pix_icon('i/dashboard', '')
                    );
                    $this->rootnodes['home']->showinflatnavigation = true;
                }
            }
        } else {
            // The home element should be the site because the root node is my moodle.
            $this->rootnodes['home'] = $this->add(
                get_string('sitehome'),
                new url('/'),
                self::TYPE_SETTING,
                null,
                'home',
                new pix_icon('i/home', '')
            );
            $this->rootnodes['home']->showinflatnavigation = true;
            if (!empty($CFG->defaulthomepage) && $CFG->defaulthomepage == HOMEPAGE_MY) {
                // We need to stop automatic redirection.
                $this->rootnodes['home']->action->param('redirect', '0');
            }
        }
        $this->rootnodes['myprofile'] = $this->add(get_string('profile'), null, self::TYPE_USER, null, 'myprofile');
        $this->rootnodes['users'] = $this->add(get_string('users'), null, self::TYPE_ROOTNODE, null, 'users');

        // Load for the current user.
        $this->load_for_user();

        // Load each extending user into the navigation.
        foreach ($this->extendforuser as $user) {
            if ($user->id != $USER->id) {
                $this->load_for_user($user);
            }
        }

        // Give the local plugins a chance to include some navigation if they want.
        $this->load_local_plugin_navigation();

        // Remove any empty root nodes.
        foreach ($this->rootnodes as $node) {
            // Dont remove the home node.
            if (!in_array($node->key, ['home', 'myhome']) && !$node->has_children() && !$node->isactive) {
                $node->remove();
            }
        }

        if (!$this->contains_active_node()) {
            $this->search_for_active_node();
        }

        return true;
    }

    /**
     * This function gives local plugins an opportunity to modify navigation.
     */
    protected function load_local_plugin_navigation() {
        foreach (get_plugin_list_with_function('local', 'extend_navigation') as $function) {
            $function($this);
        }
    }

    /**
     * Returns true if the current user is a parent of the user being currently viewed.
     *
     * If the current user is not viewing another user, or if the current user does not hold any parent roles over the
     * other user being viewed this function returns false.
     * In order to set the user for whom we are checking against you must call {@link set_userid_for_parent_checks()}
     *
     * @since Moodle 2.4
     * @return bool
     */
    protected function current_user_is_parent_role() {
        global $USER, $DB;
        if ($this->useridtouseforparentchecks && $this->useridtouseforparentchecks != $USER->id) {
            $usercontext = context_user::instance($this->useridtouseforparentchecks, MUST_EXIST);
            if (!has_capability('moodle/user:viewdetails', $usercontext)) {
                return false;
            }
            if ($DB->record_exists('role_assignments', ['userid' => $USER->id, 'contextid' => $usercontext->id])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Loads user specific information into the navigation in the appropriate place.
     *
     * If no user is provided the current user is assumed.
     *
     * @param stdClass $user
     * @param bool $forceforcontext probably force something to be loaded somewhere (ask SamH if not sure what this means)
     * @return bool
     */
    protected function load_for_user($user = null, $forceforcontext = false) {
        global $DB, $CFG, $USER;

        if ($user === null) {
            // We can't require login here but if the user isn't logged in we don't want to show anything.
            if (!isloggedin() || isguestuser()) {
                return false;
            }
            $user = $USER;
        } else if (!is_object($user)) {
            // If the user is not an object then get them from the database.
            $select = context_helper::get_preload_record_columns_sql('ctx');
            $sql = "SELECT u.*, $select
                      FROM {user} u
                      JOIN {context} ctx ON u.id = ctx.instanceid
                     WHERE u.id = :userid AND
                           ctx.contextlevel = :contextlevel";
            $user = $DB->get_record_sql($sql, ['userid' => (int)$user, 'contextlevel' => CONTEXT_USER], MUST_EXIST);
            context_helper::preload_from_record($user);
        }

        $iscurrentuser = ($user->id == $USER->id);

        $usercontext = context_user::instance($user->id);
        $systemcontext = context_system::instance();

        // Create a node to add user information under.
        $usersnode = null;
        if ($USER->id != $user->id) {
            // This is the site so add a users node to the root branch.
            $usersnode = $this->rootnodes['users'];
            $userviewurl = new url('/user/profile.php', ['id' => $user->id]);
        }
        if (!$usersnode) {
            return false;
        }

        // Add a branch for the current user.
        // Only reveal user details if $user is the current user, or a user to which the current user has access.
        $viewprofile = true;
        if (!$iscurrentuser) {
            require_once($CFG->dirroot . '/user/lib.php');
            if ($this->page->context->contextlevel == CONTEXT_USER && !has_capability('moodle/user:viewdetails', $usercontext)) {
                $viewprofile = false;
            } else if ($this->page->context->contextlevel != CONTEXT_USER && !user_can_view_profile($user, null, $usercontext)) {
                $viewprofile = false;
            }
            if (!$viewprofile) {
                $viewprofile = user_can_view_profile($user, null, $usercontext);
            }
        }

        // Now, conditionally add the user node.
        if ($viewprofile) {
            $canseefullname = has_capability('moodle/site:viewfullnames', $systemcontext);
            $usernode = $usersnode->add(fullname($user, $canseefullname), $userviewurl, self::TYPE_USER, null, 'user' . $user->id);
        } else {
            $usernode = $usersnode->add(get_string('user'));
        }

        if ($this->page->context->contextlevel == CONTEXT_USER && $user->id == $this->page->context->instanceid) {
            $usernode->make_active();
        }

        // Add user information to the participants or user node.
        // If the user is the current user or has permission to view the details of the requested
        // user than add a view profile link.
        if (
            $iscurrentuser || has_capability('moodle/user:viewdetails', $systemcontext) ||
                has_capability('moodle/user:viewdetails', $usercontext)
        ) {
            $usernode->add(get_string('viewprofile'), new url('/user/profile.php', ['id' => $user->id]));
        }

        // Add the messages link.
        if (!empty($CFG->messaging)) {
            $messageargs = ['user1' => $USER->id];
            if ($USER->id != $user->id) {
                $messageargs['user2'] = $user->id;
            }
            $url = new url('/message/index.php', $messageargs);
            $usernode->add(get_string('messages', 'message'), $url, self::TYPE_SETTING, null, 'messages');
        }

        // Add the "My private files" link.
        if ($iscurrentuser && has_capability('moodle/user:manageownfiles', $usercontext)) {
            $url = new url('/user/files.php');
            $usernode->add(get_string('privatefiles'), $url, self::TYPE_SETTING, null, 'privatefiles');
        }

        // Let plugins hook into user navigation.
        $pluginsfunction = get_plugins_with_function('extend_navigation_user', 'lib.php');
        foreach ($pluginsfunction as $plugintype => $plugins) {
            if ($plugintype != 'report') {
                foreach ($plugins as $pluginfunction) {
                    $pluginfunction($usernode, $user, $usercontext, null, $systemcontext);
                }
            }
        }

        return true;
    }

    /**
     * Extends the navigation for the given user.
     *
     * @param stdClass $user A user from the database
     */
    public function extend_for_user($user) {
        $this->extendforuser[] = $user;
    }

    /**
     * Returns all of the users the navigation is being extended for
     *
     * @return array An array of extending users.
     */
    public function get_extending_users() {
        return $this->extendforuser;
    }

    /**
     * Clears the navigation cache
     */
    public function clear_cache() {
        $this->cache->clear();
    }

    /**
     * Sets an expansion limit for the navigation
     *
     * The expansion limit is used to prevent the display of content that has a type
     * greater than the provided $type.
     *
     * Can be used to ensure things such as activities or activity content don't get
     * shown on the navigation.
     * They are still generated in order to ensure the navbar still makes sense.
     *
     * @param int $type One of navigation_node::TYPE_*
     * @return bool true when complete.
     */
    public function set_expansion_limit($type) {
        $nodes = $this->find_all_of_type($type);

        // We only want to hide specific types of nodes.
        // Only nodes that represent "structure" in the navigation tree should be hidden.
        // If we hide all nodes then we risk hiding vital information.
        $typestohide = [
            self::TYPE_CATEGORY,
            self::TYPE_SECTION,
        ];

        foreach ($nodes as $node) {
            foreach ($node->children as $child) {
                $child->hide($typestohide);
            }
        }
        return true;
    }

    #[\Override]
    public function get($key, $type = null) {
        if (!$this->initialised) {
            $this->initialise();
        }
        return parent::get($key, $type);
    }

    #[\Override]
    public function find($key, $type) {
        if (!$this->initialised) {
            $this->initialise();
        }
        if ($type == self::TYPE_ROOTNODE && array_key_exists($key, $this->rootnodes)) {
            return $this->rootnodes[$key];
        }
        return parent::find($key, $type);
    }
}

// Alias this class to the old name.
// This file will be autoloaded by the legacyclasses autoload system.
// In future all uses of this class will be corrected and the legacy references will be removed.
class_alias(global_navigation::class, \global_navigation::class);
