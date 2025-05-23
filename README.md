# Ekklessia Church Management System

A comprehensive church management system built with PHP that helps manage members, events, roles, and church activities at different organizational levels.

## Features

- **Multi-level Organization Management**
  - National level (EXCO)
  - Zone level
  - Assembly level
  - Household level

- **Member Management**
  - Member registration and profiles
  - Role assignment
  - Permission management
  - Household assignment

- **Event Management**
  - Event scheduling
  - Recurring events
  - Calendar view
  - Event types customization
  - Multi-level events (National, Zone, Assembly, Household)

- **Role-Based Access Control**
  - Hierarchical roles
  - Granular permissions
  - Scope-based access

- **Additional Features**
  - Financial management
  - Specialized ministries
  - Reporting system
  - Dark mode support
  - Mobile responsive design

## Technology Stack

- PHP
- MySQL/MariaDB
- Bootstrap 5
- JavaScript/jQuery
- FullCalendar.js
- PDO for database operations

## Installation

1. Clone this repository
2. Set up a web server (e.g., XAMPP, WAMP)
3. Import the database schema from `updates/` directory
4. Configure database connection in `app/config/db.php`
5. Configure application settings in `app/config/config.php`
6. Access the application through your web server

## Configuration

1. Copy `app/config/db.php.example` to `app/config/db.php`
2. Update database credentials in `db.php`
3. Copy `app/config/config.php.example` to `app/config/config.php`
4. Update application settings in `config.php`

## Project Structure

```
app/
├── config/         # Configuration files
├── functions/      # Business logic and utilities
├── includes/       # Reusable components
├── pages/         # Application pages
└── uploads/       # File uploads
Public/
├── assets/        # CSS, JS, images
└── index.php      # Entry point
```

## Security

- Session-based authentication
- Role-based authorization
- PDO prepared statements
- Input validation and sanitization
- XSS protection
- CSRF protection

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
