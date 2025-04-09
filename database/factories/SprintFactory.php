<?php

namespace Database\Factories;

use App\Models\Sprint;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SprintFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sprint::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentYear = Carbon::now()->year;
        $startOfYear = Carbon::create($currentYear, 1, 1)->startOfWeek();

        // Generate a random sprint with 7-day duration
        $randomSprintNumber = $this->faker->numberBetween(1, 52); // Max 52 weeks in a year

        // Calculate start date (Monday) and end date (Sunday)
        $startDate = $startOfYear->copy()->addWeeks($randomSprintNumber - 1);
        $endDate = $startDate->copy()->addDays(6); // Monday + 6 days = Sunday
        $duration = 7; // 7 days per sprint

        // Calculate progress based on current date's relationship to the sprint dates
        $now = Carbon::now();
        $daysElapsed = 0;
        $daysRemaining = 0;
        $progressPercentage = 0;
        $status = 'completed'; // Default status

        if ($now->gte($startDate) && $now->lte($endDate)) {
            $status = 'active';
            $daysElapsed = $startDate->diffInDays($now) + 1;
            $daysRemaining = $now->diffInDays($endDate);
            $progressPercentage = min(100, round(($daysElapsed / $duration) * 100, 1));
        } else {
            $daysElapsed = $duration;
            $daysRemaining = 0;
            $progressPercentage = 100;
        }

        // Format sprint number with year (e.g., 2024/01)
        $formattedSprintNumber = sprintf('%d/%02d', $currentYear, $randomSprintNumber);

        return [
            'sprint_number' => $randomSprintNumber,
            'sprint_year' => $currentYear,
            'sprint_year_number' => $formattedSprintNumber,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration' => $duration,
            'status' => $status,
            'progress_percentage' => $progressPercentage,
            'days_elapsed' => $daysElapsed,
            'days_remaining' => $daysRemaining,
            'notes' => "Sprint {$formattedSprintNumber} (Week of {$startDate->format('Y-m-d')})",
            'created_at' => $startDate->copy()->subDays(1),
            'updated_at' => $now,
        ];
    }

    /**
     * Configure the factory to generate a planned sprint.
     *
     * @return $this
     */
    public function planned()
    {
        return $this->state(function (array $attributes) {
            $startDate = Carbon::now()->addDays(rand(1, 14));
            $duration = 7; // Fixed 7-day duration
            $endDate = $startDate->copy()->addDays($duration - 1);

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'planned',
                'progress_percentage' => 0,
                'days_elapsed' => 0,
                'days_remaining' => $duration,
            ];
        });
    }

    /**
     * Configure the factory to generate an active sprint.
     *
     * @return $this
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            $now = Carbon::now();
            $duration = 7; // Fixed 7-day duration
            $daysElapsed = rand(1, $duration - 1);
            $startDate = $now->copy()->subDays($daysElapsed);
            $endDate = $startDate->copy()->addDays($duration - 1);
            $daysRemaining = max(0, $endDate->diffInDays($now));
            $progressPercentage = min(100, round(($daysElapsed / $duration) * 100, 1));

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'progress_percentage' => $progressPercentage,
                'days_elapsed' => $daysElapsed,
                'days_remaining' => $daysRemaining,
            ];
        });
    }

    /**
     * Configure the factory to generate a completed sprint.
     *
     * @return $this
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            $endDate = Carbon::now()->subDays(rand(1, 30));
            $duration = 7; // Fixed 7-day duration
            $startDate = $endDate->copy()->subDays($duration - 1);

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'completed',
                'progress_percentage' => 100,
                'days_elapsed' => $duration,
                'days_remaining' => 0,
            ];
        });
    }

    /**
     * Configure the factory to generate sequential sprint numbers.
     *
     * @param int $startNumber
     * @return $this
     */
    public function sequential(int $startNumber = 1)
    {
        return $this->sequence(
            fn ($sequence) => ['sprint_number' => $startNumber + $sequence->index]
        );
    }
}
