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
 * The signed-in person's permanent Hence id.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_hence\person;

require_login();
if (isguestuser()) {
    throw new moodle_exception('noguest');
}

$userid = optional_param('userid', 0, PARAM_INT);
if (!$userid) {
    $userid = (int) $USER->id;
}
if ($userid !== (int) $USER->id) {
    require_capability('local/hence:view', context_system::instance());
}

$url = new moodle_url('/local/hence/my.php', ['userid' => $userid]);
$PAGE->set_context(context_user::instance($userid));
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('myid', 'local_hence'));
$PAGE->set_heading(get_string('myid', 'local_hence'));
$PAGE->requires->css(new moodle_url('/local/hence/styles.css'));

$publicid = person::public_id($userid);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('myid', 'local_hence'));
echo html_writer::start_div('hence-banner');
echo html_writer::tag('p', get_string('myid_desc', 'local_hence'));
echo html_writer::tag('p', s($publicid), ['class' => 'hence-id']);
echo html_writer::end_div();
echo $OUTPUT->footer();
