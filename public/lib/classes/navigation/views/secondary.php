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

namespace core\navigation\views;

use navigation_node;
use url_select;
use settings_navigation;

/**
 * Class secondary_navigation_view.
 *
 * The secondary navigation view is a stripped down tweaked version of the
 * settings_navigation/navigation
 *
 * @package     core
 * @category    navigation
 * @copyright   2021 onwards Peter Dias
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class secondary extends view {
    /** @var string $headertitle The header for this particular menu*/
    public $headertitle;

    /** @var int The maximum limit of navigation nodes displayed in the secondary navigation */
    const MAX_DISPLAYED_NAV_NODES = 5;

    /** @var string The key of the node to set as selected in the overflow menu, if explicitly set by a page. */
    protected $overflowselected = null;

    /**
     * Get default admin more menu nodes.
     *
     * @return array
     */
    protected function get_default_admin_more_menu_nodes(): array {
        return [];
    }

    /**
     * Initialises the secondary navigation.
     *
     * The secondary nav is only considered for the following pages:
     * 1 - Site admin settings
     */
    public function initialise(): void {
        if (during_initial_install() || $this->initialised) {
            return;
        }
        $this->id = 'secondary_navigation';
        $context = $this->context;
        $this->headertitle = get_string('menu');
        $defaultmoremenunodes = [];
        $maxdisplayednodes = self::MAX_DISPLAYED_NAV_NODES;

        switch ($context->contextlevel) {
            case CONTEXT_SYSTEM:
                $this->headertitle = get_string('homeheader');
                $this->load_admin_navigation();
                // If the site administration navigation was generated after load_admin_navigation().
                if ($this->has_children()) {
                    // Do not explicitly limit the number of navigation nodes displayed in the site administration
                    // navigation menu.
                    $maxdisplayednodes = null;
                }
                $defaultmoremenunodes = $this->get_default_admin_more_menu_nodes();
                break;
        }

        $this->remove_unwanted_nodes($this);

        // Force certain navigation nodes to be displayed in the "more" menu.
        $this->force_nodes_into_more_menu($defaultmoremenunodes, $maxdisplayednodes);
        // Search and set the active node.
        $this->scan_for_active_node($this);
        $this->initialised = true;
    }

    /**
     * Returns a node with the action being from the first found child node that has an action (Recursive).
     *
     * @param navigation_node $node The part of the node tree we are checking.
     * @param navigation_node $basenode  The very first node to be used for the return.
     * @return navigation_node|null
     */
    protected function get_node_with_first_action(navigation_node $node, navigation_node $basenode): ?navigation_node {
        $newnode = null;
        if (!$node->has_children()) {
            return null;
        }

        // Find the first child with an action and update the main node.
        foreach ($node->children as $child) {
            if ($child->has_action()) {
                $newnode = $basenode;
                $newnode->action = $child->action;
                return $newnode;
            }
        }
        if (is_null($newnode)) {
            // Check for children and go again.
            foreach ($node->children as $child) {
                if ($child->has_children()) {
                    $newnode = $this->get_node_with_first_action($child, $basenode);

                    if (!is_null($newnode)) {
                        return $newnode;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Gets the first action from the first node that has an action.
     *
     * @param navigation_node $node  The navigation node to search.
     * @return navigation_node|null
     */
    protected function get_first_action_for_node(navigation_node $node): ?navigation_node {
        if ($node->has_action()) {
            return $node;
        }
        if ($node->has_children()) {
            foreach ($node->children as $child) {
                $firstaction = $this->get_first_action_for_node($child);
                if (!is_null($firstaction)) {
                    return $firstaction;
                }
            }
        }
        return null;
    }

    /**
     * Add external nodes to secondary navigation.
     *
     * @param navigation_node $node The external node.
     * @param navigation_node $basenode The base node.
     * @param ?navigation_node $root The root node to add to.
     */
    protected function add_external_nodes_to_secondary(navigation_node $node, navigation_node $basenode,
            ?navigation_node $root = null): void {
        $newnode = null;
        $root = $root ?? $this;

        foreach ($node->children as $child) {
            if (!$child->display) {
                continue;
            }

            if ($child->has_action()) {
                $root->add_node($child);
            } else if ($child->has_children()) {
                // If the child has grandchildren, check to see if any are active and add the first node that has an action.
                foreach ($child->children as $grandchild) {
                    $hasaction = $this->get_first_action_for_node($grandchild);
                    if (!is_null($hasaction)) {
                        $newnode = $this->get_node_with_first_action($grandchild, $grandchild);
                        $root->add_node($newnode);
                    }
                }
            }
        }
    }

    /**
     * Checks if the provided node matches the current URL.
     *
     * @param navigation_node $node The node to check.
     * @return navigation_node|null
     */
    protected function nodes_match_current_url(navigation_node $node): ?navigation_node {
        $pageurl = $this->page->url;
        if ($node->has_action()) {
            $nodeaction = $node->action();
            if ($nodeaction->compare($pageurl, URL_MATCH_BASE)) {
                return $node;
            }
        }

        foreach ($node->children as $child) {
            $matchingchild = $this->nodes_match_current_url($child);
            if (!is_null($matchingchild)) {
                return $matchingchild;
            }
        }
        return null;
    }

    /**
     * Checks if the provided node matches a given key string.
     *
     * @param navigation_node $node The node to check.
     * @param string $key The key to match.
     * @return navigation_node|null
     */
    protected function node_matches_key_string(navigation_node $node, string $key): ?navigation_node {
        if ($node->key == $key) {
            return $node;
        }
        foreach ($node->children as $child) {
            $matchingchild = $this->node_matches_key_string($child, $key);
            if (!is_null($matchingchild)) {
                return $matchingchild;
            }
        }
        return null;
    }

    /**
     * Sets a node key to be marked as selected in the overflow menu.
     *
     * @param string $nodekey The key of the node to mark as selected.
     */
    public function set_overflow_selected_node(string $nodekey): void {
        $this->overflowselected = $nodekey;
    }

    /**
     * Get data for the overflow menu.
     *
     * @return url_select|null
     */
    public function get_overflow_menu_data(): ?url_select {
        return null;
    }

    /**
     * Load the admin navigation.
     */
    protected function load_admin_navigation(): void {
        global $PAGE, $SITE;

        $settingsnav = $this->page->settingsnav;
        $node = $settingsnav->find('root', self::TYPE_SITE_ADMIN);

        if (!$node || !$node->has_children()) {
            return;
        }

        $this->add_external_nodes_to_secondary($node, $node);
    }

    /**
     * Adds ordered nodes to the navigation.
     *
     * @param array $nodes The nodes to add.
     * @param ?navigation_node $rootnode The root node to add to.
     */
    protected function add_ordered_nodes(array $nodes, ?navigation_node $rootnode = null): void {
        $rootnode = $rootnode ?? $this;
        foreach ($nodes as $key => $node) {
            $rootnode->add_node($node);
        }
    }

    /**
     * Force nodes into the more menu.
     *
     * @param array $defaultmoremenunodes Default more menu nodes.
     * @param ?int $maxdisplayednodes Maximum displayed nodes.
     */
    protected function force_nodes_into_more_menu(array $defaultmoremenunodes = [], ?int $maxdisplayednodes = null) {
        // Check if we have enough nodes to display.
        if ($this->children->count() <= 1) {
            return;
        }

        $this->moremenulabel = get_string('moremenu');

        $displayednodecount = 0;
        foreach ($this->children as $child) {
            if (is_null($maxdisplayednodes) || $displayednodecount < $maxdisplayednodes) {
                if (in_array($child->key, $defaultmoremenunodes)) {
                    $child->set_force_into_more_menu(true);
                } else {
                    $displayednodecount++;
                }
            } else {
                $child->set_force_into_more_menu(true);
            }
        }
    }

    /**
     * Remove unwanted nodes from the navigation.
     *
     * @param navigation_node $node The node to remove unwanted children from.
     */
    protected function remove_unwanted_nodes(navigation_node $node) {
        foreach ($node->children as $child) {
            if (!$child->display) {
                $child->remove();
            }
        }
    }

    /**
     * Create a menu element from navigation nodes.
     *
     * @param array $navigationnodes The navigation nodes.
     * @param bool $forceheadings Force headings.
     * @return array|null
     */
    public static function create_menu_element(array $navigationnodes, bool $forceheadings = false): ?array {
        if (empty($navigationnodes)) {
            return null;
        }

        $menu = [];
        foreach ($navigationnodes as $navnode) {
            if (!$navnode->has_action()) {
                continue;
            }
            $menu[] = [
                'text' => self::format_node_text($navnode),
                'url' => $navnode->action(),
            ];
        }

        if (empty($menu)) {
            return null;
        }

        return $menu;
    }

    /**
     * Format node text for display.
     *
     * @param navigation_node $navigationnode The navigation node.
     * @return string
     */
    protected static function format_node_text(navigation_node $navigationnode): string {
        return $navigationnode->text;
    }
}
