# Student Registration System

This is a standalone PHP Laravel web application designed to manage student registration for a Saturday school. It handles parent and child information, payments via Stripe, email notifications, and administrative features.

## About

This repository provides a Docker-based setup for a small Laravel application, including:

- **PHP-FPM** for running Laravel (optimized for ~10 concurrent processes).
- **Nginx** as a reverse proxy to PHP-FPM.
- **MySQL** database (easily switchable to MariaDB or PostgreSQL).
- **Redis** for caching and session storage.
- **Queue worker** for handling asynchronous tasks, such as email sending.

## Prerequisites
- Docker >= 20
- Docker Compose >= 1.29

## Installation and Usage

1. **Clone** or **download** this repository.

2. Create your `.env` file using the `.env.example` and update the custom settings section with appropriate values

3. Navigate to the project root (where `docker-compose.yml` is located).

4. Run the following command to start the services:

   ```bash
   docker-compose up -d
   ```

5. To access the application container for commands (e.g., migrations, artisan):

   ```bash
   docker exec -it php_app bash
   ```

6. Create admin user:

    ```php
    php artisan tinker
    \App\Models\User::create(['name' => 'Admin User', 'email' => 'enter email', 'password' => bcrypt('enter password')]);
    ```

7. Run optimisation after deployment
 
    ```bash
    php artisan optimize
    ```

## Useful Commands

Here are some SQL and PHP snippets for common administrative tasks:

- **Reset registrations for the new year**:

  ```sql
  UPDATE `parents` SET `registration_status` = 'pending';
  UPDATE `parents` SET `payment_token` = NULL;
  ```

- **Count paid children**:

  ```php
  DB::table('children')->join('parents', 'parent_id', 'parents.id')->where('parents.registration_status', '=', 'completed')->count();
  ```

- **Count total payments**:

  ```php
  \App\Models\Payment::all()->count();
  ```

  Or via SQL:

  ```sql
  SELECT COUNT(*) FROM payments;
  ```

- **Paid Students**

  ```sql
  SELECT c.student_number, c.first_name, c.last_name, p.registration_status
  FROM children c, parents p
  WHERE c.parent_id = p.id
  and p.registration_status = "completed"
  ORDER BY c.student_number;
  ```

- **Export student list**:

  ```sql
  SELECT c.student_number, c.first_name, c.last_name, c.gender, c.day_school_year, c.dhamma_class, c.sinhala_class, p.parent1_email, p.parent2_email
  FROM children c, parents p
  WHERE c.parent_id = p.id
  ORDER BY c.student_number;
  ```

- **List unregistered parents**:

  ```sql
  SELECT `parent1_first_name`, `parent1_last_name`
  FROM parents
  WHERE `registration_status` != 'completed'
  ORDER BY `parent1_first_name`;
  ```

- **WhatsApp contact list**:

  ```sql
  SELECT c.student_number, `parent1_first_name`, `parent1_last_name`, `parent1_email`, `parent1_phone`, `parent2_first_name`, `parent2_last_name`, `parent2_email`, `parent2_phone`, `registration_status`
  FROM children c, parents p
  WHERE c.parent_id = p.id
  ORDER BY c.student_number;
  ```

- **Registered parents' emails**:

  ```sql
  SELECT `updated_at`, `parent1_first_name`, `parent1_last_name`, `parent1_email`, `parent2_first_name`, `parent2_last_name`, `parent2_email`
  FROM parents
  WHERE `registration_status` = 'completed'
  ORDER BY `parents`.`updated_at` DESC;
  ```

- **Input for student attendance**:

  ```sql
  SELECT c.student_number, c.first_name, c.last_name, p.registration_status
  FROM children c, parents p
  WHERE c.parent_id = p.id
  ORDER BY c.student_number;
  ```

**Export for ACL CLS Grant Program Form**
```sql
  SELECT c.student_number, c.last_name, c.first_name, c.gender, c.date_of_birth, c.`residency_status`, c.`day_school_name`, c.`day_school_year`
  FROM children c, parents p
  WHERE c.parent_id = p.id
  and p.registration_status = "completed"
  ORDER BY c.student_number;
```


## Development Setup

### Initial Setup Commands

These commands were used to initialize the project:

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

## TODO

- Handle cases with 0 children in the UI and backend.
- Create additional test files (e.g., ChildTest.php, PaymentTest.php, StudentNumberTrackerTest.php) and ensure model factories are set up.
- Remove DOB field if not needed.
- Prevent form bounce issues.
- Implement "yes, I want to subscribe" email functionality.