# Santa Rosa City e-Services

A barangay e-services portal covering all **18 barangays** of Santa Rosa City,
Laguna. It runs on **localhost only**: Laravel 12 on PHP 8.2, MariaDB through
XAMPP, and a hand-written CSS design system with **no Node build step** — the
CSS and JS in `public/` are the real assets and are served as-is.

The system has exactly **two interfaces**: a **Resident** portal and a **City
Administrator** console. Each has its own dashboard, its own sidebar and its own
routes.


## Requirements

| | |
|---|---|
| PHP | 8.2 or newer, with `pdo_mysql` and `mbstring` (XAMPP ships both) |
| Database | MariaDB / MySQL |
| Composer | required for `vendor/` (already committed? no — it is gitignored) |
| Node.js | **not needed**; `package.json` and `vite.config.js` are leftovers from the Laravel skeleton and nothing in the app reads them |


## Setup

```powershell
# 1. XAMPP Control Panel: start MySQL. (Apache is optional — see "Running" below.)

# 2. Create an empty database named brgy_based
#    phpMyAdmin > New > "brgy_based", collation utf8mb4_unicode_ci

# 3. From the project root:
composer install
copy .env.example .env          # then open .env and check the DB_ lines
php artisan key:generate
php artisan migrate:fresh --seed
php artisan config:cache        # see "Why config:cache is required" below
php artisan view:clear
```

In `.env`, these are the lines that matter:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=brgy_based
DB_USERNAME=root
DB_PASSWORD=
```

`DB_PORT` is the usual stumbling block. A stock XAMPP listens on **3306**; the
machine this project was developed on has a second MySQL service on 3306, so its
XAMPP was moved to **3307**. Check yours in the XAMPP Control Panel and match it.
`php artisan migrate` failing with `SQLSTATE[HY000] [2002] ... refused` means
MySQL is not started or the port is wrong.

**Why `.env.example` ships the MySQL block explicitly.** `config/database.php`
reads `'default' => env('DB_CONNECTION', 'sqlite')`, so SQLite is the framework's
last-resort answer when `.env` cannot be read at all — which the threaded-mod_php
problem below makes a real possibility, and why `/database/database.sqlite` is
gitignored. But `.env.example` used to ship `DB_CONNECTION=sqlite` with the MySQL
lines commented out, so *anyone copying it got the fallback on purpose*: the app
would start happily, on an empty SQLite file, with none of the barangay data. The
example file now carries the MariaDB block above, so a fresh copy points at the
right database and the fallback still exists for the case it is actually for.


## Running

**Simplest — no Apache:**

```powershell
php artisan serve
```

then open <http://127.0.0.1:8000>.

**Or through Apache**, serving the `public/` folder under a short name:

```powershell
New-Item -ItemType Junction -Path C:\xampp\htdocs\brgy -Target "<this folder>\public"
```

then open <http://localhost/brgy>. With Apache you **must** run
`php artisan config:cache` (next section).


## Why `config:cache` is required here

Apache runs PHP as **mod_php under the threaded `mpm_winnt` MPM**, so all
requests share one process. Laravel loads `.env` with `putenv()`, which is
process-global and not thread-safe in a threaded SAPI. Under concurrent requests
the environment races, and a request can boot with no `.env` at all — it then
falls back to `APP_ENV=production`, no `APP_KEY`, and SQLite.

Measured on this box: without the config cache, 12 concurrent requests produce a
mix of 503s and 500s. With it, 120/120 requests succeed and no failures are
logged.

**The trade-off:** `.env` is no longer read per request, so after editing `.env`
you must re-run `php artisan config:cache`. If the site suddenly looks like it is
ignoring your `.env`, that command is the fix.

`bootstrap/cache/` is gitignored, so the cache (which contains `APP_KEY`) is
never committed — a fresh clone must run `config:cache` once.


## Signing in

Every seeded account uses the password `password`.

| Account | Interface | What it demonstrates |
|---|---|---|
| `city.admin@barangay.gov` | City Administrator | no home barangay: opens on all 18 |
| `admin@barangay.gov` | City Administrator | home barangay Tagapo: opens filtered to Tagapo |
| `staff@barangay.gov` | City Administrator | the desk account other seeders name in tracker notes |
| `admin.labas@barangay.gov` | City Administrator | home barangay Labas |
| `maria.santos@residents.ph` | Resident | lives in Kanluran |

Registration is resident-only. The sign-up form has no role field and the
controller hard-codes `role => 'resident'`, so nobody can register themselves
into the console; an account becomes an administrator only when an existing
administrator grants it from Admin → Accounts.

A note on stored role values: the database still holds `admin` and `staff` for
accounts created before the two-interface design. Those values are read
everywhere (via `User::OFFICIAL_ROLES`) but offered nowhere: they open the same
City Administrator console and are displayed as "City Administrator". Nothing
was migrated, so existing records and logins kept working. Assigning
"City Administrator" writes `city_admin`, so the roster normalises as it is
edited.


## Access control

- Routes are grouped by interface. Resident routes sit behind
  `['auth', 'role:resident']`; console routes behind `['auth', 'role:official']`.
- One middleware (`app/Http/Middleware/EnsureRole.php`) does three things: sends
  guests to the login page, logs out and warns suspended accounts, and redirects
  an authenticated user without the role **to their own dashboard** rather than
  showing them a dead 403 page.
- `isAdmin()` / `User::isOfficial()` decide the landing page on sign-in, so an
  officer never lands on the resident dashboard and vice versa.
- Record ownership is enforced separately: a resident asking for someone else's
  complaint or request gets 403, and the roster refuses to suspend, delete or
  demote the last remaining administrator account so the console can never lock
  itself out.


## The barangay filter

Every officer has a **home barangay** (`users.barangay_id`). It decides where the
console *opens*, not what the officer may see: the header filter can narrow the
whole console to any one of the 18 barangays or widen it to all of them
(`?barangay=<id>` / `?barangay=all`).

That choice is resolved in exactly one place, `app/Support/BarangayFilter.php`,
and read by the controllers, the sidebar badge counts and the header pill alike.
Before this existed the same number was computed twice — once in the controller
trait and once by hand in the view composer — which is how the sidebar came to
claim a total that the table underneath it contradicted.

The resolved value is memoised **on the request object, not in a static
property**. Under threaded mod_php one process serves every request, so a static
cache would survive into the next officer's page and show them another barangay's
data. This was caught by testing, not by reading the code.

`app/Support/ListFilters.php` is the equivalent single source for the list pages:
it keeps the search term, the priority and the active barangay when you click a
status tab, instead of resetting everything to "All".


## Interface and accessibility

The interface is token-driven: colours, radii, shadows, fonts and layout widths
are CSS custom properties declared once at the top of `public/css/app.css`, and
every component (buttons, badges, chips, tabs, cards, tables, modals, toasts)
reads them. That is what makes the whole site re-skinable from one place.

The sidebar is collapsible. Administrators get **6 rows** and residents **4**,
with related pages grouped under headings that open in place; the section
containing the page you are on opens itself, and the panel folds away entirely
behind the ☰ control at any width (drawer plus backdrop on a phone, which also
closes when you choose a link).

**The Display button** (top bar, the public site nav, and both auth screens —
because someone needs contrast and larger text to *read* the login page) offers
six independent preferences:

1. **Theme** — *City* (the default dark blue) or *Terminal*: monochrome,
   monospace, flat, hairline borders, no gradients, glow or blur.
2. **Text size** — Small / Normal / Large / Extra large, applied at the document
   root so the ~105 rem-based sizes in the stylesheet scale together.
3. **High contrast** — pure black fields, white text, and gradient-filled ink
   removed: a cyan-to-violet heading measures anywhere between 4.5:1 and 8:1
   depending on which letter you look at, which is not a promise anyone can rely
   on.
4. **Motion** — Automatic (follows the operating system), Reduce, or Allow.
   Someone who has already told Windows to limit animations is not asked to find
   our switch.
5. **Underline links** — colour alone is not a valid signal.
6. **Bigger tap targets** — 44px minimum on controls (WCAG 2.5.5).

Preferences live in `localStorage` under `brgy:a11y` — per device and per
browser, never in the database, so they apply before sign-in and cost nothing to
reset. `public/js/access.js` is loaded **without `defer`** in the `<head>` so the
stored choices are on `<html>` before the first paint; nobody sees the wrong
theme flash first. The controls are real radio buttons and checkboxes, so
keyboard and screen-reader use works without extra code, and `Escape` returns
focus to the button.

Contrast was measured rather than eyeballed: across the administrator console,
the resident portal, the login page and the public landing page, the Terminal
theme has **no WCAG AA failures** (worst case 6.55:1 against the 4.5:1 floor).


## Certificates

An approved document request produces a real printable certificate
(`/resident/documents/{id}/certificate` and `/admin/requests/{id}/certificate` —
**the same template**, selected by which interface the viewer is in, so the paper
the records desk prints and the paper a resident prints at home cannot drift
apart). It is a print-styled page plus `@media print`, not a generated PDF: the
browser's Print-to-PDF produces the file, so there is no extra dependency and no
second copy of every certificate. The letterhead, office address and signatory
come from Admin → Settings, so one edit updates every certificate in the system.
Requests that have not reached an approved state cannot print at all.

Certificates deliberately keep fixed pixel font sizes: enlarging the site's text
must not move official paper around.


## Where things live

```
routes/web.php                          every route, grouped by interface
app/Http/Controllers/AuthController     sign in / register / redirect by role
app/Http/Controllers/Resident/…         resident interface
app/Http/Controllers/Admin/…            administrator interface
app/Http/Controllers/Concerns/          ScopesAdminQueries (the barangay filter)
app/Http/Middleware/EnsureRole.php      the role gate
app/Models/                             User, Barangay, Complaint, DocumentRequest, …
app/Support/BarangayFilter.php          what barangay is on screen
app/Support/ListFilters.php             filter links that keep your other choices
app/Support/CityBreakdown.php           per-barangay workload numbers
resources/views/layouts/portal.blade.php  the shared shell for both interfaces
resources/views/layouts/nav/…           the two sidebars
resources/views/components/…            icon, nav-item, nav-group, access
public/css/app.css                      the whole design system + themes
public/js/app.js                        sidebar, modals, in-place filtering/saving
public/js/access.js                     display & accessibility preferences
database/migrations, database/seeders    schema and the demo data
```


## Known rough edges

- `tests/` still holds only Laravel's two default example tests. Behaviour here is
  verified by rendering every page for every role, not by an automated suite.
- Two administrator modals (Accounts and Appointments) rely on JavaScript to set
  their submit target; with scripting off those two forms would fail. Every other
  page works without JS.
- Some administrator list pages still show both status tabs and a status dropdown
  (Announcements, Jobs, Lost & Found, Accounts). Complaints, Document Requests
  and Appointments have been consolidated; the rest are mid-pass.
- In the default theme two decorative components sit just under the AA floor —
  the initials avatar and the brand mark, both 4.48:1 on their gradient fill.
  Terminal and high-contrast mode fix them.
- `resources/views/welcome.blade.php` is dead: no route returns it.


## Licence

MIT. Built on the Laravel framework, which is open-source software licensed
under the MIT licence.
