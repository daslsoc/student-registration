# Student Registration System

A Laravel web application for managing student registration at a Saturday
school. It handles parent and child information, Stripe payments, email
notifications, and administrative features (lists, CSV import/export).

## Stack

- **Laravel 12** (PHP 8.2)
- **PHP-FPM + Nginx**, **MySQL 8**, **Redis** (cache/session/queue), and a
  **queue worker** — all via Docker Compose.
- **Vite + Tailwind** for assets, **Stripe** for payments.

## Prerequisites

- Docker >= 20 and Docker Compose v2
- Node 18+ on the host (the JS test/build tooling runs on the host; PHP tooling
  runs inside the `app` container)

## Quick start

```bash
cp .env.example .env          # then fill in the custom settings section
make build                    # build the Docker images
make up                       # start the dev stack
make install                  # composer (in container) + npm (host) deps
make db-setup                 # create + migrate the test/dusk databases
make artisan ARGS="migrate"   # migrate the dev database
```

The app is then served at **http://localhost:8090**.

Create an admin user and run post-deploy optimisation: see
[docs/operations.md](docs/operations.md).

Run `make help` to list every target.

## Testing

PHP runs in the `app` container; JavaScript (Vitest) runs on the host; Dusk
drives a Selenium container.

| Goal | Command |
|------|---------|
| Unit + Feature (PHPUnit) | `make test` |
| Just Unit / just Feature | `make test-unit` / `make test-feature` |
| PHP coverage (HTML) | `make coverage` → `tests/coverage/index.html` |
| JS tests (Vitest) | `make js-test` |
| JS coverage (HTML) | `make js-coverage` → `tests/js-coverage/index.html` |
| Browser tests (Dusk) | `make test-dusk` |
| Dusk coverage (which pages are untested) | `make dusk-coverage` |
| Everything | `make test-all` |
| Lint (Pint, read-only) | `make lint` |
| Auto-format (Pint) | `make lint-fix` |

**Databases:** `laravel_db` is dev; `student_reg_test` is the PHPUnit suite;
`student_reg_dusk` is the browser suite. The test database is pinned in
`phpunit.xml` (`force="true"`) and re-asserted by a guard in
`tests/TestCase.php`, so a test run can never touch the dev/prod database. See
[docs/security.md](docs/security.md) for why this guard exists.

## Custom configuration

The app reads school-specific values (name, pricing, minimum child age,
WhatsApp link, Google Analytics id, Stripe secret) from the environment — see
the "Custom settings" block in `.env.example` and `config/custom.php`.

## Attendance integration (class allocation + sync API)

When a parent completes payment, each child is **auto-allocated to a class**
from their day-school year, and the sibling **student-attendance** app pulls
that allocation to enrol them. This app is the **source of truth** for
allocations.

- **The rule lives in config** — `config/integration.php` maps each
  `day_school_year` (Pre School … Grade 12) to a class (`Class A`…`Class E`).
  Edit the bands there without touching code.
- **At payment** (`RegistrationController::handleSuccess`), `ClassAllocator`
  sets `children.allocated_dhamma_class` and `allocated_sinhala_class` (both to
  the same class initially). The existing `dhamma_class` / `sinhala_class` are
  left as last-year history.
- **Admins can override** at `/admin/allocations` (behind login) — for
  exceptions or moves. Saving bumps `children.updated_at`, which is the sync
  clock.
- **The confirmation email** now tells parents the allocated class.
- **The API** (token-gated via `INTEGRATION_API_TOKEN`):
  `GET /api/integration/changes?since=<ts>` returns
  `{ last_changed_at, count, students:[…] }` — the paid children and their
  allocated classes, filtered to those changed since `?since=` so the attendance
  app only pulls deltas. No parent/contact/DOB data is exposed.

> Email runs inline in production (`QUEUE_CONNECTION=sync`), so no worker is
> needed for the confirmation/allocation email.

## Documentation

- [docs/deployment.md](docs/deployment.md) — production deploy checklist for
  the shared server (no Docker; files-on-top).
- [docs/operations.md](docs/operations.md) — admin SQL/PHP snippets, annual
  reset, reports, admin-user creation.
- [docs/security.md](docs/security.md) — security review, fixes, and
  recommended follow-ups.

## TODO

- Handle the 0-children case in the UI and backend.
- Add `ChildTest` / `PaymentTest` / `StudentNumberTrackerTest`.
- Remove the DOB field if not needed.
- Prevent form bounce issues.
- Implement the "yes, I want to subscribe" email functionality.
