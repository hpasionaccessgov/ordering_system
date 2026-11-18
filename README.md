<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_dark.svg">
        <source media="(prefers-color-scheme: light)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg">
        <img src="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg" alt="Yii Framework" height="100">
        <img src="web\Cafebara_logo.png" alt="Cafebara Logo" height="100">
    </picture>
    <h1 align="center">Ordering System</h1>
    <br>
</p>

A Yii2 basic application for ordering system. This application is built using Yii2 framework and provides a simple ordering system for products.

## Requirements

- PHP 8.3 or higher
- Composer
- MariaDB 10.4+ or MySQL 8.0+
- Git
  OR Install Laragon v6 for all in one universal pack (https://laragon.org/download/)

## Local setup

1. Clone the repository:
    ```bash
    git clone <repository-url>
    ```
2. Install local dependencies:
    ```bash
    composer install && npm install
    ```
3. Create a `.env` file in the root directory of the project and configure your database connection settings, You can use the provided `.env.example` file as a template
4. Create the database:
    ```bash
    mysql -u root -p -e "CREATE DATABASE ordering_system;"
    ```
    OR use the laragon GUI database manager to create the database using this CREATE query:
    ```bash
    CREATE DATABASE ordering_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
    ```
5. Configure the database in `.env`. For Laragon, a typical local configuration is:
    ```dotenv
    DB_DSN="mysql:host=127.0.0.1;port=3306;dbname=ordering_system"
    DB_USERNAME=root
    DB_PASSWORD=
    ```
    If your MySQL root account has a password, set it in `DB_PASSWORD`.
6. For an existing database, back it up and check the legacy category-column
    removal precautions under **Category permissions** before migrating.
    Run the application migrations:
    ```bash
    php yii migrate --interactive=0
    ```
    For an existing database where Yii RBAC tables were already created by
    Yii's original RBAC migration path, run this once before migrating:
    ```bash
    php yii migrate/mark m261112_000000_create_rbac_tables
    ```
    New databases do not need the `migrate/mark` step.
7. Seed local test data:
    ```bash
    php yii seed/test-data
    ```
    The seeder is additive and idempotent. It adds only missing users and
    products and does not archive, update, delete, or reassign existing data.
8. Start the development server:
    ```bash
    php yii serve
    ```
    OR use the laragon GUI to start the server.
9. Start Laragon Apache and MySQL, then open http://localhost/ordering_system/web/.
10. Register a new account. Registration activates the account and signs you in immediately; new accounts receive the Customer role.
11. Promote your first account to Admin from the project directory:
    ```bash
    php yii rbac/make-admin your-email@example.com
    ```
12. Sign in or reload the page. The admin dashboard is accessible only to users with the Admin role.

## Category permissions

Run `php yii migrate` after updating the application to add the category
permissions. The migration grants them to the built-in `admin` role; existing
custom roles are not changed. In Roles, grant `category.index` and
`category.view`, plus the desired create, update, and delete permissions, to
each role that should manage categories. Search, sorting, and pagination use
the list permission and do not need separate permissions. Selecting
`category.index` in the role editor selects all category actions; uncheck
actions that the role should not have.

Module permissions apply equally to built-in and custom roles. `staff.access`
controls the staff dashboard and, like `admin.access`, permits access to all
orders when the role also has the relevant order permissions. Without either
access permission, order queries remain limited to the user's own orders.

Products use `category_id` as the category source of truth. Before applying the
legacy category-column removal migration, back up the database, stop application
writers, and verify that external reports, exports, and integrations no longer
read `products.category`. The migration checks the non-null foreign key and
existing references before removing that column. Its rollback recreates the
column from current category names, not the original legacy values; restore a
backup if the original values are needed.

An active product remains visible when its category is inactive. New category
assignments must use an active category; an existing product may retain its
unchanged inactive category.

Category-reference tests require the separate `yii2basic_test` database from
`config/test_db.php`. After creating that database, migrate it from Git Bash:

```bash
DB_DSN='mysql:host=127.0.0.1;port=3306;dbname=yii2basic_test' php yii migrate --interactive=0
php vendor/bin/codecept run Unit --filter ProductCategoryTest
```

Never point the test configuration at a development or production database.

## PHP and Composer checks

The locked PHPUnit version requires PHP 8.3. On Laragon, select
`C:\laragon\bin\php\php-8.3.36-1-Win32-vs16-x64` as the active PHP version.

```powershell
php -v
composer check-platform-reqs
```

If PowerShell still resolves XAMPP PHP, use the Laragon executable explicitly:

```powershell
& "C:\laragon\bin\php\php-8.3.36-1-Win32-vs16-x64\php.exe" -v
```

## Troubleshooting `MySQL server has gone away`

The seeder stores sample product images in the database. Confirm MySQL is
running in Laragon and use the same PHP version for migrations and seeding:

```powershell
& "C:\laragon\bin\php\php-8.3.36-1-Win32-vs16-x64\php.exe" yii migrate --interactive=0
& "C:\laragon\bin\php\php-8.3.36-1-Win32-vs16-x64\php.exe" yii seed/test-data
```

If MySQL still disconnects while inserting an image, increase
`max_allowed_packet` in the active MySQL/MariaDB configuration to at least
`64M`, restart MySQL in Laragon, and rerun the seeder. The seeder processes
each new product independently, preserves the original database error, and
does not mutate existing products.

Seed credentials:

| Username | Email | Password | Role |
|---|---|---|---|
| `seed_admin` | `seed.admin@cafebara.test` | `CafebaraTest123!` | Admin |
| `seed_staff` | `seed.staff@cafebara.test` | `CafebaraTest123!` | Staff |
| `seed_customer` | `seed.customer@cafebara.test` | `CafebaraTest123!` | Customer |
