<?php

$config = [
    'example-userpass' => [
        'exampleauth:UserPass',
        'distant_future:a' => [
            'eduPersonPrincipalName' => ['DISTANT_FUTURE@ssp-hub-idp.local'],
            'eduPersonTargetID' => ['11111111-1111-1111-1111-111111111111'],
            'sn' => ['Future'],
            'givenName' => ['Distant'],
            'mail' => ['distant_future@example.com'],
            'employeeNumber' => ['11111'],
            'cn' => ['DISTANT_FUTURE'],
            'schacExpiryDate' => [
                gmdate('YmdHis\Z', strtotime('+6 months')), // Distant future
            ],
        ],
        'near_future:b' => [
            'eduPersonPrincipalName' => ['NEAR_FUTURE@ssp-hub-idp.local'],
            'eduPersonTargetID' => ['22222222-2222-2222-2222-222222222222'],
            'sn' => ['Future'],
            'givenName' => ['Near'],
            'mail' => ['near_future@example.com'],
            'employeeNumber' => ['22222'],
            'cn' => ['NEAR_FUTURE'],
            'schacExpiryDate' => [
                gmdate('YmdHis\Z', strtotime('+1 day')), // Very soon
            ],
        ],
        'already_past:c' => [
            'eduPersonPrincipalName' => ['ALREADY_PAST@ssp-hub-idp.local'],
            'eduPersonTargetID' => ['33333333-3333-3333-3333-333333333333'],
            'sn' => ['Past'],
            'givenName' => ['Already'],
            'mail' => ['already_past@example.com'],
            'employeeNumber' => ['33333'],
            'cn' => ['ALREADY_PAST'],
            'schacExpiryDate' => [
                gmdate('YmdHis\Z', strtotime('-1 day')), // In the past
            ],
        ],
        'missing_exp:d' => [
            'eduPersonPrincipalName' => ['MISSING_EXP@ssp-hub-idp.local'],
            'eduPersonTargetID' => ['44444444-4444-4444-4444-444444444444'],
            'sn' => ['Expiration'],
            'givenName' => ['Missing'],
            'mail' => ['missing_exp@example.com'],
            'employeeNumber' => ['44444'],
            'cn' => ['MISSING_EXP'],
        ],
        'invalid_exp:e' => [
            'eduPersonPrincipalName' => ['INVALID_EXP@ssp-hub-idp.local'],
            'eduPersonTargetID' => ['55555555-5555-5555-5555-555555555555'],
            'sn' => ['Expiration'],
            'givenName' => ['Invalid'],
            'mail' => ['invalid_exp@example.com'],
            'employeeNumber' => ['55555'],
            'cn' => ['INVALID_EXP'],
            'schacExpiryDate' => [
                'invalid'
            ],
        ],
    ],
];
