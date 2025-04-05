# Sprint Data Seeding

This document explains how to use the sprint data seeding functionality to quickly populate your database with sample sprint data for testing and development.

## Available Seeders

The application includes the following seeders for generating sprint data:

1. `SprintSeeder` - Creates 5 sprints with multiple reports for each sprint
   - 2 completed sprints (Sprint #1 and #2)
   - 1 active sprint (Sprint #3)
   - 2 planned sprints (Sprint #4 and #5)

## Seeding Data

### Using the Artisan Command

The easiest way to seed sprint data is to use the provided Artisan command:

```bash
# To add sprint data to your existing database
php artisan sprints:seed

# To refresh your database and add sprint data
php artisan sprints:seed --fresh
```

The `--fresh` option will run `migrate:fresh` before seeding, which will drop all tables and recreate them.

### Using the Database Seeder

You can also use the standard Laravel database seeder:

```bash
# Seed all data (including sprints)
php artisan db:seed

# Seed only sprint data
php artisan db:seed --class=SprintSeeder
```

## What Gets Created

When you run the seeder, the following will be created:

1. **5 Sprints**:
   - Sprints #1-2: Completed sprints (in the past)
   - Sprint #3: Active sprint (currently in progress)
   - Sprints #4-5: Planned sprints (in the future)

2. **Multiple Reports per Sprint**:
   - Each sprint will have 2-3 teams with reports
   - Each team will have 1-3 reports (to simulate versioning)
   - Reports include realistic data for story points and bugs

## Factory Classes

If you need more custom data, you can use the factory classes directly:

```php
// Create a single sprint
$sprint = \App\Models\Sprint::factory()->create();

// Create a completed sprint
$completedSprint = \App\Models\Sprint::factory()->completed()->create();

// Create an active sprint
$activeSprint = \App\Models\Sprint::factory()->active()->create();

// Create a planned sprint
$plannedSprint = \App\Models\Sprint::factory()->planned()->create();

// Create a sprint report
$report = \App\Models\SprintReport::factory()->create(['sprint_id' => $sprint->id]);

// Create a sequence of sprints with sequential numbers
$sprints = \App\Models\Sprint::factory()
    ->count(5)
    ->sequential()
    ->create();
```

## Notes

- All dates in reports and sprints use the Asia/Bangkok (GMT+7) timezone and are displayed in 24-hour format
- The sprint report data contains realistic values for story points and bug metrics
- Teams are randomly selected from: Frontend Team, Backend Team, Design Team, DevOps Team, QA Team
- Each team might have multiple reports per sprint, with the most recent one marked as "(Latest)" 