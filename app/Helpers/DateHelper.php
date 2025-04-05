<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class DateHelper
{
    /**
     * Format a date with the standard 24-hour time format
     *
     * @param Carbon|string $date Date to format
     * @param bool $includeTime Whether to include time
     * @return string Formatted date
     */
    public static function formatDate($date, $includeTime = false)
    {
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }
        
        return $includeTime 
            ? $date->format(config('timezone.format.datetime', 'd M Y H:i')) 
            : $date->format(config('timezone.format.date', 'd M Y'));
    }
    
    /**
     * Format a date for sprint display
     *
     * @param Carbon|string $date Date to format
     * @return string Formatted date (e.g., "Mar 15, 2023")
     */
    public static function formatSprintDate($date)
    {
        if (!$date) {
            return 'Unknown Date';
        }
        
        // If it's a string, convert to Carbon
        if (is_string($date)) {
            try {
                $date = Carbon::parse($date);
            } catch (\Exception $e) {
                return 'Invalid Date';
            }
        }
        
        // Format date as "Month Day, Year" (e.g., "January 1, 2025")
        return $date->format('F j, Y');
    }
    
    /**
     * Format a short date
     *
     * @param Carbon|string $date Date to format
     * @return string Formatted date (e.g., "Mar 15")
     */
    public static function formatShortDate($date)
    {
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }
        
        return $date->format('M j');
    }
    
    /**
     * Format a datetime with 24-hour time
     *
     * @param Carbon|string $date Date to format
     * @return string Formatted date and time
     */
    public static function formatDateTime($date)
    {
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }
        
        return $date->format(config('timezone.format.datetime', 'd M Y H:i'));
    }
    
    /**
     * Format time only in 24-hour format
     *
     * @param Carbon|string $date Date to format
     * @return string Formatted time
     */
    public static function formatTime($date)
    {
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }
        
        return $date->format(config('timezone.format.time', 'H:i'));
    }
} 