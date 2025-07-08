# Test Assessment Application

A full-stack web application built with Laravel (backend) and React with Inertia.js (frontend), featuring user authentication, role-based access control, and user profile management.

# AI Use in this project to speed up the assesment

I used AI for some of the crud end point and most of the docker configuration.

## Tech Stack

### Backend
- **PHP 8.3**
- **Laravel 12** - PHP framework
- **MySQL** - Database
- **PHP-FPM** - FastCGI Process Manager
- **Composer** - PHP dependency manager

### Frontend
- **React 18** - JavaScript library for building user interfaces
- **Inertia.js** - Server-side routing and client-side rendering
- **Tailwind CSS** - Utility-first CSS framework
- **Vite** - Next Generation Frontend Tooling
- **TypeScript** - Type-safe JavaScript
- **Radix UI** - Unstyled, accessible components

## Prerequisites

- Docker and Docker Compose
- Node.js 20.x
- PHP 8.3
- Composer
- MySQL

## Local Development Setup

### Using Docker (Recommended)

1. Clone the repository:
   ```bash
   git clone [repository-url]
   cd test-assesment
   ```

2. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```

3. Start the containers:
   ```bash
   docker-compose up -d
   ```
4. Access the application at: http://localhost:8000


## Default Admin Credentials

- **Email**: admin@example.com
- **Password**: password

## Features

- User authentication (register, login, password reset)
- Role-based access control (Admin/User)
- User profile management
- RESTful API endpoints
- Responsive design with Tailwind CSS

## API Documentation

API endpoints are documented in the Postman collection located at `/postman/Test Assessment API.postman_collection.json`
