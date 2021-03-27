<?php
return [
    'regisztracio' => '/site/signup',
    'bejelentkezes' => '/site/login',

    'felhasznalok' => '/user/index',
    'profil' => '/user/profile',
    'profil/modositas' => '/user/update',
    'profil/jelszo-valtoztatas' => '/user/change-password',

    'osszes' => 'observe/index',
    //melyeg
    'melyeg' => 'deepsky/index',
    'melyeg/<id:\d+>' => 'deepsky/view',
    'melyeg/feltoltes' => 'deepsky/create',
    'melyeg/modositas' => 'deepsky/update',
    //bolygo
    'bolygo' => 'planet/index',
    'bolygo/<id:\d+>' => 'planet/view',
    'bolygo/feltoltes' => 'planet/create',
    'bolygo/modositas' => 'planet/update',
    //hold
    'hold' => 'moon/index',
    'hold/<id:\d+>' => 'moon/view',
    'hold/feltoltes' => 'moon/create',
    'hold/modositas' => 'moon/update',
];