# UPTMS - University Project & Thesis Management System

World University of Bangladesh, Department of CSE. Final-year project of **Sobuj (3917), Md Adil Miah (3919), Prodip Rajbongshi (3921)**, supervised by Dr. Jannatul Naeem.

Raw PHP 8.2 (OOP) + MySQL/MariaDB (PDO) + Bootstrap 5 + Alpine.js. No PHP framework. Written to be **explainable at an intermediate level** (every part must be defendable in the viva).

> **Last updated: 2026-10-19.** This file is the single source of truth for resuming work. Read sections 1, 12 and 13 first.

---

## 1. Status at a glance

| Area | State |
|---|---|
| Foundation + security (login, CSRF, rate limit, panel guard, folder protection) | done |
| Reusable pieces (`Validation`, `FileUpload`, JSON API pattern, initials avatar) | done |
| Research setup: categories, tags | done |
| University structure: departments, batches, academic sessions | done |
| People: admins, teachers (with research permission), students | done |
| Research / thesis: admin catalog, pending approvals, ongoing, library, My Research, requests | done |
| Student dashboard, profile pages, change password | **not started** |
| Teams, proposals, projects, milestones, progress, documents, feedback | **not started** |
| Evaluations, reports, settings, dashboards (admin / teacher) | **not started** |

Real (database-connected) pages: **21 of 63** - admin 15/33, teacher 3/15, student 3/15. The rest are still the original static mock-ups (hardcoded PHP arrays).

**Nothing since commit `0c1ed39` is committed yet** (about 48 changed/new paths). Commit soon, in logical commits (see section 13).

---

## 2. Run it

Needs XAMPP (Apache with `mod_rewrite` and `AllowOverride All`, MySQL/MariaDB), PHP 8.1+, Composer.

```bash
composer install                 # vendor/ is not in git
copy .env.example .env           # then check APP_URL / ASSET_URL / DB_* in .env
php migrate.php --seed           # creates database uptms_db, all tables, and the 3 demo users
```

Open `http://localhost/UPTMS`. Migrations run **only from the command line** (the script refuses to run in a browser).

### Demo accounts (password `password123` for all)
| Role | Email | Notes |
|---|---|---|
| Admin | `admin@uptms.test` | |
| Teacher | `teacher@uptms.test` | has **no profile row** yet; edit it in the admin teacher page to complete it |
| Student | `developersobuj@gmail.com` | this is the seeded student whose email/name were edited in the admin UI. `--seed` would create `student@uptms.test` only if that email is missing |

The login screen has a role selector (Admin / Teacher / Student); the selected type must match the account's role.

---

## 3. Architecture

```
Browser page (admin/..., teacher/..., student/...)          PHP prints only the layout
   Alpine component  x-data="Sobuj_xxxPage(...)"            page logic lives in an inline <script> (or shared js)
        |  fetch()  via  Sobuj_api()  (assets/admin_assets/js/api.js: adds the CSRF header, parses JSON)
        v
api/xxx.php        Api::start(); Api::requireRole(...); Api::requireCsrf();   -> JSON only
        v
app/Controllers/XxxController.php     static methods, return arrays: ['success','errors','message']
        v
app/Models/Xxx.php                    PDO + explicit SQL, soft-delete rule inside the model
        v
MySQL (uptms_db)
```

Request guard order for panel pages: `app/bootstrap.php` -> `session_start()` -> `Auth::guardPanel()`. The panel is decided by the **top-level folder** of the page (`admin/`, `teacher/`, `student/`), so **no page needs its own guard line**. The user is re-read from the database on every panel request (`Auth::currentUser()`), so a deleted, deactivated or role-changed account is logged out on its next request, and the API refuses it with 401.

Why this design: PHP is only the backend; the screens are plain Alpine + JSON, which is easy for teammates who do not know PHP, and every API can be tested with curl.

---

## 4. Folder map

```
admin/  student/  teacher/      the three panels (pages). */includes/ = sidebar + shared partials (not web-reachable)
api/                            JSON endpoints (see section 6)
app/
  bootstrap.php                 composer autoload + session + guard
  Config/Database.php           PDO connection (reads .env)
  Controllers/                  Auth, UserController, CategoryController, TagController, DepartmentController,
                                BatchController, AcademicSessionController, ResearchController
  Models/                       User, Category, Tag, Department, Batch, AcademicSession, Research
  Services/                     Validation, FileUpload, Api, Role
  helpers/helpers.php           env, url, base_path, current_file/folder, page_action, old, error, csrf_*, flash, initials
assets/admin_assets/            css/main.css, libs/ (bootstrap, icons, apexcharts, flatpickr, alpinejs - all local), js/
database/migrations/            001 ... 014
docs/                           older planning docs + the proposal PDF (gitignored, partly outdated)
includes/admin/                 page_header_breadcrumb.php
uploads/                        uploaded files (ignored by git; has its own .htaccess)
index.php  logout.php  migrate.php  .htaccess (root rules)  .env  .env.example
```

Shared JS in `assets/admin_assets/js/`:
- `api.js` - `Sobuj_api()` (JSON and FormData), `Sobuj_rememberMessage()` / `Sobuj_takeMessage()` (message across a redirect)
- `users.js` - user list/form components, `Sobuj_initials()`, `Sobuj_photoUrl()`
- `research.js` - research list / library / my-research / forms components
- `term.js` - `Sobuj_termLabel()` ("Semester 5 of 6")
- `auth.js`, `dashboard.js` - original template scripts (login toggle, sidebar, charts)

---

## 5. Database (uptms_db)

| Table | Purpose / key points |
|---|---|
| `users` | login + shared fields: name, email (unique, **even after delete**), phone, photo, password (hash), role `admin|teacher|student`, status, failed_attempts, locked_until, last_login_at, deleted_at |
| `teacher_profiles` | `user_id` PK, `employee_id` (unique), department_id, designation, specialization, research_area, **`can_add_research`** |
| `student_profiles` | `user_id` PK, `student_id` (unique), department_id, batch_id, gender, cgpa, completed_credit_hours |
| `departments` | name, code, description, status, deleted_at |
| `academic_sessions` | name, `term_type` `yearly|semester|trimester`, year, start_date, end_date, `is_current` (one per type), status |
| `batches` | name, code, department_id, **shift** `Regular|Evening`, **term_system**, **total_terms** (program length, entered per batch), admission_session_id, current_session_id, current_term_no |
| `categories`, `tags` | name, description, status, deleted_at |
| `research` | code `RS-<year>-<id>`, title, abstract, type `Thesis|Research Paper|Project`, category_id, year, **status** `requested|pending|ongoing|completed|rejected`, target_status, supervisor_id, created_by, review_note, reviewed_at, deleted_at |
| `research_tag`, `research_student` | link tables (research <-> tags, research <-> students) |
| `migrations` | which migration files already ran |

Migration rule: **never edit a migration that already ran** - add a new numbered file (`ALTER TABLE ...`). Run with `php migrate.php`.

Soft delete: tables with `deleted_at` are never really deleted; **every read query must contain `deleted_at IS NULL`, and it lives inside the Model**. Validation rule `unique_active` ignores soft-deleted rows (category/tag/department/batch names), `unique` counts them (user emails, student/employee IDs stay reserved).

---

## 6. API endpoints (all return JSON; POST needs `X-CSRF-Token`)

| File | Who | Does |
|---|---|---|
| `categories.php`, `tags.php`, `departments.php`, `batches.php`, `sessions.php` | admin | list (search/filter/page), save (add/edit), delete |
| `users.php?role=admin\|teacher\|student` | admin | list, one, save (multipart with photo), delete |
| `options.php` | admin | dropdown data: departments, batches, sessions |
| `research.php` | admin | list/one, `save`, `review` (approve/reject), `complete`, `delete` |
| `library.php` | admin, teacher, student | read-only library (completed, + ongoing with `include_ongoing=1`) + counts, category counts, years |
| `my_research.php` | teacher, student | own research (submitted / supervised / member), `save`, `delete` (withdraw) |
| `research_options.php` | all three | categories, tags, types (+ teachers for admin) |
| `lookup.php?q=` | admin | student search for the picker |

Status codes: 200 ok, 422 validation (`errors` per field + `message`), 401 not logged in, 403 wrong role or bad CSRF (`code: csrf`), 404, 405. (Not 419: Apache turns unknown codes into 500.)

---

## 7. Pages

**Real (admin):** `admins/` (list + manage), `university/{teachers,students}/` (list + manage), `university/departments`, `university/batches`, `university/sessions`, `research/{index,manage,pending,ongoing,categories,tags}.php`.
**Real (teacher + student):** `research/index.php` (library), `research/my_research.php`, `research/manage.php` (request / add form).

**Still static (to build):**
- admin: `index` (dashboard), `projects/*` (index, manage, proposals, pending, milestones, progress), `teams/index`, `documents/*`, `evaluation/*` (projects, thesis, criteria), `reports/*`, `settings/index`; orphan `research/my_research.php` (not linked).
- teacher: `index`, `projects`, `project`, `proposals`, `proposal`, `milestones`, `progress`, `documents`, `feedback`, `evaluations`, `reports`, `profile`.
- student: `index`, `projects`, `project`, `proposals`, `proposal`, `milestones`, `progress`, `documents`, `feedback`, `evaluations`, `reports`, `profile`.

---

## 8. Business rules already implemented

**Research**
- Admin adds research directly as `ongoing` or `completed`. A student's request becomes `requested`; a teacher's submission becomes `pending` (teacher becomes its supervisor). **Everything a student or teacher submits waits for the admin** (thesis committee decides).
- Admin **review**: approve -> `ongoing` (supervisor required) or `completed` (supervisor optional), or reject with a required reason. `complete` turns ongoing into completed.
- A teacher may add research **only with `can_add_research = 1`** (checked on every save, so revoking it also blocks edits).
- Owners can edit/withdraw only research that is `requested`, `pending` or `rejected`. Editing a rejected one resubmits it (note cleared).
- Library shows `completed` (+ `ongoing` with the checkbox). `requested/pending/rejected` never appear there; reviewer notes and "created by" are stripped.
- Server ignores tampered fields (a student cannot set status, supervisor, year or members).
- Categories/tags used by research cannot be deleted.

**University**
- A batch carries its own term system (`yearly | semester | trimester`), shift (`Regular | Evening`) and program length. `current_term_no <= total_terms`; the batch's sessions must be of the same type as the batch.
- Only one `is_current` session per type; an inactive session cannot be current; a session used by batches cannot be deleted or change type.
- A department with batches/teachers/students cannot be deleted; a batch with students cannot be deleted. A student's batch must belong to the chosen department.

**Users**
- Passwords are hashed; empty password on edit keeps the old one. Admin cannot delete/deactivate themselves; the last active admin cannot be removed.
- Photo: JPG/PNG/WebP up to 2 MB (`FileUpload` checks real type, extension, random name; old file deleted on replace). Shown as photo, else initials (`Dr. Jannatul Naeem` -> `JN`, titles skipped) in the `.table-user-avatar` design.

---

## 9. Security

Implemented: PDO prepared statements everywhere, `password_hash`, CSRF token (form field + `X-CSRF-Token` header), per-account login lock (5 failures -> 5 minutes, stored in the DB), session id regenerated on login, session cookie `HttpOnly` + `SameSite=Lax`, server-side role guard on every panel page and API, DB re-check of the logged-in user, input validation server-side, output escaping (`x-text` / `htmlspecialchars`), upload validation, root `.htaccess` blocking `.env`, `.git`, `vendor`, `app`, `database`, `docs`, `includes`, directory listing and `*.md/*.sql/*.log`; `uploads/.htaccess` blocks running PHP.

Not done / known: logout is a GET link; no per-IP rate limit; `.env` is still tracked in git; Apache-only protection (Nginx would need equivalent rules; ideal deploy puts `app/`, `vendor/`, `.env` outside the web root).

---

## 10. Conventions (keep following these)

- **Intermediate level, explainable.** No framework tricks, no ORM, no DI container. Plain PHP classes, explicit SQL.
- **Every Alpine function / helper on pages starts with `Sobuj_`** (author watermark): `Sobuj_api`, `Sobuj_userList`, `Sobuj_categoryPage` ...
- **Page scripts** (categories, tags, departments, batches, sessions) are **inline in the page, without comments**, structured as: data functions (`Sobuj_fetchX`, `Sobuj_saveX`, `Sobuj_deleteX`) then one component function. Shared multi-page logic lives in the shared JS files above.
- **Reuse the existing design system** (`main.css`): `table-card-custom`, `table-custom`, `table-user-avatar`, `badge-table success|pending|failed`, `form-control-custom`, `form-select-custom`, `form-label-custom`, `table-btn-action`. Do not invent new styling unless unavoidable.
- Controllers **return arrays**, never echo/redirect/flash; the `api/*.php` file turns them into JSON. Models hold all SQL and the `deleted_at IS NULL` rule.
- Pages print layout only; no PHP data logic in the page body.
- `resetForm()` must restore exactly the initial form shape (a bug that bit three pages).
- Do not combine Bootstrap display utilities (`d-flex`, `d-block`...) with `x-show` on the same element - `!important` beats Alpine's inline `display:none`.
- New panel page: load `app/bootstrap.php` first (it applies the guard), then `admin/includes/header.php` and the panel sidebar.

### Recipe: add a new CRUD module
1. Migration (new numbered file) -> `php migrate.php`.
2. `app/Models/X.php` (paginate/count/find/create/update/softDelete, all with `deleted_at IS NULL`).
3. `app/Controllers/XController.php` (static methods returning arrays, uses `Validation`).
4. `api/x.php` (`Api::start`, `requireRole`, `requireCsrf`, call controller).
5. Page: layout in PHP + inline Alpine script (copy `admin/research/tags.php` as the template).
6. Test: curl for the API, then a real browser run (section 12).

---

## 11. Helpers & rules quick reference

`Validation::validate($data, $rules, $labels = [])` -> `['success', 'errors']`. Rules: `required email min:n max:n in:a,b numeric integer between:a,b date unique:table,col[,ignoreId[,idCol]] unique_active:table,col[,ignoreId]`. Rules can be a `'a|b'` string or an array. Bad rule definitions throw; bad user input never does.

`FileUpload::save($_FILES['photo'], 'photos', 'image')` -> `['success','path','error']`; `FileUpload::delete($path)`. Presets: `image` (JPG/PNG/WebP, 2 MB), `document` (PDF, 5 MB - ready for the document feature).

`Api::start() / requireRole(...) / requireCsrf() / body() / json($data, $status)`.

---

## 12. Testing approach and gotchas

How features were verified (repeat this for new work):
1. `php -l` on all PHP, `node --check` on all JS.
2. API: curl with real sessions (login -> read `csrf-token` meta tag -> send `X-CSRF-Token`), covering guest/wrong-role/CSRF/validation/permission/tampering/soft delete.
3. UI: headless Chrome (installed on this machine) driven over the DevTools protocol (a small node script using the built-in `WebSocket`), loading the real page and clicking/typing; screenshots to look at the result. A **temporary** `zz_*.php` page may be used to set a session; **delete it immediately after** (it would be a login backdoor).
4. Use **real-time Chrome**, not `--virtual-time-budget` (Alpine's `x-show` needs animation frames; virtual time gives false failures). Disable `confirm()` after each navigation (a native dialog hangs headless Chrome).
5. Clean up test data afterwards (the DB should hold only the demo users + your own categories).

Environment gotchas (Windows + Git Bash):
- Long/special-character scripts in a bash heredoc can mangle backslashes or hang - write script files with the editor tool and run them.
- XAMPP MySQL is at `D:\Xampp\mysql\bin\mysql.exe` (user `root`, no password).
- `.htaccess` needs `AllowOverride All` (it is on in this XAMPP).

---

## 13. Next tasks (in this order)

**0. Housekeeping (do first, 10 minutes)**
- Commit the work in logical commits (e.g. login+security, university structure, users, research). `git rm --cached .env` and add `.env` to `.gitignore` (keep `.env.example`).

**1. Student side, part 1** (small, mostly standalone)
- **Student dashboard with real data**: research stats (requested / ongoing / completed), batch + current term, recent requests, library totals. The four project cards (project, milestones, documents, feedback) show "no project yet" until teams/proposals exist.
- **My Profile + change password** for all three roles (shared `api/account.php`): view own details and photo, edit phone/photo, change password (old password required, min 8). Pages: `student/profile.php`, `teacher/profile.php`, admin already edits itself via `admins/manage.php`.

**2. Teams and proposals** (core of the proposal document; fills most student/teacher pages)
- Student creates/joins a team -> submits a proposal (title, abstract, objectives, problem statement, methodology, expected outcome, team members) -> teacher reviews (approve / request revision / reject, revision loop) -> admin assigns the supervisor -> approved proposal becomes a **project**.
- Likely tables: `teams`, `team_members`, `proposals`, `projects`. Decisions to confirm first: team size limits, one proposal vs several per team, whether a proposal can link to a research code, who picks the supervisor.
- Admin pages: `teams/index`, `projects/proposals`, `projects/pending`, `projects/index`, `projects/manage`.

**3. Project follow-up**
- Milestones, progress updates, documents (use `FileUpload` `document` preset), feedback (teacher <-> student).

**4. Evaluation and reports**
- Evaluation criteria (admin, standalone), project/thesis evaluation, results visible to students; reports (research report is possible right now).

**5. Dashboards last** (admin and teacher dashboards still show fixed numbers; they count everything above).

**Small standalone items any time:** System Settings page, Evaluation Criteria CRUD, Research Report, "Promote batch to next term" button, a trash/restore screen for soft-deleted rows, remove the orphan `admin/research/my_research.php`, make logout a POST.

---

## 14. Known gaps

- Teacher/student `profile.php` are static samples; there is no change-password or forgot-password yet.
- Students list is wide (9 columns) and the Actions column can be clipped around 1400px.
- The library has no "Department" column (research has no department by decision).
- The demo teacher has no `teacher_profiles` row (edit them once in the admin).
- `docs/` holds earlier planning notes; they use the word "supervisor" for the role and an older folder plan - this README is current.

---

## 15. How to resume in a new session

1. Read this README (sections 1, 10, 13).
2. Quick health check: `php migrate.php` (should say "Already migrated" for 001-014), open `http://localhost/UPTMS`, log in as admin.
3. Tell Claude which item of section 13 to start. Working agreement: **plan first for anything big, then build in small steps; explain the reasoning so it can be repeated in front of the supervisor; test everything before saying it is done.**
