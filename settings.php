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
 * Site administration entry for the Hence test record.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_hence',
        get_string('pluginname', 'local_hence'),
        new moodle_url('/local/hence/index.php'),
        'local/hence:view'
    ));

    $settings = new admin_settingpage('local_hence_settings', get_string('settings', 'local_hence'));
    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_heading(
            'local_hence/schedule',
            get_string('scheduleheading', 'local_hence'),
            get_string('schedule_desc', 'local_hence')
        ));
        $settings->add(new admin_setting_configtext(
            'local_hence/endpoint',
            get_string('endpoint', 'local_hence'),
            get_string('endpoint_desc', 'local_hence'),
            \local_hence\sender::DEFAULT_ENDPOINT,
            PARAM_URL
        ));
        $domain = \local_hence\registrar::domain();
        \local_hence\registrar::ensure();
        $registered = trim((string) get_config('local_hence', 'domain'));
        $registererror = trim((string) get_config('local_hence', 'register_error'));
        if ($registered === $domain && $domain !== '') {
            $registration = get_string('domain_saved', 'local_hence', $domain);
        } else if ($registererror === 'exists') {
            $registration = get_string('domain_taken', 'local_hence', $domain);
        } else if ($registererror !== '') {
            $registration = get_string('domain_waiting', 'local_hence', $domain);
        } else {
            $registration = get_string('domain_waiting', 'local_hence', $domain);
        }
        $settings->add(new admin_setting_configcheckbox(
            'local_hence/showinmenu',
            get_string('showinmenu', 'local_hence'),
            get_string('showinmenu_desc', 'local_hence'),
            0
        ));
        $settings->add(new admin_setting_heading(
            'local_hence/registration',
            get_string('domain', 'local_hence'),
            $registration
        ));
    }
    $ADMIN->add('localplugins', $settings);
}
