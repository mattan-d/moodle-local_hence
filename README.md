# Hence

Hence is the trust layer for human capability. This local plugin reads the facts Moodle already holds — submitted assignments and recorded grades — and sends new facts to Hence.

The scheduled task **Send new facts to Hence** runs once every 4 hours by default, at minute 0 of 00:00, 04:00, 08:00, 12:00, 16:00 and 20:00. A manager can change that under Site administration > Server > Scheduled tasks. Moodle cron still has to run often enough to pick the task up.

## Requirements

- Moodle 4.5 or later
- GNU GPL v3 or later
- A Hence endpoint and the same shared secret on both sides

## Installation

1. Place this folder at `local/hence` (the repository root is the plugin root).
2. Visit Site administration > Notifications and install **Hence**.
3. Open Site administration > Plugins > Local plugins > Hence settings.
4. The **Hence address** is already `https://hence.today/api/evidence`. Change it only if this site should send facts somewhere else.
5. On install, the plugin asks Hence for a sending secret for this site's domain and saves it. Do not commit that secret.

Until Hence has issued the secret, the task records that it is not configured and does not mark any fact as sent. An empty address still posts to `https://hence.today/api/evidence`.

## What is sent

Each run posts facts Hence has not accepted yet. A fact is sent again when its time in Moodle changes. Batches hold up to 200 facts.

A fact is one of:

- `AssignmentSubmitted` — the latest submitted attempt on an assignment
- `GradeRecorded` — a final grade on an activity or a manual grade item

Hence does not receive the person's name, email, or Moodle user id. Each user gets one Hence id when the account is created, and that id never changes. It looks like `HNC-7K3M-9Q2P-4X8N-6B2D`. The link between the Moodle user and the Hence id stays in Moodle. A manager can turn on a user-menu link to that id under Hence settings. It is off by default.

The JSON body has `site`, `sent_at`, and `evidence`. Each evidence item has `id`, `person` (the Hence id), `source` (`Moodle`), `type`, `occurred_at`, and `metadata` (`course`, `signal`, and for a grade also `grade` and `max`).

Hence answers `{"ok": true, "accepted": N}`. Only then does the plugin remember that fact. A refused batch stays pending and is tried on the next run.

## Privacy

The plugin stores the permanent Hence id for each Moodle user in `local_hence_person`. It also stores the id and time of each fact Hence has accepted, in `local_hence_sent`, so it is not sent again. That second table does not store a user id. The facts sent to Hence carry the Hence id, the course, and what happened. They do not carry the person's name.

## Support

Issues: https://github.com/mattan-d/moodle-local_hence/issues

## License

GNU GPL v3 or later. See http://www.gnu.org/copyleft/gpl.html.
