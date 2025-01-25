# Spent Now Test

## Table of Contents

-   [Overview](#overview)
-   [Dependencies](#dependencies)
-   [Project Setup](#project-setup)
-   [Features](#features)
    -   [Authentication](#authentication)
    -   [User Management](#user-management)
-   [Testing](#testing)
-   [Conclusion](#conclusion)

---

## Overview

**Spent Now Test** is a simple user management system that includes authentication and role-based access control. It provides RESTful APIs for:

1. **User registration and login**: Securely register and log in users.
2. **Token-Based Authentication**: Implemented using Laravel Passport for secure API access.
3. **Administrative Tasks**: Manage user accounts, roles, and permissions.

The application is designed to enforce role-based permissions, ensuring a secure and efficient system for managing users.

---

## Dependencies

-   **[Laravel](https://laravel.com/docs/11.x)**: A PHP framework for building web applications.
-   **[Laravel Passport](https://laravel.com/docs/11.x/passport)**: Provides OAuth2 server implementation for API authentication.

---

## Project Setup

Follow these steps to set up the project locally:

1. **Install Prerequisites**  
   Ensure the following are installed on your machine:

    - **[PHP](https://www.php.net/downloads)** (version 8.1 or higher)
    - **[Composer](https://getcomposer.org/download/)**

2. **Clone the Repository**

    ```bash
    git clone https://github.com/Ruthiejayjay/spentnow_test.git
    cd spentnow_test

    ```

3. **Set Up Environment File**  
   Copy the example environment file and update it with your configuration:
    ```bash
    cp .env.example .env
    ```

-   Create a database for the application.
-   Update the `.env` file with your database credentials.

4. **Install Dependencies**  
   Run the following command to install required packages:

    ```php
    composer install
    ```

5. **Migrate the Database**  
   Create the necessary tables in your database by running:
    ```php
    php artisan migrate
    ```
6. **Set Up Passport Keys**  
   Generate Laravel Passport client keys for secure token-based authentication:
    ```php
    php artisan passport:client –passport
    ```
7. **Start the Development Server**  
   Run the application locally:
    ```php
     php artisan serve
    ```
    You can now access the application at `http://localhost:8000`.

---

# Features

## **Authentication**

-   Secure user login and registration
-   Token-based API authentication using Laravel Passport.

### API Endpoints

-   **POST** `/api/auth/register`: Register a new user.
-   **POST** `/api/auth/login`: Log in and receive an access token.
-   **POST** `/api/auth/logout`: Revoke the user's access token.

## **User Management**

-   Create, read, update, and delete user accounts.
-   Role-based access control for managing permissions.

### API Endpoints

-   **GET** `/api/users`: Retrieve all users (Admin only).
-   **POST** `/api/users`: Create a new user (Admin only).
-   **GET** `/api/users/{id}`: Retrieve a specific user's profile.
-   **PUT** `/api/users/{id}`: Update a specific user's profile.
-   **DELETE** `/api/users/{id}`: Delete a specific user (Admin only).
-   **PATCH** `/api/users/{id}`: Update a user's role (Admin only).

---

# Testing

### Authentication

Contained in [`AuthenticationTest.php`](tests\Feature\AuthenticationTest.php). These tests cover:

1. Successful register, login and logout.
2. Validation checks for invalid input.
3. Error cases, such as incorrect credentials or unauthorized access.

Run the tests using:
```php
php artisan test --filter AuthenticationTest
```

### User Management

Contained in [`UserTest.php`](tests\Feature\UserTest.php). These tests cover:

1. Successful creation, retrieval, updating, and deletion of user accounts.
2. Validation checks for invalid input.
3. Error handling for unauthorized actions.

Run the tests using:
```php
php artisan test --filter UserTest
```

---

# Conclusion

The **Spent Now Test** project provides a foundational user management system with secure authentication and robust role-based access control. The application includes well-documented APIs and comprehensive test coverage for critical functionalities.

A **[Postman collection](Spent-Now.postman_collection.json)** is also available for testing the API endpoints. You can import it into your Postman workspace for easy testing of the application's features.
