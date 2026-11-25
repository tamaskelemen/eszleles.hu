<?php

$keys = file_exists(__DIR__ . '/social.php') ? require __DIR__ . '/social.php' : [];

$clients = [
    'google' => [
        'class' => 'yii\authclient\clients\Google',
        'normalizeUserAttributeMap' => [
            'email' => function ($attributes) {
                return empty($attributes['verified_email']) ? null : ($attributes['email'] ?? null);
            },
        ],
        'viewOptions' => [
            'label' => 'Folytatás Google-fiókkal',
            'icon' => 'google',
        ],
    ],
];

foreach ($clients as $id => $client) {
    if (empty($keys[$id]['clientId'])) {
        unset($clients[$id]);
    } else {
        $clients[$id] = array_merge($client, $keys[$id]);
    }
}

return $clients;
