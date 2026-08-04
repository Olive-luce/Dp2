---
name: testing-incident-flow
description: How to set up and end-to-end test the Dp2 community disaster platform locally — starting services, DB access, role logins, seeding test data for volunteer/resource flows, and verifying the incident relay across citizen/responder/volunteer/admin views.
---

# Testing the Dp2 disaster platform locally

## Bring the app up

The blueprint's `initialize` installs PHP + MariaDB and symlinks the repo to `~/www/Dp2`. At session start:

```bash
sudo service mariadb start
curl -s -o /dev/null -w "%{http_code}\n" http://localhost/Dp2/   # expect 200
```

If nothing is listening, serve it yourself: `sudo php -S localhost:80 -t ~/www`.

If you re-import the schema, apply the migrations too — the base dump may lag behind:

```bash
sudo mysql < database/disaster_platform.sql
for f in database/migrations/*.sql; do sudo mysql community_disaster_platform < "$f"; done
```

## Database access without printing credentials

Credentials live in `config/config.php`. Build a defaults file from the constants so secrets never hit the transcript:

```bash
php -r 'require "config/config.php"; file_put_contents("/tmp/my.cnf",
  sprintf("[client]\nhost=%s\nuser=%s\npassword=%s\n", DB_HOST, DB_USER, DB_PASS));'
chmod 600 /tmp/my.cnf
mysql --defaults-extra-file=/tmp/my.cnf community_disaster_platform -e "SELECT ..."
```

## Logins

All seeded users share password `admin123`: `admin` (admin), `maria` (responder), `jose` (volunteer), `ana` (citizen). There is no role switcher — log out via the top-right **Logout** button and log back in as the next role. Expect several logout/login cycles for any cross-role relay test; budget for that.

## Where the features live

| Flow | Path | Role |
|---|---|---|
| Submit a report | `/citizen/report.php` (also shows "Your Reports") | citizen |
| Dispatch board: claim + post status | `/responder/incidents.php` | responder |
| Allocate stock to an incident | `/responder/resources.php` | responder |
| Volunteer tasks / availability | `/volunteer/tasks.php`, `/volunteer/checkin.php` | volunteer |
| Map pin reporting | `/modules/incidents/map.php` | any role |
| Incident table | `/admin/incidents.php` | admin |

`includes/incidents.php` is the single source of truth (`createIncident`, `setIncidentStatus`, `logIncidentUpdate`, `notifyIncidentParticipants`, `fetchIncidents`). Anything that should show up across roles must go through it — if a new write path bypasses it, the incident will not appear on other roles' pages.

## Seeding data the UI cannot create

There is no admin UI to create a `volunteer_assignments` row, so a volunteer has no task to act on unless you insert one. Volunteer 1 maps to user `jose`:

```sql
INSERT INTO volunteer_assignments (volunteer_id, incident_id, assignment_note, status)
VALUES (1, <incident_id>, 'Welfare checks', 'assigned');
```

## Verifying dashboard counts honestly

Responder and citizen dashboards read live counts, so never judge them by eye — they can coincidentally match the old hardcoded values. Compare against SQL:

```sql
SELECT COUNT(*) FROM disaster_incidents WHERE status <> 'resolved';     -- Live Incidents
SELECT COUNT(*) FROM resources WHERE status = 'available';              -- Resources Ready
SELECT COUNT(*) FROM shelters WHERE status <> 'closed';                 -- Shelters Open
SELECT SUM(current_occupancy), SUM(capacity) FROM shelters;             -- Shelter Occupancy %
SELECT COUNT(*) FROM disaster_incidents WHERE reported_by = <user_id>;  -- Reported Issues
SELECT COUNT(*) FROM announcements;                                     -- Public Updates
```

## API authorization checks

The incident APIs require a session. Unauthenticated requests should be 401, and a citizen session should get 403 on PUT/DELETE:

```bash
for ep in incidents_crud map_incidents; do for m in GET POST PUT DELETE; do
  curl -s -o /dev/null -w "$m $ep %{http_code}\n" -X $m \
    http://localhost/Dp2/api/$ep.php -H 'Content-Type: application/json' -d '{}'
done; done

curl -s -c /tmp/ana.txt -o /dev/null -d "username=ana&password=admin123" \
  http://localhost/Dp2/auth/login.php
curl -s -b /tmp/ana.txt -o /dev/null -w "%{http_code}\n" -X DELETE \
  "http://localhost/Dp2/api/incidents_crud.php?id=1"
```

Note the login endpoint accepts form-encoded `username`/`password` and sets a normal PHP session cookie, so `curl -c/-b` works fine for API-role checks. Do not reuse a browser cookie for this.

## Gotchas seen in practice

- **The citizen report page shows only the newest update per incident** (`$updates[0]`). If you post a responder note and then trigger a volunteer/resource event, the note is no longer visible to the citizen. To prove a responder→citizen relay, post a *fresh* note as the last action before checking the citizen view.
- **Seed incidents 1–3 have Manila coordinates (14.6, 121.0)** while the map centres on Bangladesh at zoom 6, so they render off-screen and the map can look empty. Don't mistake this for broken pin rendering — click a point inside the visible Bangladesh area and submit to test pins.
- **Map reports hardcode `address: 'Selected location'`** in `assets/js/map-app.js`, producing titles like "Flood reported near Selected location".
- JS builds fetch URLs from `body[data-base-url]` (set in `includes/header.php`). If fetches 404, check that attribute is present on the page under test.
- Bootstrap `<select>` elements need a click to open then a click on the option; typing into them does not work reliably with computer-use.

## Cleaning up test rows

Incident writes fan out into four tables. Clean all of them, or later count assertions will be wrong:

```sql
DELETE FROM volunteer_assignments  WHERE id > <baseline>;
DELETE FROM resource_allocations   WHERE id > <baseline>;
DELETE FROM incident_updates       WHERE incident_id > <baseline>;
DELETE FROM notifications          WHERE created_at >= '<run start>';
DELETE FROM disaster_incidents     WHERE id > <baseline>;
UPDATE volunteers SET availability = 'available' WHERE id = 1;
```

Record the baseline counts *before* testing so you can verify the restore afterwards.

## Devin Secrets Needed

None. All credentials are local seed data (`admin123`) and DB credentials come from `config/config.php`.
