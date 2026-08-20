# Complaint Management System

A Core PHP and MySQL complaint-management system for policy-related complaints. Users can register, submit and track complaints, while administrators manage users, roles, complaint reasons, assignments, statuses, and audit history.

## Features

### User Features

- Registration with server-assigned `pending` status and `user` role
- Approved-user login and secure logout
- Blocked/pending account enforcement
- Profile viewing and name/email updates
- User dashboard with account details and ownership-scoped complaint counts
- Dummy policy guidance on the home page
- Complaint creation with active reason selection
- Server-controlled complaint priority derived from the selected reason
- Multiple PDF, JPG, JPEG, and PNG attachments
- User-owned complaint list and details
- Read-only complaint history
- Ownership-protected attachment downloads

### Admin Features

- Database-backed admin authorization
- Admin dashboard with user and complaint counts
- User list and user details
- User status management for `pending`, `approved`, and `blocked`
- Protected role management with safe deletion checks
- Complaint reason management with priority and active/inactive state
- Complaint list with status, priority, and search filters
- Complaint details with complainant and assignee information
- Assignment to eligible approved users
- Complaint status changes
- Transactional assignment and status history
- Read-only complaint history view
- Admin-authorized attachment downloads

### Security Features

- `password_hash()` and `password_verify()`
- CSRF protection for state-changing requests
- Strict, cookies-only PHP sessions with secure cookie attributes
- Session ID regeneration after login
- Database-backed admin authorization
- Account-status revalidation on protected requests
- PDO prepared statements
- XSS-safe output escaping
- Session-derived user identity and complaint ownership
- Server-controlled complaint priority and status
- Upload count, size, extension, and MIME/content validation
- Generated attachment filenames
- Path traversal protection
- Apache denial of direct storage and upload access
- Transactional complaint creation, assignment, and status history

## Architecture

The project uses a feature-first Core PHP structure. Related presentation, request handling, and business/database logic stay together by feature instead of being forced into global `Controllers/`, `Models/`, and `Views/` directories.

Small features are allowed to use fewer files. The project does not force every feature to contain a form, API, service, CSS file, and JavaScript file.

```text
complaint-system/
├── app/
│   ├── auth/
│   ├── profile/
│   ├── dashboard/
│   ├── complaints/
│   ├── admin/
│   │   ├── dashboard/
│   │   ├── users/
│   │   ├── roles/
│   │   ├── reasons/
│   │   ├── complaints/
│   │   └── complaint-history/
│   └── shared/
│       ├── helpers/
│       ├── middleware/
│       └── security/
├── assets/
│   ├── css/main.css
│   └── js/main.js
├── config/database.php
├── database/schema.sql
├── storage/uploads/
└── index.php
```

This is plain PHP, not Laravel, Symfony, an MVC framework, or an ORM.

## Technology Stack

- PHP
- MySQL or MariaDB
- PDO with native prepared statements
- HTML
- CSS
- Vanilla JavaScript
- Apache-compatible `.htaccess` storage protection

Axios and SweetAlert are not currently used. They were not necessary for the implemented server-rendered workflows.

## Requirements

The code has been syntax-tested with PHP 8.1 CLI and PHP's MySQL PDO driver. PHP 8.1 or newer is recommended. A MySQL/MariaDB server with InnoDB and `utf8mb4` support is required.

Required PHP capabilities include:

- PDO MySQL
- Fileinfo
- Sessions
- File uploads
- `mbstring`

## Installation

### 1. Clone the repository

```bash
git clone <your-repository-url>
cd complaint-system
```

### 2. Create the database

Import the schema:

```bash
mysql -u <database-user> -p < database/schema.sql
```

### 3. Configure environment variables

The application reads database configuration from environment variables. See [Environment Configuration](#environment-configuration).

For the PHP built-in server:

```bash
export CMS_DB_HOST=127.0.0.1
export CMS_DB_PORT=3306
export CMS_DB_NAME=complaint_system
export CMS_DB_USER=<database-user>
export CMS_DB_PASSWORD=<database-password>
```

### 4. Configure the web server

For Apache, point an alias or document root at this project and allow the included `.htaccess` files. The upload directory must be writable by the PHP runtime, while direct web access to `storage/` must remain denied.

For local development, run:

```bash
php -S 127.0.0.1:8091 -t .
```

Use one host consistently so the PHP session cookie is reused.

### 5. Set up the first administrator

The schema seeds the `user` and `admin` roles, but it does not create an administrator account. Register a user, then promote that account through a controlled database operation:

```sql
UPDATE users AS u
INNER JOIN roles AS r ON r.name = 'admin'
SET u.status = 'approved', u.role_id = r.id
WHERE u.email = '<admin-email>';
```

Replace the placeholder with the registered administrator email. Never place real credentials in this README or source control.

### 6. Upload permissions

Ensure `storage/uploads/` is writable by the PHP process. Do not commit uploaded files. The directory is retained in Git through `.gitkeep`.

## Environment Configuration

The application uses these variables:

| Variable | Purpose |
| --- | --- |
| `CMS_DB_HOST` | MySQL/MariaDB host |
| `CMS_DB_PORT` | Database port |
| `CMS_DB_NAME` | Database name |
| `CMS_DB_USER` | Database username |
| `CMS_DB_PASSWORD` | Database password |

Credentials are never sent to frontend code.

## Database

Import [database/schema.sql](database/schema.sql). It creates:

- `users`, linked to `roles`
- `roles`, including seeded `user` and `admin` roles
- `complaint_reasons`, including priority and active state
- `complaints`, including stored priority, status, owner, and assignee
- `complaint_attachments`, linked to complaints
- `complaint_history`, linked to complaints and performer/assignee users

Complaint priority is stored as a snapshot on each complaint. Editing a reason affects future complaints only.

## User Flow

```text
Register
→ pending account
→ admin approval
→ login
→ dashboard
→ raise complaint
→ server derives priority
→ track complaint
→ view details/history
→ download authorized attachments
→ logout
```

## Admin Flow

```text
Admin login
→ admin dashboard
→ users
→ roles
→ complaint reasons
→ complaints
→ assignment
→ status changes
→ complaint history
```

All admin destinations verify authorization from the current database role, not from navigation visibility or submitted values.

## Testing

### Tests passed

- PHP syntax checks across all PHP files
- VS Code diagnostics on changed files
- Protected-route unauthenticated access checks
- Admin authorization boundary checks
- Static ownership/IDOR audits
- Static CSRF and state-changing route audits
- Static password-selection audit
- Static transaction/history audit
- Apache direct storage access denied with `403 Forbidden`
- Apache direct upload-file access denied with `403 Forbidden`
- Upload validation and path-safety code review

### Tests unavailable or not executed

- Full authenticated user journey integration tests
- Full authenticated admin journey integration tests
- Cross-user IDOR tests with two live sessions
- Live complaint creation, assignment, status, and history persistence tests
- Live database duplicate/foreign-key tests
- JavaScript syntax check where Node.js was unavailable

These tests require configured database credentials, live test accounts, and authenticated browser sessions.

## Project Status

The application is functionally complete for the implemented user and admin workflows. Repository preparation and final security hardening are complete. Authenticated end-to-end integration testing remains an environment-dependent follow-up.

## Future Improvements

The following are intentionally not implemented:

- Full admin user creation/editing and role-assignment workflows
- Pagination for large admin/user/complaint/history datasets
- Automated integration and security test suites
- Email notifications
- Password reset/change workflows
- Production deployment configuration and secret management
- A PHP built-in-server router for upload denial; Apache `.htaccess` protection is currently provided
