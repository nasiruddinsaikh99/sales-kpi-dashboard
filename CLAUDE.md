# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sales KPI Dashboard for Syntrex - A PHP-based web application for tracking and visualizing sales agent performance metrics. The system allows admins to upload CSV files containing KPI data and provides dashboards for both administrators and agents to view performance metrics.

## Architecture

### Routing System
- Custom router in [index.php](index.php) handles all requests
- Base path: `/sales-kpi-dashboard`
- Routes defined as array mapping URI to Controller/Action pairs
- `.htaccess` rewrites all non-file requests to index.php

### MVC Pattern
- **Models** ([src/Models/](src/Models/)): Database interaction via PDO
  - `User`: Authentication and user management (agents and admins)
  - `Upload`: Batch upload records
  - `KpiRecord`: Individual agent performance records
  - `Setting`: Application settings storage

- **Controllers** ([src/Controllers/](src/Controllers/)): Business logic and request handling
  - `AuthController`: Login/logout
  - `AdminController`: Admin dashboard, records, settings
  - `DashboardController`: Agent dashboard
  - `UploadController`: CSV import processing

- **Views** ([src/Views/](src/Views/)): PHP templates organized by role
  - `admin/`: Admin interface views
  - `agent/`: Agent interface views
  - `auth/`: Login views
  - `layouts/`: Shared layouts
  - `partials/`: Reusable components

### Database
- MySQL database with PDO connection
- Database config: [config/database.php](config/database.php)
- Schema includes: `users`, `uploads`, `kpi_records`, `settings`
- Foreign keys with CASCADE delete for data integrity

## Common Commands

### Database Setup
```bash
# Initialize database and create tables
php setup_db.php

# Import employees from CSV
php import_employees.php

# Seed demo data for testing
php seed_demo_data.php
```

### Development
```bash
# Check database connection
php check_db.php

# Test CSV import
php test_import.php
```

### Default Credentials
- Admin: `admin@example.com` / `admin123`
- Agents: Auto-generated during import with password `Welcome123`

## CSV Upload System

The [UploadController](src/Controllers/UploadController.php) handles complex CSV import with intelligent column mapping.

### Key Features
- **Flexible header matching**: Supports variations in column names (e.g., "GROSS PROFIT" vs "gross profit (rq)")
- **Dynamic field mapping**: Maps 50+ possible CSV columns to database fields
- **Accelerator columns**: Special handling for "Accel %" columns that appear after parent metrics
- **Data cleaning**: Handles currency formatting, percentages, negative values in parentheses
- **Agent matching**: Case-insensitive name matching (exact then partial)

### CSV Format Expected
- Row 1: Headers (e.g., "Individual Employee", "GROSS PROFIT", "Net GP after Chargebacks", etc.)
- Row 2+: Agent data rows
- Agent name in first column
- See `buildColumnMap()` method in [UploadController.php](src/Controllers/UploadController.php:122-206) for full column mapping

### Adding New CSV Columns
1. Add database column to `kpi_records` table
2. Update `$fieldMappings` array in `buildColumnMap()` method
3. Add field to appropriate cleaning method (`isDecimalField()` or `isIntegerField()`)

## Session & Authentication

- Session-based authentication (PHP sessions)
- Two roles: `admin` and `agent`
- Role determines dashboard redirect and access control
- Check `$_SESSION['user_role']` in controllers for authorization

## Frontend

- TailwindCSS via CDN for styling
- Chart.js for data visualization
- No build process required - all assets served directly
- Theme toggle (dark mode) stored in localStorage

## Key Business Logic

### Agent Dashboard
- Shows agent's own historical KPI records
- Filtered by history visibility setting (default: 3 months)
- Displays performance trends over time

### Admin Dashboard
- Global stats across all agents
- Monthly trend charts (Net GP, Gross Profit)
- Upload management (view, delete batches)
- Settings control (password change, history visibility)

## Database Schema Notes

- `uploads.for_month`: DATE field storing first day of month (YYYY-MM-01)
- `kpi_records.agent_name_snapshot`: Stores agent name at time of upload (preserved even if user renamed)
- `kpi_records` has 50+ columns for various KPI metrics
- Foreign keys ensure cascading deletes when uploads removed

## Important Patterns

### Model Usage
Models use instance methods after construction:
```php
$model = new KpiRecord();
$records = $model->findByUserId($userId);
```

Some models also have static utility methods:
```php
User::createAgentIfNotExists($name);
Setting::get('key', 'default');
```

### Database Connection
Singleton pattern in Database class:
```php
$pdo = Database::connect();
```

### View Rendering
Controllers include views directly:
```php
require __DIR__ . '/../Views/admin/dashboard.php';
```
Views have access to variables set in controller scope.
