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
 * Scheduled send of new facts to Hence.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hence\task;

use local_hence\sender;

/**
 * Runs every 4 hours by default. A manager can change that under Scheduled tasks.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_evidence extends \core\task\scheduled_task {

    /**
     * @return string
     */
    public function get_name(): string {
        return get_string('tasksend', 'local_hence');
    }

    /**
     * @return void
     */
    public function execute(): void {
        $result = sender::send();
        if ($result['error'] === 'notconfigured') {
            mtrace('Hence secret is not set. No facts were sent.');
            return;
        }
        if ($result['error'] !== '') {
            mtrace('Hence did not accept the batch (' . $result['error'] . '). '
                . $result['count'] . ' facts were sent before it stopped.');
            return;
        }
        if (!empty($result['capped'])) {
            mtrace('Sent ' . $result['count'] . ' facts to Hence. This run stops at '
                . sender::MAX_PER_RUN . '; the rest waits for the next run.');
            return;
        }
        mtrace('Sent ' . $result['count'] . ' facts to Hence. ' . $result['pending'] . ' still waiting.');
    }
}
