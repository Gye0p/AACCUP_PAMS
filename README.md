# AACCUP PAMS

AACCUP PAMS is an accreditation program management system for organizing accreditation cycles, compliance activities, evidence, internal accreditor reviews, monitoring reports, and program progress.

## Stack

- **Frontend:** React 19, Vite, React Router, TanStack Query, Tailwind CSS
- **Backend:** PHP 8.1+, Symfony 6.4, API Platform, Doctrine ORM
- **Authentication:** JWT
- **Database:** MySQL 8
- **Local database tools:** Docker Compose and phpMyAdmin

## Project Structure

```text
backend/    Symfony API, entities, migrations, fixtures, and authentication
frontend/   React/Vite web application
```

## Requirements

- PHP 8.1 or newer with Composer
- Node.js and npm
- MySQL 8, or Docker Desktop for the included database services
- Symfony CLI is recommended for local backend development

## Setup

### 1. Start the database

From `backend/`:

```bash
docker compose up -d db phpmyadmin
```

The MySQL database is available on port `3306`. phpMyAdmin is available at `http://127.0.0.1:8081`.

### 2. Configure and start the backend

From `backend/`:

```bash
composer install
php bin/console lexik:jwt:generate-keypair
php bin/console doctrine:migrations:migrate
symfony server:start --port=8080
```

The API will be available at `http://127.0.0.1:8080`.

The default development database configuration expects:

```text
mysql://root:root@127.0.0.1:3306/aaccup_pams
```

Set `DATABASE_URL` in `backend/.env.local` when using different database credentials. Never commit private JWT keys or local environment files.

### 3. Start the frontend

From `frontend/`:

```bash
npm install
npm run dev
```

Open `http://127.0.0.1:5173`. Vite forwards `/api` requests to the backend at `http://127.0.0.1:8080`.

## Useful Commands

### Frontend

```bash
npm run build
npm run lint
```

### Backend

```bash
php bin/console about
php bin/console doctrine:migrations:migrate
php bin/console cache:clear
```

## Main Features

- JWT-based login and role-protected routes
- Accreditation cycle and area management
- Compliance activity tracking
- Evidence upload and review workflows
- Internal accreditor review
- Cycle dashboard and Gantt chart views
- Monitoring reports and administrative management

## License

This project is proprietary. See the repository owner for usage and distribution permissions.