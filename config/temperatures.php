<?php

return [
    'api_uri' => env('TEMPERATURES_API_URI', 'https://api.open-meteo.com/v1/forecast'),
    'delivery_item_temperature_hours' => (int) env('DELIVERY_ITEM_TEMPERATURE_HOURS', 24),
    'alert_range' => (float) env('DELIVERY_ITEM_TEMPERATURE_ALERT_RANGE', 5),
    'alert_email' => env('DELIVERY_ITEM_TEMPERATURE_ALERT_EMAIL'),
    'use_seeder' => env('TEMPERATURES_USE_SEEDER', false),
];
