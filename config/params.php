<?php

$secrets = require __DIR__ . '/secrets.php';
$version = require __DIR__ . '/version.php';

return [
    /**
     * mailer 
     */
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',

    /**
     * Configurazione invio email
     */
    'mail_name' => $secrets['defaultTimeZone'],
    'mail_support' => $secrets['defaultTimeZone'],
    'mail_host' => $secrets['defaultTimeZone'], // SMTP ENDPOINT
    'mail_adminusername' => $secrets['defaultTimeZone'], // indirizzo mail nella firma
    'mail_username' => $secrets['defaultTimeZone'],
    'mail_password' => $secrets['defaultTimeZone'],
    'mail_port' => $secrets['defaultTimeZone'],
    'useFileTransport' => $secrets['defaultTimeZone'], // set false in production
    'encryption' => $secrets['defaultTimeZone'],

    /**
     * app settings
     */
    'bsVersion' => '5.x', // this will set globally `bsVersion` to Bootstrap 5.x for all Krajee Extensions
    'defaultTimeZone' => $secrets['defaultTimeZone'],
    'localTimeZone' => $secrets['localTimeZone'],

    'hostname' => $secrets['hostname'],
    'version' => $version['version'],

    'logoApplicazione' => '@web/bundles/site/images/logo.png',
    'icon-framework' => 'fa',  // Font Awesome Icon framework
    
    /**
     * Parametrizzazione società 
     */
    'webapp_society' => $secrets['webapp_society'],
    'webapp_link' => $secrets['webapp_link'],

    
    /**
     * Set the password reset token expiration time.
     */
    'user.passwordResetTokenExpire' => 24 * 60 * 60,
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

];
