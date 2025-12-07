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
 * Stub file for badges library.
 * Badge functionality has been removed from this installation.
 *
 * @package    core
 * @subpackage badges
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Badge constants - kept for compatibility.
define('BADGE_PERPAGE', 50);
define('BADGE_CRITERIA_AGGREGATION_ALL', 1);
define('BADGE_CRITERIA_AGGREGATION_ANY', 2);
define('BADGE_STATUS_INACTIVE', 0);
define('BADGE_STATUS_ACTIVE', 1);
define('BADGE_STATUS_ACTIVE_LOCKED', 2);
define('BADGE_STATUS_INACTIVE_LOCKED', 4);
define('BADGE_TYPE_SITE', 1);
define('BADGE_TYPE_COURSE', 2);
define('BADGE_MESSAGE_NEVER', 0);
define('BADGE_MESSAGE_ALWAYS', 1);
define('BADGE_MESSAGE_DAILY', 2);
define('BADGE_MESSAGE_WEEKLY', 3);
define('BADGE_MESSAGE_MONTHLY', 4);
define('BADGE_BACKPACKAPIURL', 'https://api.badgr.io/v2');
define('BADGE_BACKPACKWEBURL', 'https://badgr.io');
define('BACKPACK_MOVE_UP', -1);
define('BACKPACK_MOVE_DOWN', 1);

/**
 * Stub - Check if badges are enabled.
 *
 * @return bool Always returns false
 */
function badges_enabled(): bool {
    return false;
}

/**
 * Stub - Get badges for user.
 *
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @param int $page Page number
 * @param int $perpage Items per page
 * @param string $search Search string
 * @param bool $onlypublic Only public badges
 * @return array Empty array
 */
function badges_get_badges($type, $courseid = 0, $sort = '', $dir = '', $page = 0, $perpage = BADGE_PERPAGE, $user = 0) {
    return [];
}

/**
 * Stub - Get user badges.
 *
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @param int $page Page
 * @param int $perpage Per page
 * @param string $search Search
 * @param bool $onlypublic Public only
 * @return array Empty array
 */
function badges_get_user_badges($userid, $courseid = 0, $page = 0, $perpage = 0, $search = '', $onlypublic = false) {
    return [];
}

/**
 * Stub - Get badge by ID.
 *
 * @param int $badgeid Badge ID
 * @return bool|stdClass Returns false
 */
function badges_get_badge($badgeid) {
    return false;
}

/**
 * Stub - Check user can earn badge.
 *
 * @param object $badge Badge object
 * @param int $userid User ID
 * @return bool Always returns false
 */
function badges_user_can_earn_badge($badge, $userid = 0) {
    return false;
}

/**
 * Stub - Delete badge.
 *
 * @param object $badge Badge
 */
function badges_delete_badge($badge) {
    // No-op.
}

/**
 * Stub - Award badge.
 *
 * @param int $badgeid Badge ID
 * @param int $userid User ID
 */
function badges_award_badge($badgeid, $userid) {
    // No-op.
}

/**
 * Stub - Revoke badge.
 *
 * @param int $badgeid Badge ID
 * @param int $userid User ID
 */
function badges_revoke_badge($badgeid, $userid) {
    // No-op.
}

/**
 * Stub - Get badge image URL.
 *
 * @param object $badge Badge
 * @return moodle_url Empty URL
 */
function badges_get_badge_image_url($badge) {
    return new moodle_url('/');
}

/**
 * Stub - Bake badge.
 *
 * @param int $hash Hash
 * @param int $badgeid Badge ID
 * @param int $userid User ID
 * @param bool $pathhash Path hash
 */
function badges_bake($hash, $badgeid, $userid = 0, $pathhash = false) {
    // No-op.
}

/**
 * Stub - Get backpack settings.
 *
 * @param int $userid User ID
 * @return bool|null Returns null
 */
function badges_get_user_backpack($userid = 0) {
    return null;
}

/**
 * Stub - Get available backpacks.
 *
 * @return array Empty array
 */
function badges_get_site_backpacks() {
    return [];
}

/**
 * Stub - Local backpack JS.
 */
function badges_local_backpack_js($checkaliases = true) {
    // No-op.
}

/**
 * Stub - Setup backpack JS.
 *
 * @param int $userid User ID
 */
function badges_setup_backpack_js($userid = 0) {
    // No-op.
}

/**
 * Stub class for badge.
 */
class badge {
    public $id;
    public $name;
    public $description;
    public $timecreated;
    public $timemodified;
    public $usercreated;
    public $usermodified;
    public $issuername;
    public $issuerurl;
    public $issuercontact;
    public $expiredate;
    public $expireperiod;
    public $type;
    public $courseid;
    public $message;
    public $messagesubject;
    public $attachment;
    public $notification;
    public $status;
    public $nextcron;
    public $version;
    public $language;
    public $imageauthorname;
    public $imageauthoremail;
    public $imageauthorurl;
    public $imagecaption;

    public function __construct($badgeid) {
        $this->id = $badgeid;
    }

    public function is_active() {
        return false;
    }

    public function is_locked() {
        return false;
    }

    public function is_issued($userid) {
        return false;
    }

    public function get_criteria() {
        return [];
    }

    public function get_criteria_completions($userid) {
        return [];
    }

    public function has_criteria() {
        return false;
    }

    public function has_awards() {
        return false;
    }

    public function get_awards() {
        return [];
    }

    public function issue($userid, $nobake = false) {
        return false;
    }

    public function delete($archive = true) {
        // No-op.
    }

    public function save() {
        return false;
    }
}
