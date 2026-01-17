# student-registration
This system is a stand alone PHP Laravel web application to manage student registration for a Saturday school.

## About
This repository demonstrates a Docker-based setup for a small Laravel app with:

- **PHP-FPM** for running Laravel (max ~10 concurrent processes).
- **Nginx** reverse proxy to PHP-FPM.
- **MySQL** database (optional: can switch to MariaDB or PostgreSQL).
- **Redis** for caching & session storage.
- **Queue worker** to handle asynchronous tasks (like mail).

## Prerequisites
- Docker >= 20
- Docker Compose >= 1.29

## Usage

1. **Clone** or **download** this repository.
2. In the project root (where `docker-compose.yml` is), run:
   ```bash
   docker-compose up -d
   ```

   docker exec -it php_app bash

## Useful commands

* How many children have paid?

```php
DB::table('children')->join('parents','parent_id','parents.id')->where('parents.registration_status','=','completed')->count();
```

* How many payments have been made?

```php
\App\Models\Payment::all()->count();
```

```sql
select c.student_number, c.first_name, c.last_name, c.gender, c.day_school_year, c.dhamma_class, c.sinhala_class, p.parent1_email, p.parent2_email from children c, parents p where c.parent_id=p.id order by c.student_number
```

* parents not registered

```sql
select `parent1_first_name`,`parent1_last_name` from parents where `registration_status` != 'completed' order by `parent1_first_name`;
```

* whats app list

```sql
select c.student_number,`parent1_first_name`, `parent1_last_name`, `parent1_email`, `parent1_phone`, `parent2_first_name`, `parent2_last_name`, `parent2_email`, `parent2_phone`, `registration_status` from children c, parents p where c.parent_id=p.id order by c.student_number
```

* registered parents emails

```sql
select `updated_at`, `parent1_first_name`, `parent1_last_name`, `parent1_email`, `parent2_first_name`, `parent2_last_name`,`parent2_email` from parents where `registration_status` = 'completed' ORDER BY `parents`.`updated_at` DESC
```

* input to student attendance

```sql
select c.student_number, c.first_name, c.last_name, p.registration_status from children c, parents p where c.parent_id=p.id order by c.student_number;
```

## Historical Information

### Initial Setup

```bash
docker run --mount type=bind,src=./,dst=/app composer:2 create-project laravel/laravel school_registration
docker run --mount type=bind,src=./,dst=/app composer:2 require stripe/stripe-php
docker run --mount type=bind,src=./,dst=/app composer:2 require league/csv

docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:model ParentModel -m
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:model Child -m
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:model Payment -m
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:model StudentNumberTracker -m

docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:controller RegistrationController
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:controller AdminController

docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:mail RegistrationConfirmation
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:mail UpdateRegistrationLink
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:controller AuthenticateController

docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:test RegistrationFeatureTest
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:test UpdateRegistrationFeatureTes
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:test CSVImportFeatureTest
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:test AdminFeatureTest

docker run --mount type=bind,src=./,dst=/app composer:2 require --dev laravel/dusk
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan dusk:make RegistrationBrowserTest

docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:factory ParentModelFactory
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:factory ChildModelFactory
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:factory PaymentFactory
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:factory StudentNumberTrackerFactor

docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:migration add_guidelines_acceptance_to_parent_models_table
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:migration add_postcode_to_parent_models_table
docker run --mount type=bind,src=./,dst=/var/www/html php:8-fpm php artisan make:migration add_photography_allowed_to_children_table
```

== TODO
(You’d also have ChildTest.php, PaymentTest.php, StudentNumberTrackerTest.php similarly. Make sure you create model factories if you want to use Model::factory()->create(). For brevity, not all are shown here, but the pattern is the same.)

* todo need to deal with 0 children in ui and backend

\App\Models\User::create(['name' => 'Admin User', 'email' => 'dileepa@codiphisolutions.com.au', 'password' => bcrypt('secret123')]);

remove dob
add postcode
need to prevent bounce in form

* "yes i want to subscribe" email