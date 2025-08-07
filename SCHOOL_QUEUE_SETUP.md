# Laravel Queue Implementation for School Creation

This document explains the implementation of Laravel Queue system for handling school creation operations in the background, improving user experience and system performance.

## Overview

The school creation process involves several time-consuming operations:
- Creating a new database for each school
- Running database migrations
- Setting up default roles and permissions
- Inserting initial data and settings
- Sending welcome emails

Previously, these operations were performed synchronously, taking 2-3 minutes and blocking the user interface. With the queue implementation, these operations are now handled in the background, providing immediate feedback to users.

## Architecture

### Job Classes

**SetupSchoolDatabase** (`app/Jobs/SetupSchoolDatabase.php`)
   - Handles database creation, migrations, and initial setup
   - Updates school status on completion/failure
   - Handles welcome email sending

## Setup Instructions

### 1. Database Configuration

Run the migration to add the status column to schools table:

```bash
php artisan migrate
```

### 2. Queue Driver Configuration

Update your `.env` file to use a queue driver other than 'sync':

```env
QUEUE_CONNECTION=database
# or
QUEUE_CONNECTION=redis
```

### 3. Create Queue Tables (if using database driver)

```bash
php artisan queue:table
php artisan migrate
```

### 4. Start Queue Workers

Start queue workers to process the jobs:

```bash
# Process all queues
php artisan queue:work

# Process with supervisor (recommended for production)
# See Laravel documentation for supervisor configuration
```

## School Installed Status

- `0`: Pending/Setup in progress
- `1`: setup completed successfully

## Production Deployment

### Supervisor Configuration

Create a supervisor configuration file for queue workers:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```


## Performance Benefits

1. **Improved User Experience**: Immediate response to school creation requests
2. **Better System Performance**: Non-blocking operations
3. **Scalability**: Can handle multiple school creation requests simultaneously
4. **Reliability**: Automatic retry mechanism for failed operations

## Troubleshooting

### Common Issues

1. **Jobs not processing**
   - Check if queue workers are running
   - Verify queue driver configuration
   - Check for failed jobs in `failed_jobs` table

2. **School setup failures**
   - Check logs for specific error messages
   - Verify database permissions
   - Use retry commands to re-process failed schools

3. **Email delivery issues**
   - Check email configuration
   - Verify SMTP settings
   - Check email logs

## Security Considerations

1. **Database Permissions**: Ensure queue workers have proper database permissions
2. **File Permissions**: Verify storage and log directory permissions
3. **Environment Variables**: Secure sensitive configuration in production