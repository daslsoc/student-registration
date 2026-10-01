# Backlog

## Code health — deferred Sonar refactors

These stay open on the local SonarQube dashboard (http://localhost:9000,
project `student-registration`) on purpose: real, but each is a
behaviour-sensitive restructure that waits for a reason to touch the code
rather than being split for the metric.

- `app/Http/Controllers/AdminController.php` — `storePaymentOverride()` has
  cognitive complexity 27 (limit 15). The mark-as-paid and revert branches
  (payment rows, allocation, audit line) belong in two private methods or a
  small service, pinned by `PaymentOverrideTest`.
