<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Application Timezone
    |--------------------------------------------------------------------------
    |
    | This value is the default timezone for the application. This is set to
    | Asia/Bangkok (GMT+7) by default. All dates will be displayed in this 
    | timezone and formatted using 24-hour time format.
    |
    */
    
    'default' => 'Asia/Bangkok', // GMT+7
    
    /*
    |--------------------------------------------------------------------------
    | Time Format
    |--------------------------------------------------------------------------
    |
    | This value is the default time format for the application. 
    | Use 'H:i' for 24-hour format (e.g., 15:30)
    | 
    */
    
    'format' => [
        'time' => 'H:i',
        'date' => 'd M Y',
        'datetime' => 'd M Y H:i',
        'sprint_date' => 'M j, Y',
    ],
]; 