<?php
return array (
  'backend' =>
  array (
    'frontName' => 'admin',
  ),
  'crypt' =>
  array (
    'key' => '6f89ade9569ea1ef34bf68a0adcee8f4',
  ),
  'session' =>
  array (
    'save' => 'files',
  ),
  'db' =>
  array (
    'table_prefix' => '',
    'connection' =>
    array (
      'default' =>
      array (
        'host' => getenv('MAGENTO_DB_HOST'),
        'dbname' => getenv('MAGENTO_DB_NAME'),
        'username' => getenv('MAGENTO_DB_USERNAME'),
        'password' => getenv('MAGENTO_DB_PASSWORD'),
        'model' => 'mysql4',
        'engine' => 'innodb',
        'initStatements' => 'SET NAMES utf8;',
        'active' => '1',
      ),
    ),
  ),
  'resource' =>
  array (
    'default_setup' =>
    array (
      'connection' => 'default',
    ),
  ),
  'x-frame-options' => 'SAMEORIGIN',
  'MAGE_MODE' => 'default',
  'cache_types' =>
  array (
    'config' => 1,
    'layout' => 1,
    'block_html' => 1,
    'collections' => 1,
    'reflection' => 1,
    'db_ddl' => 1,
    'eav' => 1,
    'customer_notification' => 1,
    'full_page' => 1,
    'config_integration' => 1,
    'config_integration_api' => 1,
    'translate' => 1,
    'config_webservice' => 1,
  ),
  'install' =>
  array (
    'date' => 'Mon, 31 Jul 2017 15:15:05 +0000',
  ),
);
