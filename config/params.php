<?php

$secrets = require __DIR__ . '/secrets.php';
$version = require __DIR__ . '/version.php';

return [
    /**
     * mailer 
     */
    'adminName' => $secrets['adminName'],
    'adminPhone' => $secrets['adminPhone'],
    'adminEmail' => $secrets['adminEmail'],
    'website' => $secrets['website'],

    /**
     * app settings
     */
    'bsVersion' => '5.x', // this will set globally `bsVersion` to Bootstrap 5.x for all Krajee Extensions
    'defaultTimeZone' => $secrets['defaultTimeZone'],
    'localTimeZone' => $secrets['localTimeZone'],

    'logoApplicazione' => '@web/bundles/site/images/logo.png',
    'icon-framework' => 'fa',  // Font Awesome Icon framework
    'version' => $version['version'],
    
    /**
     * Parametrizzazione società 
     */
    'webapp_society' => $secrets['webapp_society'],
    'webapp_link' => $secrets['webapp_link'],

    /**
     * Set the password reset token expiration time.
     */
    'user.passwordResetTokenExpire' => 60 * 60, // 1 ora
    'user.authTimeout' => 30 * 24 * 60 * 60, // 30 giorni
    'user.passwordMinLength' => 8,

    /**
     * Set the list of usernames that we do not want to allow to users to take upon registration or profile change.
     */
    'user.spamNames' => 'admin|superadmin|creator|thecreator|username|administrator|root',

    /**
     * Set the secret for encrypt/decrypt
     * generated from: https://randomkeygen.com/
     */
    'secret_hash_key' => $secrets['secret_hash_key'],


    /**
     * Set the nonce timeout for activate user
     * 
     * 48 ore per cliccare sul link di attivazione
     */
    'nonce.timeout' => 2 * 24 * 60 * 60,
];
