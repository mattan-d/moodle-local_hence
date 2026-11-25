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
 * Test engine. Hence has no live inference yet, so the numbers are computed here and shown in full.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hence;

/**
 * Turns Moodle facts into claims and explanations.
 *
 * A claim is believed only when there are at least two facts, they span 14 days,
 * and the belief is at least 0.75. Otherwise the finding is withhold.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class engine {

    /** @var float Belief at or above this, with enough span, is a belief. */
    public const BELIEF_LINE = 0.75;

    /** @var int Days of evidence required before a claim can be believed. */
    public const SPAN_DAYS = 14;

    /**
     * Claims for one person's facts.
     *
     * @param array<int,array<string,mixed>> $facts
     * @return array<int,array<string,mixed>>
     */
    public static function claims(array $facts): array {
        $claims = [];
        $submissions = array_values(array_filter($facts, static fn(array $fact): bool => $fact['kind'] === 'submission'));
        $grades = array_values(array_filter($facts, static fn(array $fact): bool => $fact['kind'] === 'grade'));

        if ($submissions) {
            $claims[] = self::claim('submission', 'claimsubmits', $submissions);
        }
        if ($grades) {
            $claims[] = self::claim('grade', 'claimgraded', $grades);
        }
        return $claims;
    }

    /**
     * The JSON Hence would exchange for this person, while the service is still a test.
     *
     * @param int $userid
     * @param array<int,array<string,mixed>> $facts
     * @param array<int,array<string,mixed>> $claims
     * @return array{evidence:array,claims:array,explain:array}
     */
    public static function payloads(int $userid, array $facts, array $claims): array {
        $person = 'user_' . $userid;
        $evidence = [];
        foreach ($facts as $fact) {
            $metadata = [
                'course' => $fact['source'],
                'signal' => $fact['signal'],
            ];
            if ($fact['kind'] === 'grade') {
                $metadata['grade'] = $fact['grade'];
                $metadata['max'] = $fact['max'];
            }
            $evidence[] = [
                'person' => $person,
                'source' => 'Moodle',
                'type' => $fact['type'],
                'occurred_at' => gmdate('c', (int) $fact['occurred']),
                'metadata' => $metadata,
            ];
        }

        $claimrows = [];
        $explain = [];
        foreach ($claims as $claim) {
            $claimrows[] = [
                'statement' => $claim['statement'],
                'belief' => $claim['belief'],
                'finding' => $claim['finding'],
            ];
            $because = [];
            foreach ($claim['facts'] as $fact) {
                $because[] = [
                    'source' => $fact['source'],
                    'signal' => self::signal_line($fact),
                ];
            }
            $explain[] = [
                'claim' => $claim['statement'],
                'belief' => $claim['belief'],
                'finding' => $claim['finding'],
                'because' => $because,
            ];
        }

        return [
            'evidence' => [
                'person' => $person,
                'evidence' => $evidence,
            ],
            'claims' => [
                'person' => $person,
                'claims' => $claimrows,
            ],
            'explain' => $explain,
        ];
    }

    /**
     * One claim from a set of facts of the same kind.
     *
     * @param string $kind
     * @param string $stringid
     * @param array<int,array<string,mixed>> $facts
     * @return array<string,mixed>
     */
    private static function claim(string $kind, string $stringid, array $facts): array {
        $times = array_map(static fn(array $fact): int => (int) $fact['occurred'], $facts);
        $courses = [];
        foreach ($facts as $fact) {
            $courses[(int) $fact['courseid']] = true;
        }
        $count = count($facts);
        $coursecount = count($courses);
        $span = 0;
        if ($count > 1) {
            $span = (int) floor((max($times) - min($times)) / DAYSECS);
        }

        $belief = 0.42 + (min($count, 6) * 0.08) + (min($coursecount, 3) * 0.06);
        if ($span >= self::SPAN_DAYS) {
            $belief += 0.12;
        }
        if ($span >= 60) {
            $belief += 0.06;
        }
        if ($count < 2) {
            $belief = min($belief, 0.62);
        }
        $belief = round(min(0.97, $belief), 2);

        $finding = ($belief >= self::BELIEF_LINE && $count >= 2 && $span >= self::SPAN_DAYS)
            ? 'believe'
            : 'withhold';

        return [
            'kind' => $kind,
            'statement' => get_string($stringid, 'local_hence'),
            'belief' => $belief,
            'finding' => $finding,
            'count' => $count,
            'courses' => $coursecount,
            'span' => $span,
            'facts' => $facts,
        ];
    }

    /**
     * One line a person can read under an explanation.
     *
     * @param array<string,mixed> $fact
     * @return string
     */
    public static function signal_line(array $fact): string {
        if ($fact['kind'] === 'grade') {
            return get_string('signal_grade', 'local_hence', (object) [
                'item' => $fact['signal'],
                'grade' => format_float((float) $fact['grade'], 1),
                'max' => format_float((float) $fact['max'], 0),
            ]);
        }
        return get_string('signal_submission', 'local_hence', $fact['signal']);
    }
}
