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
 * Posts new Moodle facts to Hence.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hence;

/**
 * Sends facts that Hence has not accepted yet.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sender {

    /** @var string Where facts go unless a manager sets another address. */
    public const DEFAULT_ENDPOINT = 'https://hence.today/api/evidence';

    /** @var int Facts in one request. */
    public const MAX_FACTS = 100;

    /** @var int Safety ceiling for one request. A normal bulk of 100 stays under it. */
    public const MAX_BYTES = 262144;

    /** @var int Characters kept from a course or activity name. */
    private const TEXT_LIMIT = 80;

    /**
     * Send pending facts.
     *
     * @return array{count:int,pending:int,error:string}
     */
    public static function send(): array {
        $endpoint = self::endpoint();
        $secret = registrar::ensure();
        $pending = self::pending();
        if ($secret === '') {
            self::remember(0, 'notconfigured');
            return ['count' => 0, 'pending' => count($pending), 'error' => 'notconfigured'];
        }
        if (!$pending) {
            self::remember(0, '');
            return ['count' => 0, 'pending' => 0, 'error' => ''];
        }

        $sent = 0;
        foreach (array_chunk($pending, self::MAX_FACTS) as $chunk) {
            $error = self::post($endpoint, $secret, $chunk);
            if ($error !== '') {
                self::remember($sent, $error);
                return ['count' => $sent, 'pending' => count($pending) - $sent, 'error' => $error];
            }
            self::mark_sent($chunk);
            $sent += count($chunk);
        }
        self::remember($sent, '');
        return ['count' => $sent, 'pending' => 0, 'error' => ''];
    }

    /**
     * The saved address, or the Hence endpoint when none has been saved.
     *
     * @return string
     */
    public static function endpoint(): string {
        $endpoint = trim((string) get_config('local_hence', 'endpoint'));
        return $endpoint !== '' ? $endpoint : self::DEFAULT_ENDPOINT;
    }

    /**
     * Facts whose id is new, or whose time changed since the last accepted send.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function pending(): array {
        global $DB;

        $known = $DB->get_records_menu('local_hence_sent', null, '', 'factid, occurred');
        $pending = [];
        foreach (collector::all_facts() as $fact) {
            $id = (string) $fact['id'];
            if (!array_key_exists($id, $known) || (int) $known[$id] !== (int) $fact['occurred']) {
                $pending[] = $fact;
            }
        }
        return $pending;
    }

    /**
     * @param string $endpoint
     * @param string $secret
     * @param array<int,array<string,mixed>> $facts
     * @return string Empty when Hence accepted the batch.
     */
    private static function post(string $endpoint, string $secret, array $facts): string {
        global $CFG;

        $people = person::public_ids(array_column($facts, 'userid'));
        $payload = self::encode($facts, '', $people);
        if ($payload === null || strlen($payload) > self::MAX_BYTES) {
            return 'encode';
        }

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        $curl->setHeader([
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Hence-Evidence-Secret: ' . $secret,
        ]);
        $curl->setopt([
            'CURLOPT_TIMEOUT' => 20,
            'CURLOPT_CONNECTTIMEOUT' => 5,
        ]);
        $response = $curl->post($endpoint, $payload);
        $status = (int) ($curl->get_info()['http_code'] ?? 0);
        $decoded = is_string($response) ? json_decode($response, true) : null;
        if ($status >= 200 && $status < 300 && is_array($decoded) && !empty($decoded['ok'])) {
            return '';
        }
        if ($status === 0) {
            return 'network';
        }
        return 'http-' . $status;
    }

    /**
     * @param array<int,array<string,mixed>> $facts
     * @param string $standin Used only to measure size before the real ids are loaded.
     * @param array<int,string> $people
     * @return string|null
     */
    private static function encode(array $facts, string $standin = '', array $people = []): ?string {
        global $CFG;

        $evidence = [];
        foreach ($facts as $fact) {
            $person = $people[(int) ($fact['userid'] ?? 0)] ?? $standin;
            $metadata = [
                'course' => self::clip((string) ($fact['source'] ?? '')),
                'signal' => self::clip((string) ($fact['signal'] ?? '')),
            ];
            if (($fact['kind'] ?? '') === 'grade') {
                $metadata['grade'] = $fact['grade'];
                $metadata['max'] = $fact['max'];
            }
            $evidence[] = [
                'id' => $fact['id'],
                'person' => $person,
                'source' => 'Moodle',
                'type' => $fact['type'],
                'occurred_at' => gmdate('c', (int) $fact['occurred']),
                'metadata' => $metadata,
            ];
        }
        $payload = json_encode([
            'domain' => registrar::domain(),
            'site' => $CFG->wwwroot ?? '',
            'sent_at' => gmdate('c'),
            'evidence' => $evidence,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return is_string($payload) ? $payload : null;
    }

    /**
     * @param string $value
     * @return string
     */
    private static function clip(string $value): string {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, self::TEXT_LIMIT);
        }
        return substr($value, 0, self::TEXT_LIMIT);
    }

    /**
     * @param array<int,array<string,mixed>> $facts
     * @return void
     */
    private static function mark_sent(array $facts): void {
        global $DB;

        $now = time();
        foreach ($facts as $fact) {
            $existing = $DB->get_record('local_hence_sent', ['factid' => $fact['id']], 'id');
            if ($existing) {
                $DB->update_record('local_hence_sent', (object) [
                    'id' => $existing->id,
                    'occurred' => (int) $fact['occurred'],
                    'timemodified' => $now,
                ]);
                continue;
            }
            $DB->insert_record('local_hence_sent', (object) [
                'factid' => $fact['id'],
                'occurred' => (int) $fact['occurred'],
                'timecreated' => $now,
                'timemodified' => $now,
            ]);
        }
    }

    /**
     * @param int $count
     * @param string $error
     * @return void
     */
    private static function remember(int $count, string $error): void {
        set_config('lastattempt', time(), 'local_hence');
        set_config('lasterror', $error, 'local_hence');
        if ($error === '') {
            set_config('lastsuccess', time(), 'local_hence');
            if ($count > 0) {
                set_config('lastcount', $count, 'local_hence');
            }
        }
    }
}
