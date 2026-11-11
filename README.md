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
- PHP 8.2 or higher
- Composer
- MySQL 8.0.30
- Git
OR Install Laragon for all in one universal pack (https://laragon.org/download/)

## Local setup
1. Clone the repository:
   ```bash
   git clone <repository-url>
    ```
2. Install local dependencies:
   ```bash
   composer install
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
    COLLATE utf8mb4_0900_ai_ci;
    ```
5. Run the migrations to set up the database schema:
   ```bash
   php yii migrate
   ```
6. Start the development server:
   ```bash
   php yii serve
   ```
   OR use the laragon GUI to start the server.
7. Start the laragon apache and MySQL services, then open the link: http://localhost/ordering_system/web/
8. Register a sample account
9. in the runtime\mail there would be a generated .eml there, open it with a email type such as outlook app because file transport is enabled for testing local random emails