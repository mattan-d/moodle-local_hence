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
 * Hook callbacks.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hence;

/**
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Add the person's Hence id to the user menu.
     *
     * @param \core_user\hook\extend_user_menu $hook
     * @return void
     */
    public static function extend_user_menu(\core_user\hook\extend_user_menu $hook): void {
        if (!isloggedin() || isguestuser()) {
            return;
        }
        if (!(int) get_config('local_hence', 'showinmenu')) {
            return;
        }
        $item = new \stdClass();
        $item->itemtype = 'link';
        $item->url = new \moodle_url('/local/hence/my.php');
        $item->title = get_string('myid', 'local_hence');
        $item->titleidentifier = 'myid,local_hence';
        $hook->add_navitem($item);
    }
}
