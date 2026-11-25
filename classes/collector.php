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
 * Reads the Moodle facts Hence would receive.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hence;

/**
 * Assignment submissions and recorded grades are the facts this site can send.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class collector {

    /**
     * Counts for the test record header.
     *
     * @return array{people:int,evidence:int,courses:int,submissions:int,grades:int}
     */
    public static function summary(): array {
        global $DB;

        $submissions = $DB->count_records('assign_submission', ['status' => 'submitted', 'latest' => 1]);
        $grades = $DB->count_records_sql(
            "SELECT COUNT(1)
               FROM {grade_grades} gg
               JOIN {grade_items} gi ON gi.id = gg.itemid
              WHERE gg.finalgrade IS NOT NULL
                AND gi.itemtype IN ('mod', 'manual')"
        );

        $people = $DB->count_records_sql(
            "SELECT COUNT(1)
               FROM {user} u
              WHERE u.deleted = 0
                AND " . self::person_has_facts_sql('u.id')
        );

        $courses = $DB->count_records_sql(
            "SELECT COUNT(DISTINCT courseid) FROM (
                SELECT a.course AS courseid
                  FROM {assign_submission} s
                  JOIN {assign} a ON a.id = s.assignment
                 WHERE s.status = 'submitted' AND s.latest = 1
                 UNION
                SELECT gi.courseid AS courseid
                  FROM {grade_grades} gg
                  JOIN {grade_items} gi ON gi.id = gg.itemid
                 WHERE gg.finalgrade IS NOT NULL
                   AND gi.itemtype IN ('mod', 'manual')
                   AND gi.courseid > 0
            ) sources"
        );

        return [
            'people' => (int) $people,
            'evidence' => (int) $submissions + (int) $grades,
            'courses' => (int) $courses,
            'submissions' => (int) $submissions,
            'grades' => (int) $grades,
        ];
    }

    /**
     * People who already have at least one fact.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function people(): array {
        global $DB;

        $records = $DB->get_records_sql(
            "SELECT u.id, u.firstname, u.lastname, u.email, u.idnumber
               FROM {user} u
              WHERE u.deleted = 0
                AND " . self::person_has_facts_sql('u.id') . "
           ORDER BY u.lastname ASC, u.firstname ASC, u.id ASC"
        );

        $people = [];
        foreach ($records as $user) {
            $facts = self::facts_for((int) $user->id);
            $people[] = [
                'id' => (int) $user->id,
                'fullname' => fullname($user),
                'email' => (string) $user->email,
                'idnumber' => (string) $user->idnumber,
                'facts' => count($facts),
            ];
        }
        usort($people, static function (array $a, array $b): int {
            return [$b['facts'], $a['fullname']] <=> [$a['facts'], $b['fullname']];
        });
        return $people;
    }

    /**
     * Facts for one person, newest first.
     *
     * @param int $userid
     * @return array<int,array<string,mixed>>
     */
    public static function facts_for(int $userid): array {
        global $DB;

        $facts = [];

        $submissions = $DB->get_records_sql(
            "SELECT s.id, s.timemodified, a.name AS assignment, c.id AS courseid, c.fullname AS course
               FROM {assign_submission} s
               JOIN {assign} a ON a.id = s.assignment
               JOIN {course} c ON c.id = a.course
              WHERE s.userid = :userid
                AND s.status = 'submitted'
                AND s.latest = 1
           ORDER BY s.timemodified DESC",
            ['userid' => $userid]
        );
        foreach ($submissions as $row) {
            $facts[] = [
                'id' => 'submission-' . $row->id,
                'kind' => 'submission',
                'type' => 'AssignmentSubmitted',
                'source' => (string) $row->course,
                'courseid' => (int) $row->courseid,
                'signal' => (string) $row->assignment,
                'occurred' => (int) $row->timemodified,
            ];
        }

        $grades = $DB->get_records_sql(
            "SELECT gg.id, gg.finalgrade, gg.timemodified,
                    gi.itemname, gi.grademax, gi.itemmodule,
                    c.id AS courseid, c.fullname AS course
               FROM {grade_grades} gg
               JOIN {grade_items} gi ON gi.id = gg.itemid
               JOIN {course} c ON c.id = gi.courseid
              WHERE gg.userid = :userid
                AND gg.finalgrade IS NOT NULL
                AND gi.itemtype IN ('mod', 'manual')
           ORDER BY gg.timemodified DESC",
            ['userid' => $userid]
        );
        foreach ($grades as $row) {
            $item = trim((string) $row->itemname);
            if ($item === '') {
                $item = (string) $row->itemmodule;
            }
            $facts[] = [
                'id' => 'grade-' . $row->id,
                'kind' => 'grade',
                'type' => 'GradeRecorded',
                'source' => (string) $row->course,
                'courseid' => (int) $row->courseid,
                'signal' => $item,
                'grade' => (float) $row->finalgrade,
                'max' => (float) $row->grademax,
                'occurred' => (int) $row->timemodified,
            ];
        }

        usort($facts, static function (array $a, array $b): int {
            return $b['occurred'] <=> $a['occurred'];
        });
        return $facts;
    }

    /**
     * Every fact on the site, with the Moodle user id.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function all_facts(): array {
        global $DB;

        $facts = [];
        $submissions = $DB->get_records_sql(
            "SELECT s.id, s.userid, s.timemodified, a.name AS assignment, c.id AS courseid, c.fullname AS course
               FROM {assign_submission} s
               JOIN {assign} a ON a.id = s.assignment
               JOIN {course} c ON c.id = a.course
              WHERE s.status = 'submitted'
                AND s.latest = 1"
        );
        foreach ($submissions as $row) {
            $facts[] = [
                'id' => 'submission-' . $row->id,
                'userid' => (int) $row->userid,
                'kind' => 'submission',
                'type' => 'AssignmentSubmitted',
                'source' => (string) $row->course,
                'courseid' => (int) $row->courseid,
                'signal' => (string) $row->assignment,
                'occurred' => (int) $row->timemodified,
            ];
        }

        $grades = $DB->get_records_sql(
            "SELECT gg.id, gg.userid, gg.finalgrade, gg.timemodified,
                    gi.itemname, gi.grademax, gi.itemmodule,
                    c.id AS courseid, c.fullname AS course
               FROM {grade_grades} gg
               JOIN {grade_items} gi ON gi.id = gg.itemid
               JOIN {course} c ON c.id = gi.courseid
              WHERE gg.finalgrade IS NOT NULL
                AND gi.itemtype IN ('mod', 'manual')"
        );
        foreach ($grades as $row) {
            $item = trim((string) $row->itemname);
            if ($item === '') {
                $item = (string) $row->itemmodule;
            }
            $facts[] = [
                'id' => 'grade-' . $row->id,
                'userid' => (int) $row->userid,
                'kind' => 'grade',
                'type' => 'GradeRecorded',
                'source' => (string) $row->course,
                'courseid' => (int) $row->courseid,
                'signal' => $item,
                'grade' => (float) $row->finalgrade,
                'max' => (float) $row->grademax,
                'occurred' => (int) $row->timemodified,
            ];
        }
        return $facts;
    }

    /**
     * SQL fragment: the user has a submission or a recorded grade.
     *
     * @param string $useridcolumn
     * @return string
     */
    private static function person_has_facts_sql(string $useridcolumn): string {
        return "(
            EXISTS (
                SELECT 1
                  FROM {assign_submission} s
                 WHERE s.userid = {$useridcolumn}
                   AND s.status = 'submitted'
                   AND s.latest = 1
            )
            OR EXISTS (
                SELECT 1
                  FROM {grade_grades} gg
                  JOIN {grade_items} gi ON gi.id = gg.itemid
                 WHERE gg.userid = {$useridcolumn}
                   AND gg.finalgrade IS NOT NULL
                   AND gi.itemtype IN ('mod', 'manual')
            )
        )";
    }
}
