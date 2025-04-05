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
        $now = Carbon::now();
        $startDate = $now->copy()->subDays($this->faker->numberBetween(0, 60));
        $duration = $this->faker->randomElement([7, 14, 21, 28]);
        $endDate = $startDate->copy()->addDays($duration - 1);
        
        // Calculate progress based on current date's relationship to the sprint dates
        $daysElapsed = 0;
        $daysRemaining = 0;
        $progressPercentage = 0;
        $status = 'planned';
        
        if ($now->gte($startDate) && $now->lte($endDate)) {
            // Active sprint
            $status = 'active';
            $daysElapsed = $startDate->diffInDays($now) + 1;
            $daysRemaining = $now->diffInDays($endDate);
            $progressPercentage = min(100, round(($daysElapsed / $duration) * 100, 1));
        } elseif ($now->gt($endDate)) {
            // Completed sprint
            $status = 'completed';
            $daysElapsed = $duration;
            $daysRemaining = 0;
            $progressPercentage = 100;
        } else {
            // Planned sprint
            $daysElapsed = 0;
            $daysRemaining = $duration;
            $progressPercentage = 0;
        }
        
        return [
            'sprint_number' => $this->faker->unique()->numberBetween(1, 20),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration' => $duration,
            'status' => $status,
            'progress_percentage' => $progressPercentage,
            'days_elapsed' => $daysElapsed,
            'days_remaining' => $daysRemaining,
            'notes' => $this->faker->optional(0.7)->sentence(),
            'created_at' => $startDate->copy()->subDays(7),
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
            $duration = $attributes['duration'] ?? 14;
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
            $duration = $attributes['duration'] ?? 14;
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
            $duration = $attributes['duration'] ?? 14;
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