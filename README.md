<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_dark.svg">
        <source media="(prefers-color-scheme: light)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg">
        <img src="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg" alt="Yii Framework" height="100">
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
4. Start Laragon Apache and MySQL, then open http://localhost/ordering_system/web/.
Alternatively, you can start the development server via cli:
    ```bash
    php yii serve
    ```

5. Create the database:
    ```bash
    mysql -u root -p -e "CREATE DATABASE ordering_system;"
    ```
    OR use the laragon GUI database manager to create the database using this CREATE query:
    ```bash
    CREATE DATABASE ordering_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
    ```
6. Configure the database in `.env`. For Laragon, a typical local configuration is:
    ```dotenv
    DB_DSN="mysql:host=127.0.0.1;port=3306;dbname=ordering_system"
    DB_USERNAME=root
    DB_PASSWORD=
    ```
    If your MySQL root account has a password, set it in `DB_PASSWORD`.
7. Run the application migrations:
    ```bash
    php yii migrate
    ```
    For an existing database where Yii RBAC tables were already created by
    Yii's original RBAC migration path, run this once before migrating:
    ```bash
    php yii migrate/mark m261112_000000_create_rbac_tables
    ```
    New databases do not need the `migrate/mark` step.
    Order status changes create in-app notifications for the customer.
    Sign in and open Notifications to view them or mark them as read.
    Notifications are created for future status changes, not past history.
8. Seed local test data:
    ```bash
    php yii seed/test-data
    ```
    OR Fresh data reset and import
    ```bash
    php yii seed/fresh
    The seeder is additive and idempotent. It adds only missing users and
    products and does not archive, update, delete, or reassign existing data.
    OR use the laragon GUI to start the server.
5.  Sign in or reload the page.

Seed credentials:

| Username | Email | Password | Role |
|---|---|---|---|
| `seed_admin` | `seed.admin@cafebara.test` | `CafebaraTest123!` | Admin |
| `seed_staff` | `seed.staff@cafebara.test` | `CafebaraTest123!` | Staff |
| `seed_customer` | `seed.customer@cafebara.test` | `CafebaraTest123!` | Customer |
