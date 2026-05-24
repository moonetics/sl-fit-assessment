<?php

return [
    'community_weights' => [
        'Online Behavior' => 12,
        'Toxicity Control' => 14,
        'Sportsmanship' => 10,
        'Respect for Casual Members' => 10,
        'Conflict Handling' => 12,
        'Rule Acceptance' => 12,
        'Accountability' => 10,
        'Drama Risk' => 12,
        'Community Commitment' => 8,
    ],

    'competitive_weights' => [
        'Competitive Attitude' => 45,
        'Sportsmanship' => 25,
        'Accountability' => 10,
        'Rule Acceptance' => 10,
        'Respect for Casual Members' => 10,
    ],

    'thresholds' => [
        'min_duration_minutes' => 8,
        'high_speed_minutes' => 4,
        'straight_lining_medium' => 0.80,
        'straight_lining_high' => 0.90,
        'perfection_medium' => 0.90,
        'perfection_high' => 0.96,
        'refresh_count' => 25,
        'resume_count' => 8,
        'device_count' => 2,
        'min_answer_seconds' => 2,
        'fast_answer_count' => 12,
        'visibility_change_count' => 20,
        'offline_sync_count' => 5,
    ],

    'risk_levels' => [
        'Very Low',
        'Low',
        'Medium',
        'High',
        'Critical',
    ],

    'final_statuses' => [
        'Accepted',
        'Accepted as Casual Member',
        'Accepted with Trial',
        'Manual Review',
        'Watchlist',
        'Retest',
        'Rejected',
    ],
];
