<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;


// You can now use your logger
function write_log($msg){
  // create a log channel
  $log = new Logger('application');
  $log->pushHandler(new StreamHandler('application/logs/default.log', Logger::INFO));
  $log->pushHandler(new FirePHPHandler());
  $log->info($msg);
}

function task_run_logger($msg){
  // create a log channel
  $log = new Logger('application');
  $log->pushHandler(new StreamHandler('application/logs/task_run_logger.log', Logger::INFO));
  $log->pushHandler(new FirePHPHandler());
  $log->info($msg);
}
