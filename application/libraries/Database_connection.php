<?php

use Propel\Runtime\Propel;
use Propel\Runtime\Connection\ConnectionManagerSingle;
use Propel\Common\Config\ConfigurationManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
/**
 *
 */
class Database_connection
{

  function connect($conf="default"){
    // Load the configuration file
   $configManager = new ConfigurationManager('./propel.php' );

    // Set up the connection manager
   $manager = new ConnectionManagerSingle();
   $manager->setConfiguration( $configManager->getConnectionParametersArray()[ $conf ] );
   $manager->setName('default');

   $defaultLogger = new Logger('defaultLogger');
   $defaultLogger->pushHandler(new StreamHandler('application/logs/propel_error.log', Logger::WARNING));
   Propel::getServiceContainer()->setLogger('defaultLogger', $defaultLogger);
   $queryLogger = new Logger('default');
   $queryLogger->pushHandler(new StreamHandler("application/logs/propel_$conf.log"));
   Propel::getServiceContainer()->setLogger($conf, $queryLogger);
    // Add the connection manager to the service container
   $serviceContainer = Propel::getServiceContainer();
   $serviceContainer->setAdapterClass($conf, 'mysql');
   $serviceContainer->setConnectionManager($conf, $manager);
   $serviceContainer->setDefaultDatasource($conf);
  }
}
