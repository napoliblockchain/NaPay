# CAMBIARE il nome del dominio nel file apache.conf
```
	ServerName backoffice.dominio.com
```


# Configurare il .env

```bash
MYSQL_HOST=hostname
MYSQL_DBNAME=database_name
MYSQL_USER=database_user
MYSQL_PASSWORD=database_password
```

# configurare il file config/secrets.php

```php
<?php
    return [
      'defaultTimeZone' => 'UTC',
      'localTimeZone' => 'Europe/Rome',
      'hostname' => 'hostname', /// --> CONFIGURARE L'HOSTNAME
      
      // cookie secret
      'cookieValidationKey' => 'stringa_molto_complessa',
      
      
      // database
      'db_dsn' => 'mysql:host=hostname;dbname=database_name',
      'db_username' => 'database_user',
      'db_password' => 'database_password',
      
      /**
       * Set the secret for encrypt/decrypt
       */
      'secret_hash_key' => 'altra_stringa_molto_complessa',
      
      /*
       * Set console base url
       */
      'baseUrl' => 'hostname', // non mettere slash alla fine
      
      /**
       * Parametrizzazione società
       */
      'webapp_society' => 'nome società',
      'webapp_link' => 'https://urladdress.com',
    ];

```
