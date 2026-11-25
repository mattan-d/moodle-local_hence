# Hence

Hence is the trust layer for human capability. This local plugin reads the facts Moodle already holds — assignment submissions and recorded grades — and shows them on a site administration page, together with the claims and explanations a test engine returns.

Hence itself is still in test, so the plugin does not send those facts to an external service. The administration page is the whole record: every fact, every claim, and the JSON that would be exchanged.

## Requirements

- Moodle 4.5 or later
- GNU GPL v3 or later

## Installation

1. Place this folder at `local/hence` (the repository root is the plugin root).
2. Visit Site administration > Notifications and install **Hence**.
3. Open Site administration > Plugins > Local plugins > Hence, or go directly to `/local/hence/index.php`.

## What you see

The page lists people who have at least one submission or grade. Opening a person shows:

- the evidence Moodle would send
- two claims: submitting coursework, and having graded work on record
- the finding (believe, or not yet) and the facts underneath
- the `POST /evidence`, `GET /claims` and `GET /explain` payloads

A claim is believed only when there are at least two facts, they span 14 days, and the belief is at least 0.75.

## Support

Issues: https://github.com/mattan-d/moodle-local_hence/issues
