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
 * Library hooks for local_hence.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Link to the Hence id from a user's preferences.
 *
 * @param navigation_node $navigation
 * @param stdClass $user
 * @param context_user $usercontext
 * @param stdClass $course
 * @param context_course $coursecontext
 * @return void
 */
function local_hence_extend_navigation_user_settings(navigation_node $navigation, $user, $usercontext, $course, $coursecontext) {
    global $USER;

    if (!isloggedin() || isguestuser($user)) {
        return;
    }
    $ownpage = (int) $USER->id === (int) $user->id;
    if (!$ownpage && !has_capability('local/hence:view', context_system::instance())) {
        return;
    }
    $navigation->add(
        get_string('myid', 'local_hence'),
        new moodle_url('/local/hence/my.php', ['userid' => $user->id]),
        navigation_node::TYPE_SETTING
    );
}
