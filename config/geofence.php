<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Geofence Enabled
    |--------------------------------------------------------------------------
    | When enabled, employees must be within the allowed radius of an office
    | location to clock in/out. WFH employees bypass this check.
    */
    'enabled' => env('GEOFENCE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Office Locations
    |--------------------------------------------------------------------------
    | Default office locations. These can be overridden via the database
    | (office_locations table) through the admin panel.
    */
    'offices' => [
        [
            'name' => env('OFFICE_NAME', 'Main Office'),
            'lat' => env('OFFICE_LAT', 3.1390),
            'lng' => env('OFFICE_LNG', 101.6869),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Radius (meters)
    |--------------------------------------------------------------------------
    | Maximum distance from office for a valid clock in/out.
    */
    'radius' => env('GEOFENCE_RADIUS', 200),

    /*
    |--------------------------------------------------------------------------
    | GPS Accuracy Threshold (meters)
    |--------------------------------------------------------------------------
    | Reject location data with accuracy worse than this value.
    | Lower accuracy values = more precise GPS reading.
    */
    'max_accuracy' => env('GEOFENCE_MAX_ACCURACY', 100),

    /*
    |--------------------------------------------------------------------------
    | Anti-Fake GPS Settings
    |--------------------------------------------------------------------------
    */
    'anti_fake' => [
        // Reject if browser reports mock/simulated location
        'reject_mock' => env('GEOFENCE_REJECT_MOCK', true),

        // Maximum speed between consecutive clock events (km/h)
        // If an employee's location jumps impossibly fast, flag it
        'max_speed_kmh' => env('GEOFENCE_MAX_SPEED', 200),

        // Minimum accuracy required (meters) - very high accuracy on
        // mobile often indicates fake GPS apps
        'suspicious_accuracy_threshold' => env('GEOFENCE_SUSPICIOUS_ACCURACY', 1),
    ],
];
