# Laravel ERP Integration

## Project Overview

This project demonstrates a pragmatic integration of Laravel 12 with a legacy ColdFusion ERP system using a shared MySQL database. The focus is on production stability, legacy compatibility, and appropriate use of Laravel features.

## Technical Implementation

### Legacy Table Integration (Part 1)

The `Order` model (`app/Models/Order.php`) is configured to work with the existing schema without modification:

-   **Table Name**: Explicitly set to `orders`.
-   **Primary Key**: Configured as `order_id` (non-standard).
-   **Timestamps**: Disabled standard timestamps; `created_at` handled manually/read-only.
-   **Safety**: Mass assignment protection enabled via `$fillable`.

### Read-Only Views (Part 2)

-   **Web View**: Standard Blade template displaying latest 50 orders.
-   **API**: JSON endpoint for potential frontend integration.
-   **Filtering**: Implemented efficient status filtering.
-   **Sorting**: Utilizing database indexes (`created_at DESC`).

### Safe Write Operations (Part 3)

The `OrderStatusService` handles state changes with strict controls:

-   **Concurrency**: Uses `SELECT ... FOR UPDATE` to prevent race conditions.
-   **Integrity**: Enforces valid state transitions via defined allow-lists.
-   **Isolation**: Wraps updates in transactions to ensure atomicity.

## Short Written Answers (Part 4)

# Technical Decisions & Strategy

## 1. Running Laravel alongside ColdFusion

-   **Infrastructure**: Use Nginx/Apache as a reverse proxy. Route specifically defined paths (e.g., `/api`, `/new-module`) to Laravel and catch-all rest to ColdFusion.
-   **Database**: Both applications connect to the same MySQL instance. Laravel treats tables as "read-heavy" or wraps writes in strict transactions.
-   **Sessions**:
    -   **Simple**: Keep sessions separate. Users login to both or use a simple SSO token.
    -   **Integrated**: Share a database session table (requires compatible serialization) or use Redis as a shared session store.

## 2. When to bypass Eloquent for Raw SQL

-   **High-Volume Bulk Updates**: Updating thousands of rows is memory-heavy in Eloquent. Use `DB::statement` for direct `UPDATE` queries.
-   **Complex Reporting**: Queries involving multiple sub-selects, aggregations, or complex joins where Eloquent's hydration overhead is unnecessary.
-   **Legacy Stored Procedures**: Creating database-level procedures that must be called directly.
-   **Database Specifics**: Utilizing specific MySQL features (e.g., specific index hints, window functions) not exposed strictly through the query builder.

## 3. Zero Downtime Deployment

-   **Blue/Green Deployment**: Spin up the new version (Green) alongside the old (Blue). Switch load balancer traffic once Green is healthy.
-   **Database Migrations**:
    -   **Expand/Contract Pattern**: Never rename or drop columns in active use.
    -   **Step 1**: Add new columns (nullable). Deploy code that writes to both (or handles old/new).
    -   **Step 2**: Backfill data.
    -   **Step 3**: Deploy code that uses new columns.
    -   **Step 4**: Remove old columns only after the old code is completely deprecated.
-   **Feature Flags**: Wrap new functionality in configuration flags to enable/disable features without code rollbacks.

## Setup Instructions

1.  **Configure Environment**
    Copy `.env.example` to `.env` and set database credentials.
    Ensure `SESSION_DRIVER=file` to avoid schema conflicts.

2.  **Install Dependencies**

    ```bash
    composer install
    ```

3.  **Run Application**
    ```bash
    php artisan serve
    ```

## Key Decisions

-   **No Migrations**: Using existing schema availability.
-   **File Sessions**: avoiding table creation conflicts in legacy DB.
-   **Raw SQL Availability**: implemented `indexRaw` method to demonstrate performance alternatives.

## Testing

-   **API Test**: `curl http://localhost:8000/api/orders`
-   **Status Update**: `POST /api/orders/{id}/status`

### Core Files

-   **app/Models/Order.php**: Fully configured legacy table model with custom primary key, timestamp settings, and strict state controls.
-   **app/Http/Controllers/OrderController.php**: Implements read-only views (Web/API) and safe write operations.
-   **app/Services/OrderStatusService.php**: Handles transaction-safe status updates with row locking.
-   **routes/web.php & api.php**: Routes for Blade views and JSON endpoints.
