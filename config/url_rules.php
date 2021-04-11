<?php
return [
    'regisztracio' => '/site/signup',
    'bejelentkezes' => '/site/login',
    '<id:\d+>' => '/site/observation',
    'elfelejtett-jelszo' => '/site/lost-password',
    'uj-jelszo/<token:.*>' => '/site/new-password',
    'celok' => '/site/about',

    'felhasznalok' => '/user/index',
    'profil/<id:\d+>' => '/user/profile',
    'profil/modositas' => '/user/update',
    'profil/jelszo-valtoztatas' => '/user/change-password',

    'osszes' => 'observe/index',
    'kereses' => 'observe/search',
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

    //meteor
    'meteor' => 'meteor/index',
    'meteor/<id:\d+>' => 'meteor/view',
    'meteor/feltoltes' => 'meteor/create',
    'meteor/modositas' => 'meteor/update',

    //asztrotajkep
    'asztrotajkep' => 'landscape/index',
    'asztrotajkep/<id:\d+>' => 'landscape/view',
    'asztrotajkep/feltoltes' => 'landscape/create',
    'asztrotajkep/modositas' => 'landscape/update',
    //comet
    'ustokos' => 'comet/index',
    'ustokos/<id:\d+>' => 'comet/view',
    'ustokos/feltoltes' => 'comet/create',
    'ustokos/modositas' => 'comet/update',
];