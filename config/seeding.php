<?php

/*
|--------------------------------------------------------------------------
| Database seeding
|--------------------------------------------------------------------------
|
| Controls the dataset produced by `php artisan db:seed`.
| See docs/database-seeding.md for the full explanation.
|
|   SEED_PROFILE=small|performance   which dataset size to build
|   SEED_RANDOM_SEED=<int>           same seed => same dataset
|   SEED_REFERENCE_DATE=YYYY-MM-DD   the "today" the data is generated around
|
| Only the profile decides the number of *parent* rows (patients, doctors,
| medicines...). Child tables (appointments, prescriptions, bills...) grow out
| of the relationships and distributions below, so their totals are emergent.
|
*/

return [

    'profile' => env('SEED_PROFILE', 'small'),

    'random_seed' => (int) env('SEED_RANDOM_SEED', 20260925),

    // Fixed instead of now() so the same seed produces the same data on any day.
    'reference_date' => env('SEED_REFERENCE_DATE', '2026-09-25'),

    // Rows per INSERT statement (capped automatically below PostgreSQL's 65,535 bind-parameter limit).
    'insert_chunk_size' => (int) env('SEED_CHUNK_SIZE', 1000),

    'profiles' => [
        'small' => [
            'departments' => 8,
            'doctors' => 10,
            'patients' => 100,
            'patient_account_ratio' => 0.90,
            'staff_users' => 5,
            'admin_users' => 1,
            'medicines' => 100,
            'rooms' => 20,
        ],
        'performance' => [
            'departments' => 18,
            'doctors' => 85,
            'patients' => 20000,
            'patient_account_ratio' => 0.95,
            'staff_users' => 1200,
            'admin_users' => 5,
            'medicines' => 15000,
            'rooms' => 400,
        ],
    ],

    // Share of clinical activity per year (appointments, records, admissions, bills, payments).
    'year_weights' => [2022 => 10, 2023 => 15, 2024 => 20, 2025 => 25, 2026 => 30],

    // When patients registered. Skewed early so most patients have history to accumulate.
    'registration_year_weights' => [2020 => 30, 2021 => 22, 2022 => 17, 2023 => 12, 2024 => 9, 2025 => 6, 2026 => 4],

    // How many appointments a patient books over their lifetime (the main source of skew).
    'patient_activity' => [
        'none' => ['weight' => 10, 'appointments' => [0, 0]],
        'low' => ['weight' => 45, 'appointments' => [1, 3]],
        'regular' => ['weight' => 30, 'appointments' => [4, 11]],
        'frequent' => ['weight' => 12, 'appointments' => [11, 28]],
        'chronic' => ['weight' => 3, 'appointments' => [26, 80]],
    ],

    // Chance that an appointment is with the patient's usual doctor rather than any doctor.
    'primary_doctor_share' => 0.65,

    // Zipf exponents: higher = more skewed (a few doctors / medicines dominate).
    'doctor_popularity_skew' => 0.8,
    'medicine_popularity_skew' => 1.07,

    // Uses the API's status vocabulary (AppointmentController validation).
    // Stale 'scheduled' rows in the past are intentional: nobody updated them.
    'appointment_status' => [
        'past' => ['completed' => 66, 'cancelled' => 11, 'missed' => 6, 'scheduled' => 17],
        'future' => ['scheduled' => 90, 'cancelled' => 10],
    ],

    // Old bills are mostly settled; recent bills are more often still open.
    'bill_status' => [
        'older_than_a_year' => ['paid' => 80, 'partially_paid' => 6, 'pending' => 4, 'cancelled' => 10],
        'recent' => ['paid' => 45, 'partially_paid' => 15, 'pending' => 30, 'cancelled' => 10],
    ],

    'payment_methods' => ['cash' => 45, 'bkash' => 25, 'card' => 18, 'nagad' => 7, 'bank_transfer' => 3, 'insurance' => 2],

];
