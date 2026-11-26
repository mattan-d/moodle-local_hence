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
 * Asks Hence for this institution's sending secret.
 *
 * The institution is its domain. The secret is stored here and on Hence.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hence;

/**
 * Registers this Moodle domain with Hence.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class registrar {

    /**
     * Host of this Moodle site, which is the customer id on Hence.
     *
     * @return string
     */
    public static function domain(): string {
        global $CFG;

        $host = parse_url((string) $CFG->wwwroot, PHP_URL_HOST);
        if (!is_string($host)) {
            return '';
        }
        return strtolower(rtrim($host, '.'));
    }

    /**
     * The saved secret for this domain, requesting one from Hence when needed.
     *
     * @return string
     */
    public static function ensure(): string {
        $domain = self::domain();
        $secret = trim((string) get_config('local_hence', 'secret'));
        $saved = trim((string) get_config('local_hence', 'domain'));
        if ($domain === '') {
            return '';
        }
        if ($secret !== '' && $saved === $domain) {
            return $secret;
        }
        $attempt = (int) get_config('local_hence', 'register_attempt');
        if ($attempt > time() - 120) {
            return $secret;
        }
        set_config('register_attempt', time(), 'local_hence');

        $issued = self::request($domain);
        if ($issued !== '') {
            set_config('secret', $issued, 'local_hence');
            set_config('domain', $domain, 'local_hence');
            set_config('register_error', '', 'local_hence');
            return $issued;
        }
        return $secret;
    }

    /**
     * Ask Hence to create the secret for this domain.
     *
     * @param string $domain
     * @return string
     */
    private static function request(string $domain): string {
        global $CFG;

        $endpoint = self::register_endpoint();
        $payload = json_encode(['domain' => $domain], JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            set_config('register_error', 'encode', 'local_hence');
            return '';
        }

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        $curl->setHeader([
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        $curl->setopt([
            'CURLOPT_TIMEOUT' => 20,
            'CURLOPT_CONNECTTIMEOUT' => 8,
            'CURLOPT_IPRESOLVE' => CURL_IPRESOLVE_V4,
        ]);
        try {
            $response = $curl->post($endpoint, $payload);
        } catch (\Throwable $e) {
            set_config('register_error', 'network', 'local_hence');
            return '';
        }
        $status = (int) ($curl->get_info()['http_code'] ?? 0);
        $decoded = is_string($response) ? json_decode($response, true) : null;
        if ($status === 200 && is_array($decoded) && !empty($decoded['ok'])
                && (string) ($decoded['domain'] ?? '') === $domain
                && preg_match('/^[a-f0-9]{64}$/', (string) ($decoded['secret'] ?? ''))) {
            return (string) $decoded['secret'];
        }
        if ($status === 409) {
            set_config('register_error', 'exists', 'local_hence');
            return '';
        }
        if ($status === 0) {
            set_config('register_error', 'network', 'local_hence');
            return '';
        }
        set_config('register_error', 'http-' . $status, 'local_hence');
        return '';
    }

    /**
     * Registration lives next to the evidence address.
     *
     * @return string
     */
    public static function register_endpoint(): string {
        $endpoint = sender::endpoint();
        if (str_ends_with($endpoint, '/api/evidence')) {
            return substr($endpoint, 0, -strlen('evidence')) . 'register';
        }
        return 'https://hence.today/api/register';
    }
}
