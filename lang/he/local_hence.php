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
 * Hebrew strings.
 *
 * @package    local_hence
 * @copyright  2026 CentricApp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Hence';
$string['hence:view'] = 'צפייה ברשומת הבדיקה של Hence';
$string['privacy:metadata'] = 'Hence קורא הגשות וציונים ש-Moodle כבר שומר. הוא לא שומר עותק משלו.';

$string['testbanner'] = 'Hence בבדיקות. העמוד הזה הוא כל הרשומה: כל עובדה ש-Moodle היה שולח, וכל טענה והסבר שמנוע הבדיקה מחזיר.';
$string['rule'] = 'מאמינים לטענה רק כשיש לפחות שתי עובדות, הן נפרסות על {$a} ימים, והאמונה היא לפחות 0.75. עובדה אחת נשארת אות. הממצא אז הוא «עדיין לא».';
$string['summary'] = 'מה Moodle יכול לשלוח';
$string['peoplecount'] = 'אנשים עם עובדות';
$string['evidencecount'] = 'עובדות';
$string['coursecount'] = 'קורסים';
$string['submissioncount'] = 'הגשות';
$string['gradecount'] = 'ציונים';

$string['people'] = 'אנשים';
$string['person'] = 'אדם';
$string['email'] = 'אימייל';
$string['facts'] = 'עובדות';
$string['open'] = 'פתיחה';
$string['nobody'] = 'עדיין אין הגשות או ציונים. כשלומד מגיש עבודה או מקבל ציון, הוא מופיע כאן.';

$string['evidence'] = 'ראיות';
$string['claims'] = 'טענות';
$string['explain'] = 'הסבר';
$string['payload'] = 'מה היה עובר מול Hence';
$string['postevidence'] = 'POST /evidence';
$string['getclaims'] = 'GET /claims';
$string['getexplain'] = 'GET /explain';

$string['when'] = 'מתי';
$string['type'] = 'סוג';
$string['source'] = 'קורס';
$string['signal'] = 'עובדה';
$string['belief'] = 'אמונה';
$string['finding'] = 'ממצא';
$string['window'] = 'חלון ראיות';
$string['because'] = 'בגלל';

$string['type_submission'] = 'הוגשה מטלה';
$string['type_grade'] = 'נרשם ציון';
$string['signal_submission'] = 'הגיש «{$a}»';
$string['signal_grade'] = '{$a->item} · {$a->grade} / {$a->max}';

$string['claimsubmits'] = 'מגיש עבודה, לא רק טיוטה';
$string['claimgraded'] = 'יש לו עבודה מדורגת בקורסים שלו';
$string['finding_believe'] = 'סביר להאמין לטענה';
$string['finding_withhold'] = 'עדיין לא. יש אות, אין מספיק ראיות';
$string['spandays'] = '{$a} ימים';
$string['spanoneday'] = 'יום אחד';
$string['spannone'] = 'רגע אחד';
$string['inputs'] = '{$a->count} עובדות · {$a->courses} קורסים · {$a->span}';
