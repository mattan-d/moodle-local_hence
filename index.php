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
 * Hence test record. While Hence is in test, this admin page shows every fact,
 * claim, and explanation instead of sending them to a live service.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_hence\collector;
use local_hence\engine;
use local_hence\sender;

require_login();

$userid = optional_param('userid', 0, PARAM_INT);
$sendnow = optional_param('sendnow', 0, PARAM_BOOL);

$url = new moodle_url('/local/hence/index.php');
if ($userid) {
    $url->param('userid', $userid);
}

$PAGE->set_context(context_system::instance());
admin_externalpage_setup('local_hence');
$PAGE->set_url($url);
$PAGE->requires->css(new moodle_url('/local/hence/styles.css'));

if ($sendnow) {
    require_sesskey();
    sender::send();
    redirect(new moodle_url('/local/hence/index.php'));
}

$summary = collector::summary();
$people = collector::people();
$selected = null;
foreach ($people as $person) {
    if ($person['id'] === $userid) {
        $selected = $person;
        break;
    }
}
if ($selected === null && $people) {
    $selected = $people[0];
    $userid = $selected['id'];
}

$facts = $userid ? collector::facts_for($userid) : [];
$claims = engine::claims($facts);
$payloads = $userid ? engine::payloads($userid, $facts, $claims) : [];

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_hence'));

echo html_writer::start_div('hence-banner');
echo html_writer::tag('p', get_string('testbanner', 'local_hence'));
echo html_writer::tag('p', get_string('rule', 'local_hence', engine::SPAN_DAYS), ['class' => 'hence-rule']);
echo html_writer::end_div();

$lasterror = (string) get_config('local_hence', 'lasterror');
$lastsuccess = (int) get_config('local_hence', 'lastsuccess');
$lastcount = (int) get_config('local_hence', 'lastcount');
$pendingcount = count(sender::pending());
$task = \core\task\manager::get_scheduled_task(\local_hence\task\send_evidence::class);
$nextrun = ($task && $task->get_next_run_time()) ? userdate($task->get_next_run_time()) : '';

echo $OUTPUT->heading(get_string('sendheading', 'local_hence'), 3);
echo html_writer::start_div('hence-banner');
echo html_writer::tag('p', get_string('schedule_desc', 'local_hence'));
if ($lastsuccess) {
    echo html_writer::tag('p', get_string('sendlast', 'local_hence', userdate($lastsuccess)));
    echo html_writer::tag('p', get_string('sendcount', 'local_hence', $lastcount));
} else {
    echo html_writer::tag('p', get_string('sendnever', 'local_hence'));
}
echo html_writer::tag('p', get_string('sendpending', 'local_hence', $pendingcount));
if ($nextrun !== '') {
    echo html_writer::tag('p', get_string('sendnext', 'local_hence', $nextrun));
}
if ($lasterror !== '') {
    $errorkey = in_array($lasterror, ['notconfigured', 'network', 'encode'], true) ? $lasterror : 'http';
    echo html_writer::tag('p', get_string('senderror_' . $errorkey, 'local_hence', $lasterror), ['class' => 'hence-finding-withhold']);
}
echo $OUTPUT->single_button(
    new moodle_url('/local/hence/index.php'),
    get_string('sendnow', 'local_hence'),
    'post',
    ['params' => ['sendnow' => 1]]
);
echo html_writer::end_div();

echo $OUTPUT->heading(get_string('summary', 'local_hence'), 3);
echo html_writer::start_div('hence-stats');
foreach ([
    'people' => 'peoplecount',
    'evidence' => 'evidencecount',
    'courses' => 'coursecount',
    'submissions' => 'submissioncount',
    'grades' => 'gradecount',
] as $key => $stringid) {
    echo html_writer::start_div('hence-stat');
    echo html_writer::tag('b', (string) $summary[$key]);
    echo html_writer::tag('span', get_string($stringid, 'local_hence'));
    echo html_writer::end_div();
}
echo html_writer::end_div();

echo $OUTPUT->heading(get_string('people', 'local_hence'), 3);
if (!$people) {
    echo $OUTPUT->notification(get_string('nobody', 'local_hence'), 'info');
} else {
    $table = new html_table();
    $table->head = [
        get_string('person', 'local_hence'),
        get_string('email', 'local_hence'),
        get_string('facts', 'local_hence'),
        '',
    ];
    $table->attributes['class'] = 'generaltable';
    foreach ($people as $person) {
        $link = new moodle_url('/local/hence/index.php', ['userid' => $person['id']]);
        $name = html_writer::link($link, s($person['fullname']));
        if ($selected && $person['id'] === $selected['id']) {
            $name = html_writer::tag('strong', $name);
        }
        $table->data[] = [
            $name,
            s($person['email']),
            $person['facts'],
            html_writer::link($link, get_string('open', 'local_hence')),
        ];
    }
    echo html_writer::table($table);
}

if ($selected) {
    echo $OUTPUT->heading(s($selected['fullname']), 3);

    echo $OUTPUT->heading(get_string('evidence', 'local_hence'), 4);
    $evidencetable = new html_table();
    $evidencetable->head = [
        get_string('when', 'local_hence'),
        get_string('type', 'local_hence'),
        get_string('source', 'local_hence'),
        get_string('signal', 'local_hence'),
    ];
    foreach ($facts as $fact) {
        $type = $fact['kind'] === 'grade'
            ? get_string('type_grade', 'local_hence')
            : get_string('type_submission', 'local_hence');
        $evidencetable->data[] = [
            userdate((int) $fact['occurred']),
            $type,
            s($fact['source']),
            s(engine::signal_line($fact)),
        ];
    }
    echo html_writer::table($evidencetable);

    echo $OUTPUT->heading(get_string('claims', 'local_hence'), 4);
    foreach ($claims as $claim) {
        $span = get_string('spannone', 'local_hence');
        if ($claim['span'] === 1) {
            $span = get_string('spanoneday', 'local_hence');
        } else if ($claim['span'] > 1) {
            $span = get_string('spandays', 'local_hence', $claim['span']);
        }
        $findingclass = $claim['finding'] === 'believe' ? 'hence-finding-believe' : 'hence-finding-withhold';
        $finding = get_string('finding_' . $claim['finding'], 'local_hence');

        echo html_writer::start_div('hence-claim');
        echo html_writer::start_tag('header');
        echo html_writer::tag('strong', s($claim['statement']));
        echo html_writer::tag('span', s((string) $claim['belief']), ['class' => 'hence-belief']);
        echo html_writer::end_tag('header');
        echo html_writer::tag('p', s($finding), ['class' => 'hence-meta ' . $findingclass]);
        echo html_writer::tag('p', s(get_string('inputs', 'local_hence', (object) [
            'count' => $claim['count'],
            'courses' => $claim['courses'],
            'span' => $span,
        ])), ['class' => 'hence-meta']);

        echo html_writer::tag('p', get_string('because', 'local_hence'), ['class' => 'hence-meta']);
        $items = '';
        foreach ($claim['facts'] as $fact) {
            $items .= html_writer::tag('li', s($fact['source'] . ' — ' . engine::signal_line($fact)));
        }
        echo html_writer::tag('ul', $items, ['class' => 'hence-because']);
        echo html_writer::end_div();
    }

    echo $OUTPUT->heading(get_string('payload', 'local_hence'), 4);
    $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    foreach ([
        'postevidence' => $payloads['evidence'],
        'getclaims' => $payloads['claims'],
        'getexplain' => $payloads['explain'],
    ] as $label => $payload) {
        echo html_writer::start_div('hence-json');
        echo html_writer::tag('h5', get_string($label, 'local_hence'));
        echo html_writer::tag('pre', s(json_encode($payload, $flags)));
        echo html_writer::end_div();
    }
}

echo $OUTPUT->footer();
