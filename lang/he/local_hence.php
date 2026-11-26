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

$string['testbanner'] = 'עובדות חדשות נשלחות ל-Hence כל 4 שעות, תחת מזהה קבוע. Hence לא מקבל את שם האדם. העמוד הזה עדיין כל הרשומה.';
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

$string['settings'] = 'הגדרות Hence';
$string['scheduleheading'] = 'שליחה';
$string['schedule_desc'] = 'המשימה המתוזמנת שולחת עובדות חדשות פעם ב-4 שעות. זו ברירת המחדל. אפשר לשנות אותה בניהול האתר > שרת > משימות מתוזמנות.';
$string['endpoint'] = 'כתובת Hence';
$string['endpoint_desc'] = 'לאן נשלחות עובדות חדשות. ברירת המחדל היא https://hence.today/api/evidence.';
$string['secret'] = 'סוד שליחה';
$string['secret_desc'] = 'נשלח עם כל אצווה בכותרת X-Hence-Evidence-Secret. הוא מאפשר לאתר הזה לכתוב את העובדות שלו. הוא לא פותח את ניהול Hence.';
$string['tasksend'] = 'שליחת עובדות חדשות ל-Hence';
$string['sendheading'] = 'שליחה ל-Hence';
$string['sendlast'] = 'שליחה שהתקבלה לאחרונה: {$a}';
$string['sendnever'] = 'עדיין לא התקבלה שליחה.';
$string['sendcount'] = '{$a} עובדות בשליחה האחרונה שהתקבלה';
$string['sendpending'] = '{$a} עובדות ממתינות';
$string['sendnext'] = 'השליחה המתוזמנת הבאה: {$a}';
$string['sendnow'] = 'שליחה עכשיו';
$string['sendfailed'] = 'Hence לא קיבל את האצווה.';
$string['senderror_notconfigured'] = 'צריך להגדיר את סוד השליחה לפני שאפשר לשלוח עובדות. הכתובת כבר מצביעה ל-Hence.';
$string['senderror_network'] = 'לא הצלחנו להגיע ל-Hence.';
$string['senderror_encode'] = 'לא הצלחנו לקודד את האצווה.';
$string['senderror_http'] = 'Hence לא קיבל את האצווה האחרונה ({$a}).';

$string['privacy:metadata:sent'] = 'מזהה של עובדה ש-Hence כבר קיבל, כדי שלא תישלח שוב.';
$string['privacy:metadata:sent:factid'] = 'מזהה העובדה.';
$string['privacy:metadata:sent:occurred'] = 'מתי העובדה קרתה ב-Moodle.';
$string['privacy:metadata:hence'] = 'עובדות נשלחות ל-Hence.';
$string['privacy:metadata:hence:person'] = 'המזהה הקבוע של האדם ב-Hence. השם לא נשלח.';

$string['myid'] = 'המזהה שלי ב-Hence';
$string['myid_desc'] = 'המזהה הזה שלך לכל החיים. איתו אפשר להתחבר לחשבון ב-Hence בעתיד. השם שלך לא נשמר ב-Hence.';
$string['personid'] = 'מזהה Hence';
$string['personid_desc'] = 'Hence מקבל את המזהה הקבוע הזה, לא את שם האדם.';
$string['privacy:metadata:person'] = 'המזהה הקבוע של המשתמש ב-Hence.';
$string['privacy:metadata:person:userid'] = 'המשתמש ב-Moodle שהמזהה שייך לו. הקישור נשאר ב-Moodle.';
$string['privacy:metadata:person:publicid'] = 'המזהה הקבוע ש-Hence שומר במקום שם.';
$string['privacy:metadata:hence:course'] = 'הקורס שממנו הגיעה העובדה.';
$string['privacy:metadata:hence:signal'] = 'מה קרה: הגשה או ציון.';
