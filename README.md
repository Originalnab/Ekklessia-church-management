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

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.4+
- Web server (Apache/Nginx)
- Composer (for future dependencies)
- Git

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Originalnab/Ekklessia-church-management.git
   cd Ekklessia-church-management
   ```

2. Environment Setup:
   - Copy `.env.example` to `.env`
   - Update the environment variables in `.env` with your configuration
   - Set appropriate permissions for storage directories:
     ```bash
     chmod -R 775 app/uploads
     chmod -R 775 app/debug.log
     ```

3. Database Setup:
   - Create a new MySQL database
   - Import the initial schema:
     ```bash
     mysql -u your_username -p your_database < updates/initial_schema.sql
     ```
   - Run all update scripts in the `updates/` directory in sequential order

4. Web Server Configuration:
   - Point your web server's document root to the `Public` directory
   - Ensure `mod_rewrite` is enabled if using Apache
   - Configure virtual host (optional)

5. Application Setup:
   - Create an administrator account using the provided setup script
   - Configure your SMTP settings in `.env` for email functionality
   - Set up proper file permissions

6. Security Considerations:
   - Ensure `.env` file is not publicly accessible
   - Set proper file permissions
   - Configure SSL/TLS for production use
   - Regular backup of database and uploads

## Configuration

The application uses environment-based configuration. Key configuration files:

1. Environment Configuration (`.env`):
   - Database credentials
   - Application URL and environment
   - Debug mode
   - API keys and external services
   - Session and security settings

2. Application Configuration:
   - Main config: `app/config/config.php`
   - Database: `app/config/db.php`
   - Gemini AI integration: `app/config/gemini_config.php`

## Directory Structure

```
app/
├── config/         # Configuration files
├── functions/      # Business logic and utilities
├── includes/       # Reusable components and templates
├── pages/         # Application modules and views
│   ├── auth/      # Authentication
│   ├── dashboard/ # Dashboard views
│   ├── events/    # Event management
│   └── ...        # Other modules
├── resources/     # Application resources
└── uploads/       # User uploads
Public/
├── assets/        # Public assets (CSS, JS, images)
└── index.php      # Application entry point
updates/           # Database migration scripts
```

## Security

- Session-based authentication
- Role-based authorization
- PDO prepared statements
- Input validation and sanitization
- XSS protection
- CSRF protection

## Troubleshooting

Common issues and solutions:

1. Permission Issues:
   - Ensure proper file permissions on upload directories
   - Check web server user permissions

2. Database Connection:
   - Verify database credentials in `.env`
   - Check database server status
   - Ensure proper privileges for database user

3. Missing Configuration:
   - Verify all required `.env` variables are set
   - Check for proper file paths in configuration

## Support

For support:
1. Check the [Issues](https://github.com/Originalnab/Ekklessia-church-management/issues) section
2. Search existing documentation
3. Create a new issue with detailed information about your problem

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
