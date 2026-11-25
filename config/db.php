<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=eszlelesdb;dbname=eszlelesdb',
    'username' => 'keltom',
    'password' => 'asdasd',
    'charset' => 'utf8',
    // NOW() has to be in the same time zone on every server, see defaultTimeZone of the formatter
    'on afterOpen' => function ($event) {
        $event->sender->createCommand("SET TIME ZONE 'Europe/Budapest'")->execute();
    },

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
