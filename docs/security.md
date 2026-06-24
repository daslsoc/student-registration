# Security Notes

Summary of the security review of the registration app and the hardening
applied. Scope: the web app code (controllers, models, routes, middleware),
not infrastructure/hosting.

## What was checked and is OK

- **SQL injection** — none found. Every database access goes through Eloquent
  or the query builder with bound parameters. There are no `DB::raw`,
  `whereRaw`, `DB::select`, or string-concatenated queries anywhere in `app/`.
  The CSV import (`RegistrationController::handleCsvImport`) parses with
  `League\Csv` and writes via mass assignment against `$fillable` — no query
  string is ever built from file content.
- **CSRF** — all state-changing forms post through the `web` middleware group
  and include `@csrf`. Verified across the registration, update, retrieve,
  import, and login views.
- **Mass assignment** — every model declares an explicit `$fillable`. All
  controller writes pass a whitelisted `$request->only([...])` array (or a
  validated array), never `$request->all()`, so user input cannot reach
  sensitive columns (`registration_status`, `payment_token`, `update_token`).
- **Authorization** — every `/admin/*` route (parent/student lists, CSV
  export, CSV import) is behind the `auth` middleware group. Only seeded admin
  users can authenticate.
- **Payment replay** — `payment_token` is single-use: it is checked on the
  Stripe success callback and then nulled, so the success URL cannot be
  replayed to record a second payment.
- **Update links** — the email update flow uses a 64-char random token with a
  4-hour expiry **and** a Laravel signed URL (`temporarySignedRoute`).

## Fixes applied in this change

- **Dev/production database wipe (critical).** `phpunit.xml` used `<env>` tags,
  which PHPUnit 11 writes only to `$_ENV`; Laravel reads `$_SERVER` first, so
  the test database override was ignored and the suite ran against the dev DB.
  A stale `bootstrap/cache/config.php` made it worse — a cached config
  overrides `.env` entirely and pinned the **production** database name. Both
  meant `RefreshDatabase` could `migrate:fresh` a real database. Fixes:
  - `phpunit.xml` now uses `<server ... force="true">` and pins
    `DB_DATABASE=student_reg_test`.
  - `tests/TestCase.php` adds a code guard that aborts the run unless the
    connected database is `student_reg_test` / `student_reg_dusk` — this holds
    even if a cached config is present.
- **Timing-safe token comparison.** The Stripe success-token check now uses
  `hash_equals()` instead of `!==`.
- **Token no longer logged.** `sendUpdateLink` previously logged the full
  signed update URL (which embeds the single-use token). It now logs only the
  parent id.
- **Security response headers.** New `App\Http\Middleware\SecurityHeaders`
  (appended to the `web` group) sets `X-Content-Type-Options: nosniff`,
  `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`,
  `X-Permitted-Cross-Domain-Policies: none`, and HSTS over HTTPS. Covered by
  `tests/Feature/SecurityHeadersTest.php`.
- **Production env guidance.** `.env.example` now documents
  `APP_DEBUG=false` and `SESSION_SECURE_COOKIE=true` for production.

## Recommended follow-ups (not changed here)

These are real improvements deliberately left out of this change to avoid
altering payment behaviour / mass-assignment semantics without owner sign-off:

1. **Trust the server, not the query string, for the recorded amount.**
   `handleSuccess` records `amount_paid` from `?amount=` in the success URL,
   which the user controls. The actual Stripe charge is computed server-side
   and is correct, but the stored figure is forgeable. Recompute it from the
   child count + `config('custom.pricing')` instead.
2. **Drop sensitive columns from `$fillable`.** Remove `payment_token`,
   `update_token`, `token_expires_at`, and `registration_status` from
   `ParentModel::$fillable` and set them via `forceFill()->save()` in the
   controller. Defense-in-depth against a future `create($request->all())`.
3. **Move validation into FormRequest classes** (`StoreRegistrationRequest`,
   etc.) so rules live next to authorization and are reused by the update path.
4. **Add a Content-Security-Policy.** The public pages load Bootstrap/jQuery
   from jsDelivr and Google Analytics, so a CSP needs an allow-list, e.g.
   `script-src 'self' https://cdn.jsdelivr.net https://www.googletagmanager.com`.
   Start in `Content-Security-Policy-Report-Only` mode.
5. **Set `APP_DEBUG=false` and `SESSION_SECURE_COOKIE=true`** in the production
   `.env` (the example documents this; verify the live value).
