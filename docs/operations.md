# Operations

Working SQL/PHP snippets for day-to-day administration of the registration
database. These were previously inlined in the README; they live here so the
README stays focused on setup.

> **Run queries through a known-safe path.** Use `make mysql DB=laravel_db`
> (dev) to open a client, or run read-only reports against a copy. The
> destructive queries below (year reset, deletes) should only ever run against
> the production `parents` / `children` tables after a backup.

## Annual reset (start of a new school year)

Marks everyone as unpaid again and clears stale payment tokens so the
pay-again flow works. **Back up first — this touches every parent row.**

```sql
UPDATE `parents` SET `registration_status` = 'pending';
UPDATE `parents` SET `payment_token` = NULL;
```

## Counts & payment reconciliation

Count paid children:

```php
DB::table('children')
    ->join('parents', 'parent_id', 'parents.id')
    ->where('parents.registration_status', '=', 'completed')
    ->count();
```

Count total payments:

```php
\App\Models\Payment::all()->count();
```

```sql
SELECT COUNT(*) FROM payments;
```

## Reports

Paid students:

```sql
SELECT c.student_number, c.first_name, c.last_name, p.registration_status
FROM children c, parents p
WHERE c.parent_id = p.id
  AND p.registration_status = "completed"
ORDER BY c.student_number;
```

Export student list (core data):

```sql
SELECT c.student_number, c.first_name, c.last_name, c.gender, c.day_school_year,
       c.dhamma_class, c.sinhala_class, p.parent1_email, p.parent2_email
FROM children c, parents p
WHERE c.parent_id = p.id
ORDER BY c.student_number;
```

Unregistered parents:

```sql
SELECT `parent1_first_name`, `parent1_last_name`
FROM parents
WHERE `registration_status` != 'completed'
ORDER BY `parent1_first_name`;
```

WhatsApp contact list:

```sql
SELECT c.student_number, `parent1_first_name`, `parent1_last_name`, `parent1_email`,
       `parent1_phone`, `parent2_first_name`, `parent2_last_name`, `parent2_email`,
       `parent2_phone`, `registration_status`
FROM children c, parents p
WHERE c.parent_id = p.id
ORDER BY c.student_number;
```

Registered parents' emails (most recent first):

```sql
SELECT `updated_at`, `parent1_first_name`, `parent1_last_name`, `parent1_email`,
       `parent2_first_name`, `parent2_last_name`, `parent2_email`
FROM parents
WHERE `registration_status` = 'completed'
ORDER BY `parents`.`updated_at` DESC;
```

Input for student attendance:

```sql
SELECT c.student_number, c.first_name, c.last_name, p.registration_status
FROM children c, parents p
WHERE c.parent_id = p.id
ORDER BY c.student_number;
```

Export for ACL CLS Grant Program Form:

```sql
SELECT c.student_number, c.last_name, c.first_name, c.gender, c.date_of_birth,
       c.`residency_status`, c.`day_school_name`, c.`day_school_year`
FROM children c, parents p
WHERE c.parent_id = p.id
  AND p.registration_status = "completed"
ORDER BY c.student_number;
```

## Create an admin user

```php
php artisan tinker
\App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'enter email',
    'password' => bcrypt('enter password'),
]);
```

## Post-deploy

```bash
php artisan optimize
```

> If you ever see tests or the app reading a stale/wrong database or config,
> clear the compiled caches: `php artisan optimize:clear` (or delete
> `bootstrap/cache/{config,routes-v7,events,packages,services}.php`). A stale
> `bootstrap/cache/config.php` overrides `.env` entirely. See
> [security.md](security.md) and the test-database guard rail in `phpunit.xml`.
