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
 * Privacy provider for local_hence.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hence\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;

/**
 * Records which fact ids were accepted by Hence. The fact id is not a user id.
 * The facts themselves are sent to Hence.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_hence_person', [
            'userid' => 'privacy:metadata:person:userid',
            'publicid' => 'privacy:metadata:person:publicid',
        ], 'privacy:metadata:person');
        $collection->add_database_table('local_hence_sent', [
            'factid' => 'privacy:metadata:sent:factid',
            'occurred' => 'privacy:metadata:sent:occurred',
        ], 'privacy:metadata:sent');
        $collection->add_external_location_link('hence', [
            'person' => 'privacy:metadata:hence:person',
            'course' => 'privacy:metadata:hence:course',
            'signal' => 'privacy:metadata:hence:signal',
        ], 'privacy:metadata:hence');
        return $collection;
    }

    /**
     * The Hence id is stored against the site, because it is not tied to one course.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $contextlist = new contextlist();
        if ($DB->record_exists('local_hence_person', ['userid' => $userid])) {
            $contextlist->add_system_context();
        }
        return $contextlist;
    }

    /**
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if (!$contextlist->get_contextids()) {
            return;
        }
        $record = $DB->get_record('local_hence_person', ['userid' => $contextlist->get_user()->id]);
        if (!$record) {
            return;
        }
        writer::with_context(\context_system::instance())->export_data(
            [get_string('pluginname', 'local_hence')],
            (object) ['publicid' => $record->publicid]
        );
    }

    /**
     * @param \context $context
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context->contextlevel == CONTEXT_SYSTEM) {
            $DB->delete_records('local_hence_person');
        }
    }

    /**
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        if (!$contextlist->get_contextids()) {
            return;
        }
        $DB->delete_records('local_hence_person', ['userid' => $contextlist->get_user()->id]);
    }
}
