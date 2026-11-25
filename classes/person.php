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
 * Lifetime Hence id for a Moodle user.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hence;

/**
 * One permanent id per person. Hence stores this id and not the person's name.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class person {

    /** @var string Letters and digits that stay readable when the id is copied. */
    private const ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    /**
     * The Hence id for this user, created once and never changed.
     *
     * @param int $userid
     * @return string
     */
    public static function public_id(int $userid): string {
        global $DB;

        $existing = $DB->get_field('local_hence_person', 'publicid', ['userid' => $userid]);
        if (is_string($existing) && $existing !== '') {
            return $existing;
        }
        if (!$DB->record_exists('user', ['id' => $userid, 'deleted' => 0])) {
            throw new \moodle_exception('invaliduser');
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $publicid = self::mint();
            if ($DB->record_exists('local_hence_person', ['publicid' => $publicid])) {
                continue;
            }
            try {
                $DB->insert_record('local_hence_person', (object) [
                    'userid' => $userid,
                    'publicid' => $publicid,
                    'timecreated' => time(),
                ]);
                return $publicid;
            } catch (\dml_exception $e) {
                $existing = $DB->get_field('local_hence_person', 'publicid', ['userid' => $userid]);
                if (is_string($existing) && $existing !== '') {
                    return $existing;
                }
            }
        }
        throw new \moodle_exception('invalidrecord', 'error');
    }

    /**
     * Hence ids for many users. Missing ids are created.
     *
     * @param array<int,int|string> $userids
     * @return array<int,string>
     */
    public static function public_ids(array $userids): array {
        global $DB;

        $userids = array_values(array_unique(array_filter(array_map('intval', $userids))));
        if (!$userids) {
            return [];
        }
        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $rows = $DB->get_records_select_menu('local_hence_person', "userid $insql", $params, '', 'userid, publicid');
        $found = [];
        foreach ($rows as $userid => $publicid) {
            $found[(int) $userid] = (string) $publicid;
        }
        foreach ($userids as $userid) {
            if (!isset($found[$userid])) {
                $found[$userid] = self::public_id($userid);
            }
        }
        return $found;
    }

    /**
     * Give every current user an id. The guest user is not a person.
     *
     * @return int How many ids were created.
     */
    public static function ensure_missing(): int {
        global $DB;

        $guestid = (int) guest_user()->id;
        $missing = $DB->get_fieldset_sql(
            "SELECT u.id
               FROM {user} u
          LEFT JOIN {local_hence_person} p ON p.userid = u.id
              WHERE u.deleted = 0
                AND u.id <> :guestid
                AND p.id IS NULL",
            ['guestid' => $guestid]
        );
        $created = 0;
        foreach ($missing as $userid) {
            self::public_id((int) $userid);
            $created++;
        }
        return $created;
    }

    /**
     * A new id, shaped so a person can read it back later.
     *
     * @return string
     */
    private static function mint(): string {
        $length = strlen(self::ALPHABET) - 1;
        $chars = '';
        for ($i = 0; $i < 16; $i++) {
            $chars .= self::ALPHABET[random_int(0, $length)];
        }
        return 'HNC-' . substr($chars, 0, 4) . '-' . substr($chars, 4, 4)
            . '-' . substr($chars, 8, 4) . '-' . substr($chars, 12, 4);
    }
}
