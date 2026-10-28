# LAV_SMS v2 — Supabase (Postgres) Connection Guide

> **STATUS: CONNECTED on 2026-10-28.** The production site https://lav-sms-rho.vercel.app/ runs on the Supabase project `lav-sms` (Singapore, ap-southeast-1, Session pooler via Vercel env `DATABASE_URL`). The one-time token-protected `/__setup` endpoint used to run the migrations/seeders was removed after verification (commit 563e17c) and the `SETUP_TOKEN` env var was deleted. **UPDATE 2026-10-28 (v3 Pakistani version):** the live database was wiped and re-seeded with the Pakistani dataset (7 provinces + 121 districts, Playgroup–Class 10, Pakistani subjects, board grades, only core logins + 6 demo students, no student logins) via the same temporary `/__setup` flow (with a `migrate:fresh` step); the endpoint was removed again afterwards. Note: `migrate:fresh` in one call can exceed the 60s serverless limit — run `step=migrate` afterwards to finish any remaining migrations, and seed one seeder per call; the PkDemoResultsSeeder response may time out in the client while still completing server-side, so verify through the app.
The steps below remain as the reference for how it was done / how to repeat it for another school. Note for future seeding: the heavy seeders (Lgas 774 rows, Nationalities, States) and StudentRecords now bulk-insert — row-by-row seeding over the pooler times out on serverless.

The app runs in **two database modes**:

| Mode | When | Data |
|---|---|---|
| **SQLite demo** (default) | No `DATABASE_URL` set | Bundled seeded demo DB, copied to `/tmp` on Vercel — **resets on cold start**, for demos only |
| **Supabase Postgres** (production) | `DATABASE_URL` set | Real, permanent, secure — use this for a real school |

Follow these steps once, in order. You need: a Supabase account (free) and access to the Vercel project `lav-sms`.

---

## Step 1 — Create the Supabase project
1. Go to https://supabase.com → sign in → **New project**.
2. Name it e.g. `lav-sms`, choose a strong database password and **save it somewhere safe** (you will need it in Step 2), region: closest to your users (e.g. Singapore / Mumbai for Pakistan).
3. Wait until the project status is **Active**.

## Step 2 — Get the connection string (pooler)
1. In the project: **Settings → Database → Connection string → URI**.
2. Choose the **Session pooler** (or Transaction pooler, port 6543) connection string — serverless apps like Vercel must use the pooler, not the direct connection.
3. It looks like:
   `postgres://postgres.<project-ref>:<your-db-password>@aws-0-<region>.pooler.supabase.com:6543/postgres`
4. Replace `<your-db-password>` with the password from Step 1. Keep this string secret — never commit it to GitHub.

## Step 3 — Create the tables in Supabase (one-time)
From your computer, in the project folder:

```bash
# .env (local only, never commit)
DB_CONNECTION=pgsql
DATABASE_URL=postgres://postgres.<ref>:<password>@aws-0-<region>.pooler.supabase.com:6543/postgres

php artisan migrate --force        # creates all tables (incl. diaries, salary_payments)
php artisan db:seed --force        # optional: loads the demo accounts & data
```
> No local PHP? Use the Supabase **SQL Editor** instead: the migrations are standard Laravel migrations; ask your developer to export/run them, or run the two commands above from any machine with PHP 8.0 + Composer.

Verify in Supabase: **Table Editor** should list `users`, `student_records`, `diaries`, `salary_payments`, `payments`, `marks`, etc.

## Step 4 — Tell Vercel to use Supabase
1. Vercel dashboard → project **lav-sms** → **Settings → Environment Variables**.
2. Add (Production environment):
   - `DATABASE_URL` = the pooler string from Step 2
   - `SESSION_SECURE_COOKIE` = `true`
3. **Do not** change or delete the existing `APP_KEY` etc. that ship in `vercel.json` for the demo; for a real school, also add a fresh `APP_KEY` here (generate with `php artisan key:generate --show`) so the key is not the public demo one.
4. Redeploy: **Deployments → ⋯ → Redeploy** (or push any commit — the app detects `DATABASE_URL` automatically in `api/index.php` and switches to Postgres; no code change needed).

## Step 5 — Verify
1. Open the live site → log in with the Super Admin you seeded (or create one, Step 6).
2. Add a diary entry and a student, then wait/refresh — data must still be there (it is in Supabase now, not `/tmp`).
3. In Supabase **Table Editor → diaries**, the new row should be visible.

## Step 6 — Go-live checklist (security first)
- [ ] Change **every** demo password (`cj`) — Super Admin first, then all staff; delete demo accounts you don't need.
- [ ] Set a fresh `APP_KEY` in Vercel env (Step 4) — sessions/cookies signed with the demo key are public knowledge.
- [ ] Confirm `APP_DEBUG=false` (it is, in `vercel.json`) — never enable it on production except briefly to debug.
- [ ] Keep the Supabase password and `DATABASE_URL` out of Git, chat screenshots and emails.
- [ ] In Supabase: **Authentication policies** are not used by this app (Laravel handles auth), but enable **Point-in-Time Recovery / backups** if on a paid plan; on free plan, export the DB monthly (**Database → Backups**).
- [ ] Only give Super Admin accounts to trusted people — Super Admin can delete records and reverse salary payments.
- [ ] The app already enforces: login throttling (5 tries, Laravel built-in), CSRF on every form, bcrypt password hashing, role-based access (parents/students only ever see their own children's data — checked in controllers), secure + http-only session cookies in production, and security headers (X-Frame-Options, nosniff, HSTS, Referrer-Policy).

## Troubleshooting
- **"no such table" on Vercel after switching** → Step 3 migrations were not run against Supabase. Run them and redeploy.
- **Login page loads but login fails** → the Supabase DB has no users yet: run `php artisan db:seed` (Step 3) or create a user.
- **Connection timeouts** → you used the *direct* connection string; switch to the **pooler** string (port 6543).
- **Want to go back to demo mode** → delete the `DATABASE_URL` env var in Vercel and redeploy.
