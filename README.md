This is a mini E-commerce RESTful API built with Laravel 12. It includes user authentication, product management, cart, orders, Stripe/PayPal payment simulation, and localization.

Requirements:
PHP 8.2 or higher
Composer
MySQL
Laravel 12.x


//The Steps for running the project:
After cloning the Github Repo, Follow these Steps:
1. Copy the `.env.example` to `.env` (Write in the terminal: cp .env.example .env)
2. php artisan key:generate (to generate application key)
3. DB_DATABASE=your_database_name
    DB_USERNAME=your_database_user
    DB_PASSWORD=your_database_password
4. php artisan migrate (to run migrations in the database)
5. php artisan db:seed
6. php artisan storage:link
7. php artisan serve