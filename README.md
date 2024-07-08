

# Project Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Project Structure](#project-structure)
3. [Docker Setup](#docker-setup)
4. [Getting Started](#getting-started)
5. [Running the Application](#running-the-application)
6. [Updating Daily Records](#updating-daily-records)
7. [Background Jobs](#background-jobs)
8. [Scheduling Tasks](#scheduling-tasks)

## Project Overview

This project is a Laravel application designed to manage and track user data. The application utilizes Docker for containerization, making it easy to set up and deploy in different environments. Key features include:

- User management
- Daily record updates with average ages and counts
- Background processing with Laravel queues and scheduling

## Project Structure

The project follows a typical Laravel structure. Key directories and files include:

```
├── app
│   ├── Console
│   │   └── Kernel.php
│   ├── Exceptions
│   ├── Http
│   ├── Jobs
│   │   ├── DailyRecordJob.php
│   │   └── FetchUsersJob.php
│   ├── Models
│   │   ├── User.php
│   │   └── DailyRecord.php
│   ├── Providers
│   └── ...
├── bootstrap
├── config
├── database
│   ├── factories
│   ├── migrations
│   ├── seeders
├── docker
│   ├── php
│   │   └── local.ini
├── public
├── resources
├── routes
├── storage
├── tests
├── Dockerfile
├── docker-compose.yml
└── ...
```

## Docker Setup

### Dockerfile

The `Dockerfile` defines the environment for the Laravel application. It includes:

- PHP 8.1 with Apache
- Necessary PHP extensions
- Node.js and npm
- Composer
- Apache configuration

### docker-compose.yml

The `docker-compose.yml` file sets up the application services, including:

- The Laravel application
- PostgreSQL database
- Redis for caching and queues

Key service definitions:

```yaml
version: '3'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    image: laravel-backend-test-app
    container_name: laravel_backend_test_app
    restart: unless-stopped
    tty: true
    environment:
      SERVICE_NAME: app
      SERVICE_TAGS: dev
    working_dir: /var/www/html/
    volumes:
      - .:/var/www/html:cached
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    ports:
      - "8000:80"
    networks:
      - daily_laravel
    command: >
      /bin/sh -c "php artisan migrate &&
                  php artisan db:seed &&
                  php artisan config:cache &&
                  php artisan route:cache &&
                  php artisan queue:work --sleep=3 --tries=3 &
                  php artisan schedule:work &
                  apache2-foreground"

  db:
    image: postgres:13
    container_name: laravel_backend_test_db
    restart: unless-stopped
    environment:
      POSTGRES_DB: laravel
      POSTGRES_USER: laravel
      POSTGRES_PASSWORD: secret
    volumes:
      - daily_pgdata:/var/lib/postgresql/data
    networks:
      - daily_laravel

  redis:
    image: redis:alpine
    container_name: laravel_backend_test_redis
    restart: unless-stopped
    networks:
      - daily_laravel

networks:
  daily_laravel:
    driver: bridge

volumes:
  daily_pgdata:
    driver: local
```

## Getting Started

### Prerequisites

- Docker installed on your machine
- Docker Compose installed

### Initial Setup

1. **Clone the repository:**
   ```sh
   git clone https://github.com/Abdulatifhaehsh/LaravelDailyRecords.git
   cd your-project
   ```

2. **Build and start the Docker containers:**
   ```sh
   docker-compose up --build -d
   ```

3. **Run initial setup commands:**
   ```sh
   docker-compose exec app php artisan key:generate
   ```

## Running the Application

### Accessing the Application

- Open your web browser and go to `http://localhost:8000`

### Background Processing

- Laravel queues and scheduled tasks are set to run automatically in the background when the containers start.

## Updating Daily Records

### User Model

The `User` model includes logic to update daily records whenever a user is deleted:

```php
protected static function booted()
{
    static::deleted(function ($user) {
        $date = $user->created_at->toDateString();
        DailyRecord::updateAveragesAndCounts($date);
    });
}
```

### DailyRecord Model

The `DailyRecord` model has a method to update averages and counts:

```php
public static function updateAveragesAndCounts($date)
{
    $maleData = User::where('gender', 'male')
        ->whereDate('created_at', $date)
        ->selectRaw('count(*) as count, avg(age) as avg_age')
        ->first();

    $femaleData = User::where('gender', 'female')
        ->whereDate('created_at', $date)
        ->selectRaw('count(*) as count, avg(age) as avg_age')
        ->first();

    self::updateOrCreate(
        ['date' => $date],
        [
            'male_count' => $maleData->count,
            'male_avg_age' => $maleData->avg_age ?: 0,
            'female_count' => $femaleData->count,
            'female_avg_age' => $femaleData->avg_age ?: 0,
        ]
    );
}
```

## Background Jobs

### FetchUsersJob

The `FetchUsersJob` fetches random users from an external API and updates or creates user records in the database. It also updates Redis with the user counts.

```php
public function handle(): void
{
    $response = Http::get('https://randomuser.me/api/?results=20');
    $users = $response->json()['results'];

    foreach ($users as $userData) {
        $user = User::updateOrCreate(
            ['uuid' => $userData['login']['uuid']],
            [
                'name' => $userData['name']['first'] . ' ' . $userData['name']['last'],
                'age' => $userData['dob']['age'],
                'gender' => $userData['gender'],
            ]
        );

        Log::info('User created', ['record' => $user]);

        Redis::incr('user_count');
        Redis::incr($user->gender . '_count');
    }
}

```

### DailyRecordJob

The `DailyRecordJob` calculates and stores the daily average age and count of users for each gender.

```php
public function handle(): void
{
    $maleCount = Redis::get('male_count');
    $femaleCount = Redis::get('female_count');

    $maleAvgAge = User::where('gender', 'male')->avg('age');
    $femaleAvgAge = User::where('gender', 'female')->avg('age');

    Log::info('DailyRecordJob executed', [
        'male_count' => $maleCount,
        'female_count' => $femaleCount,
        'male_avg_age' => $maleAvgAge,
        'female_avg_age' => $femaleAvgAge,
    ]);

    $record = DailyRecord::create([
        'date' => now()->toDateString(),
        'male_count' => $maleCount,
        'female_count' => $femaleCount,
        'male_avg_age' => $maleAvgAge,
        'female_avg_age' => $femaleAvgAge,
    ]);

    Log::info('DailyRecord created', ['record' => $record]);

    Redis::del(['male_count', 'female_count']);
}

```

## Scheduling Tasks

### Console Kernel

The `Kernel.php` file schedules the background jobs to run at specified intervals.

```php
class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new FetchUsersJob)->hourly();
        $schedule->job(new DailyRecordJob)->daily();
    }
  
}
```

## Stay in touch
- Author - [Abdulatif Hashash](https://www.linkedin.com/in/abdulatif-hashash-8aa594202/)
- Portfolio - [website](https://abdulatifhashash.site/)
