# Custom Sprint Data Seeding

This document explains how to use the custom sprint data seeding functionality with predefined teams and team members.

## Custom Seeder Features

The custom seeder creates:

1. **Weekly Sprints**:
   - Generates sprints starting from January 1st
   - Each sprint is exactly 1 week long (7 days)
   - Creates sprints up to the week before April 4th, 2023
   - All sprints are marked as completed (100% progress)
   - No planned or active sprints are created

2. **2 Teams**:
   - Team Alpha
   - Team Beta

3. **2 Reports per Team per Sprint**:
   - Latest version and a previous version for each team
   - Each report includes team member data with story points
   - Bug data is only included in reports for the last sprint
   - Backlog of bugs from previous sprints is included after Sprint 1

4. **Predefined Team Members**:
   - XBananAX
   - Narathip Saentaweesuk
   - Patyot Sompran
   - Pawarit Sinchoom
   - Natchaya Chokchaichumnankij
   - Niyada Butcahn
   - Sutaphat Thahin
   - Thanakorn Prasertdeengam

## Running the Custom Seeder

### Using the Artisan Command

The easiest way to seed the custom sprint data is to use the provided Artisan command:

```bash
# To add sprint data to your existing database
php artisan sprints:seed-custom

# To refresh your database and add sprint data
php artisan sprints:seed-custom --fresh
```

The `--fresh` option will run `migrate:fresh` before seeding, which will drop all tables and recreate them.

### Using the Seeder Directly

You can also run the seeder directly:

```bash
# Run just the custom sprint seeder
php artisan db:seed --class=CustomSprintSeeder
```

## Data Structure

### Sprint Timeline

The sprints follow this structure:
- **Sprint 1**: Jan 1 - Jan 7
- **Sprint 2**: Jan 8 - Jan 14
- **Sprint 3**: Jan 15 - Jan 21
- And so on, up to the week before April 4th

### Each Report Contains:

1. **Team Members**:
   - Each report includes 3-5 randomly selected members from the predefined list
   - Each member has assigned story points, completed points, and completion percentage

2. **Story Points Summary**:
   - Total points assigned across all team members
   - Total points completed
   - Percentage completion

3. **Bug Data** (only in the last sprint):
   - Random number of bugs (0-8)
   - Each bug has a title, points, and priority
   - Bugs may be assigned to team members

4. **Backlog** (in all sprints after Sprint 1):
   - Accumulated bugs from previous sprints
   - Each bug has a title, points, priority, and origin sprint
   - Bugs may be assigned to team members
   - Displayed in a separate section below the current sprint's bugs

## Customizing the Data

If you need to modify the data, you can:

1. Edit the `$teamMembers` array in `CustomSprintSeeder` to change team members
2. Edit the `$teams` array to change the team names
3. Modify the reference date in the seeder to adjust the sprint range
4. Adjust the sprint duration (currently set to 1 week = 7 days)

## Notes

- All dates use the Asia/Bangkok (GMT+7) timezone and are displayed in 24-hour format
- The latest report for each team is marked as auto-generated, while the older version is marked as manual
- Report dates are evenly distributed throughout each sprint period
- All data is consistent with the specified team members