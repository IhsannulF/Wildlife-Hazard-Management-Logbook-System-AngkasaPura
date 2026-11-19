<?php

return [
    'enabled' => env('AI_ENABLED', true),
    'logbook_window_days' => (int) env('AI_LOGBOOK_WINDOW_DAYS', 30),
    'max_logbook_window_days' => 90,
    'max_manual_runs_per_day' => (int) env('AI_MAX_MANUAL_RUNS_PER_DAY', 10),
    'cooldown_minutes' => 10,
    'vision_min_confidence' => (float) env('AI_VISION_MIN_CONFIDENCE', 0.60),
    'max_payload_kb' => 60,
    'hazard_levels' => [
        'rendah' => 0.30,
        'sedang' => 0.60,
        'tinggi' => 0.85,
        // >= 0.85 is kritis
    ],
    'airport' => [
        'lat' => (float) env('AIRPORT_LAT', -7.3798),
        'lng' => (float) env('AIRPORT_LNG', 112.7875),
    ],
];
