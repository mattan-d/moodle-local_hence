# Hence

Hence is the trust layer for human capability. This local plugin reads the facts Moodle already holds — submitted assignments and recorded grades — shows them on a site administration page, and sends new facts to Hence.

The scheduled task **Send new facts to Hence** runs once every 4 hours by default, at minute 0 of 00:00, 04:00, 08:00, 12:00, 16:00 and 20:00. A manager can change that under Site administration > Server > Scheduled tasks. Moodle cron still has to run often enough to pick the task up.

## Requirements

- Moodle 4.5 or later
- GNU GPL v3 or later
- A Hence endpoint and the same shared secret on both sides

## Installation

1. Place this folder at `local/hence` (the repository root is the plugin root).
2. Visit Site administration > Notifications and install **Hence**.
3. Open Site administration > Plugins > Local plugins > Hence settings.
4. Set **Hence address**. The default is `https://hence.today/evidence.php`.
5. Set **Shared secret** to the same value stored on the Hence server. Hence reads it from the environment variable `HENCE_EVIDENCE_SECRET` or from `data/evidence_secret`. The plugin sends it in the `X-Hence-Evidence-Secret` header. Do not commit the secret.
6. Open Site administration > Plugins > Local plugins > Hence, or go directly to `/local/hence/index.php`.

Until the address and secret are set, the task records that it is not configured and does not mark any fact as sent.

## What is sent

Each run posts facts Hence has not accepted yet. A fact is sent again when its time in Moodle changes. Batches hold up to 200 facts.

A fact is one of:

- `AssignmentSubmitted` — the latest submitted attempt on an assignment
- `GradeRecorded` — a final grade on an activity or a manual grade item

The JSON body has `site`, `sent_at`, and `evidence`. Each evidence item has `id`, `person` (`user_` plus the Moodle user id), `source` (`Moodle`), `type`, `occurred_at`, and `metadata` (`course`, `signal`, and for a grade also `grade` and `max`).

Hence answers `{"ok": true, "accepted": N}`. Only then does the plugin remember that fact. A refused batch stays pending and is tried on the next run.

**Send now** on the administration page posts the pending batch immediately.

## What you see

The administration page lists people who have at least one submission or grade. Opening a person shows:

- the evidence for that person
- two local claims: submitting coursework, and having graded work on record
- the finding (believe, or not yet) and the facts underneath
- the `POST /evidence`, `GET /claims` and `GET /explain` payloads

Claims and explanations are calculated on this page. They are not posted to Hence. A claim is believed only when there are at least two facts, they span 14 days, and the belief is at least 0.75.

The same page shows how many facts are waiting, when the next scheduled send is, and when Hence last accepted a batch.

The page requires the `local/hence:view` capability. Managers have it by default, and site administrators can open it.

## Privacy

The plugin stores the id and time of each fact Hence has accepted, in `local_hence_sent`, so it is not sent again. It does not store a copy of the grade or a user id in that table. The facts themselves are sent to Hence: the Moodle user id, the course, and what happened.

## Support

Issues: https://github.com/mattan-d/moodle-local_hence/issues

## License

GNU GPL v3 or later. See http://www.gnu.org/copyleft/gpl.html.
