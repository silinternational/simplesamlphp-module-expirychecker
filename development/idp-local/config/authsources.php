<?php

$config = [
    'example-userpass' => [
        'exampleauth:UserPass',
        'normal:a' => [
            'eduPersonPrincipalName' => ['NORMAL-hub-idp.local'],
            'eduPersonTargetID' => ['11111111-1111-1111-1111-111111111111'],
            'sn' => ['User'],
            'givenName' => ['Normal'],
            'mail' => ['normal@example.com'],
            'employeeNumber' => ['11111'],
            'cn' => ['NORMAL'],
            'schacExpiryDate' => [
                gmdate('YmdHis\Z', strtotime('+6 months')), // Distant future
            ],
        ],
        'warn:b' => [
            'eduPersonPrincipalName' => ['WARN@ssp-hub-idp.local'],
            'eduPersonTargetID' => ['22222222-2222-2222-2222-222222222222'],
            'sn' => ['User'],
            'givenName' => ['Warn'],
            'mail' => ['warn@example.com'],
            'employeeNumber' => ['22222'],
            'cn' => ['WARN'],
            'schacExpiryDate' => [
                gmdate('YmdHis\Z', strtotime('+1 day')), // Very soon
            ],
        ],
        'expired:c' => [
            'eduPersonPrincipalName' => ['EXPIRED@ssp-hub-idp.local'],
            'eduPersonTargetID' => ['33333333-3333-3333-3333-333333333333'],
            'sn' => ['User'],
            'givenName' => ['Expired'],
            'mail' => ['expired@example.com'],
            'employeeNumber' => ['33333'],
            'cn' => ['EXPIRED'],
            'schacExpiryDate' => [
                gmdate('YmdHis\Z', strtotime('-1 day')), // In the past
            ],
        ],
    ],
];
