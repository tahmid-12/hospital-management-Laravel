# Hospital Management System API

A Laravel 13 backend for a hospital management platform built with PostgreSQL, UUID primary keys, API versioning, and Laravel Sanctum authentication.

## Stack

- Laravel 13
- PHP 8.3+
- PostgreSQL 17+
- Laravel Sanctum
- UUID-based primary keys
- Route ? Controller ? Service ? Model architecture

## Project goals

- Manage departments, doctors, patients, appointments, medicines, and rooms
- Secure the API with Sanctum tokens
- Keep database naming PostgreSQL-safe for local development
- Follow lower snake_case and versioned API endpoints under `/api/v1`

## Local PostgreSQL setup

This project uses PostgreSQL-safe identifiers because PostgreSQL can fail when the default schema resolves from a role or database name that contains hyphens. For example, `Hospital-Management` can break the default `$user` search path on local Windows setups.

Use the following local values in the application environment:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hospital_management
DB_USERNAME=hospital_management
DB_PASSWORD=123456
```

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan serve
```

## Authentication

Auth endpoints are available under `/api/v1/auth`:

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`

The API uses Sanctum token authentication and all resource routes are protected with `auth:sanctum`.

## Core API modules

Protected resource endpoints:

- `GET /api/v1/departments`
- `POST /api/v1/departments`
- `GET /api/v1/departments/{id}`
- `PUT /api/v1/departments/{id}`
- `DELETE /api/v1/departments/{id}`
- `GET /api/v1/doctors`
- `POST /api/v1/doctors`
- `GET /api/v1/doctors/{id}`
- `PUT /api/v1/doctors/{id}`
- `DELETE /api/v1/doctors/{id}`
- `GET /api/v1/patients`
- `POST /api/v1/patients`
- `GET /api/v1/patients/{id}`
- `PUT /api/v1/patients/{id}`
- `DELETE /api/v1/patients/{id}`
- `GET /api/v1/appointments`
- `POST /api/v1/appointments`
- `GET /api/v1/appointments/{id}`
- `PUT /api/v1/appointments/{id}`
- `DELETE /api/v1/appointments/{id}`
- `GET /api/v1/medicines`
- `POST /api/v1/medicines`
- `GET /api/v1/medicines/{id}`
- `PUT /api/v1/medicines/{id}`
- `DELETE /api/v1/medicines/{id}`
- `GET /api/v1/rooms`
- `POST /api/v1/rooms`
- `GET /api/v1/rooms/{id}`
- `PUT /api/v1/rooms/{id}`
- `DELETE /api/v1/rooms/{id}`

## Architecture

The application follows the required structure:

- Route definition in `routes/api.php`
- Controller logic in `app/Http/Controllers/Api/V1`
- Business logic in `app/Services`
- Persistence and relationships in `app/Models`
- API JSON shaping in `app/Http/Resources`
- PostgreSQL schema definitions in `database/migrations`

## Notes

- UUID primary keys are used across all major models.
- Lower snake_case naming conventions are used for schema fields.
- Validation is performed in controller methods using Laravel validators.
- Errors return a consistent JSON payload with `success`, `message`, and optional `errors` keys.
