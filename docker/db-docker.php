<?php
return [
  'class' => 'yii\db\Connection',
  'dsn' => 'mysql:host='.$_ENV['MYSQL_HOST'].';dbname='.$_ENV['MYSQL_DBNAME'],
  'username' => $_ENV['MYSQL_USER'],
  'password' => $_ENV['MYSQL_PASSWORD'],
  'charset' => 'utf8',
];
