# Deployment Guide for Render

This guide explains how to deploy the Quiz API application on Render using Docker.

## Prerequisites

- A Render account
- Git repository with your code

## Environment Variables

Set the following environment variables in your Render dashboard:

### Required Variables

```
APP_NAME=Quiz App
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_URL=https://your-app-name.onrender.com

LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
```

### Generating APP_KEY

You can generate an APP_KEY by running:
```bash
php artisan key:generate --show
```

Or Render will automatically generate one if you don't set it.

## Deployment Steps

### Option 1: Using Render Dashboard

1. **Create a New Web Service**
   - Go to your Render dashboard
   - Click "New +" → "Web Service"
   - Connect your Git repository

2. **Configure the Service**
   - **Name**: quiz-app (or your preferred name)
   - **Environment**: Docker
   - **Dockerfile Path**: `./Dockerfile`
   - **Docker Context**: `.`
   - **Plan**: Starter (or higher)

3. **Set Environment Variables**
   - Add all the environment variables listed above
   - Make sure `APP_KEY` is set (Render can auto-generate this)

4. **Deploy**
   - Click "Create Web Service"
   - Render will build and deploy your application

### Option 2: Using render.yaml

1. The `render.yaml` file is already configured
2. In Render dashboard, go to "New +" → "Blueprint"
3. Connect your repository
4. Render will automatically detect and use `render.yaml`

## Health Check

The application includes a health check endpoint at `/api/questions`. Render will use this to verify the service is running.

## Database Setup

The application uses SQLite by default. The database file will be created automatically at:
```
/var/www/html/database/database.sqlite
```

### Important Notes for SQLite on Render:

- **Ephemeral Storage**: Render's filesystem is ephemeral, meaning data can be lost on restarts
- **For Production**: Consider using Render's PostgreSQL service for persistent data
- **For Testing/Demo**: SQLite works fine but data may be lost on service restarts

### Using PostgreSQL (Recommended for Production)

1. Create a PostgreSQL database in Render
2. Update environment variables:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=your-db-host.onrender.com
   DB_PORT=5432
   DB_DATABASE=your_db_name
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_db_password
   ```

3. Update the Dockerfile to install PostgreSQL extension:
   ```dockerfile
   RUN apk add --no-cache postgresql-dev
   RUN docker-php-ext-install pdo pdo_pgsql
   ```

## Build Process

The Dockerfile uses a multi-stage build:

1. **Builder Stage**: Installs dependencies, builds assets
2. **Production Stage**: Creates lightweight production image

## Startup Process

On container start, the `start.sh` script:
1. Creates SQLite database if it doesn't exist
2. Sets proper permissions
3. Runs migrations
4. Seeds questions (if database is empty)
5. Caches configuration, routes, and views
6. Starts PHP-FPM and Nginx via Supervisor

## Port Configuration

Render uses the `PORT` environment variable. The Dockerfile is configured to:
- Listen on port 10000 by default
- Can be overridden by Render's PORT environment variable

## Troubleshooting

### Application Not Starting

1. Check logs in Render dashboard
2. Verify all environment variables are set
3. Ensure `APP_KEY` is generated
4. Check database connection settings

### Database Issues

1. Verify database file permissions
2. Check if migrations ran successfully
3. Review application logs for database errors

### Build Failures

1. Check Dockerfile syntax
2. Verify all dependencies in composer.json and package.json
3. Review build logs for specific errors

## Custom Domain

To use a custom domain:
1. Go to your service settings in Render
2. Click "Custom Domains"
3. Add your domain
4. Update `APP_URL` environment variable

## Monitoring

- View logs in Render dashboard
- Set up alerts for service health
- Monitor resource usage

## Scaling

Render allows you to scale your service:
- **Starter Plan**: 1 instance
- **Standard Plan**: Multiple instances with load balancing

## Backup Strategy

Since SQLite is ephemeral on Render:
1. Consider using PostgreSQL for production
2. Implement regular database exports
3. Store backups in external storage (S3, etc.)

## Support

For Render-specific issues, consult:
- [Render Documentation](https://render.com/docs)
- [Render Community](https://community.render.com)

