<?php

return [
    'adminEmail' => 'tamaskelemen.kt@gmail.com',
    'senderEmail' => 'info@eszleles.hu',
    'senderName' => 'Észlelés.hu csapata',
    'bsVersion' => '4.x',
    'user.passwordResetTokenExpire' => 3600,
    'homeUrl' => 'https://eszleles.hu',
    // built but hidden parts of the site, see components/Features.php
    'features' => [
        // "Észlelési tervek": hidden for now, the tables and the code stay, see README-eszlelesi-tervek.md
        'observingPlans' => false,
    ],

];
