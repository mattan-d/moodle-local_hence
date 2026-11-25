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
 * English strings.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Hence';
$string['hence:view'] = 'View the Hence test record';
$string['privacy:metadata'] = 'Hence reads assignment submissions and grades that Moodle already stores. It does not keep its own copy.';

$string['testbanner'] = 'New facts are sent to Hence every 4 hours. This page is still the whole record: every fact, every claim, and what is waiting to go out.';
$string['rule'] = 'A claim is believed only when there are at least two facts, they span {$a} days, and the belief is at least 0.75. One fact stays a signal. The finding is then “not yet”.';
$string['summary'] = 'What Moodle can send';
$string['peoplecount'] = 'People with facts';
$string['evidencecount'] = 'Facts';
$string['coursecount'] = 'Courses';
$string['submissioncount'] = 'Submissions';
$string['gradecount'] = 'Grades';

$string['people'] = 'People';
$string['person'] = 'Person';
$string['email'] = 'Email';
$string['facts'] = 'Facts';
$string['open'] = 'Open';
$string['nobody'] = 'No submissions or grades yet. When a learner submits work or receives a grade, they appear here.';

$string['evidence'] = 'Evidence';
$string['claims'] = 'Claims';
$string['explain'] = 'Explanation';
$string['payload'] = 'What would be exchanged with Hence';
$string['postevidence'] = 'POST /evidence';
$string['getclaims'] = 'GET /claims';
$string['getexplain'] = 'GET /explain';

$string['when'] = 'When';
$string['type'] = 'Type';
$string['source'] = 'Course';
$string['signal'] = 'Fact';
$string['belief'] = 'Belief';
$string['finding'] = 'Finding';
$string['window'] = 'Evidence window';
$string['because'] = 'Because';

$string['type_submission'] = 'Assignment submitted';
$string['type_grade'] = 'Grade recorded';
$string['signal_submission'] = 'Submitted “{$a}”';
$string['signal_grade'] = '{$a->item} · {$a->grade} / {$a->max}';

$string['claimsubmits'] = 'Submits coursework, not only a draft';
$string['claimgraded'] = 'Has graded work on record in their courses';
$string['finding_believe'] = 'Reasonable to believe the claim';
$string['finding_withhold'] = 'Not yet. There is a signal, not enough evidence';
$string['spandays'] = '{$a} days';
$string['spanoneday'] = '1 day';
$string['spannone'] = 'A single moment';
$string['inputs'] = '{$a->count} facts · {$a->courses} courses · {$a->span}';

$string['settings'] = 'Hence settings';
$string['scheduleheading'] = 'Sending';
$string['schedule_desc'] = 'The scheduled task sends new facts once every 4 hours. That is the default. Change it under Site administration > Server > Scheduled tasks.';
$string['endpoint'] = 'Hence address';
$string['endpoint_desc'] = 'Where new facts are posted.';
$string['secret'] = 'Shared secret';
$string['secret_desc'] = 'Sent in the X-Hence-Evidence-Secret header. The same value has to be on the Hence server.';
$string['tasksend'] = 'Send new facts to Hence';
$string['sendheading'] = 'Sending to Hence';
$string['sendlast'] = 'Last accepted send: {$a}';
$string['sendnever'] = 'Nothing has been accepted yet.';
$string['sendcount'] = '{$a} facts in the last accepted send';
$string['sendpending'] = '{$a} facts waiting';
$string['sendnext'] = 'Next scheduled send: {$a}';
$string['sendnow'] = 'Send now';
$string['sendfailed'] = 'Hence did not accept the batch.';
$string['senderror_notconfigured'] = 'Set the Hence address and the shared secret before facts can be sent.';
$string['senderror_network'] = 'Hence could not be reached.';
$string['senderror_encode'] = 'The batch could not be encoded.';
$string['senderror_http'] = 'Hence did not accept the last batch ({$a}).';

$string['privacy:metadata:sent'] = 'The id of a fact Hence has already accepted, so it is not sent again.';
$string['privacy:metadata:sent:factid'] = 'The fact id.';
$string['privacy:metadata:sent:occurred'] = 'When the fact happened in Moodle.';
$string['privacy:metadata:hence'] = 'Facts are sent to Hence.';
$string['privacy:metadata:hence:person'] = 'The Moodle user id the fact is about.';
$string['privacy:metadata:hence:course'] = 'The course the fact came from.';
$string['privacy:metadata:hence:signal'] = 'What happened: a submission or a grade.';
