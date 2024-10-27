<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$system_commands_dir = __DIR__ . '/vendor/iescarro/codeigniter3-framework/system/commands';
$app_commands_dir = __DIR__ . '/application/libraries/commands';

load_commands($system_commands_dir, $application);
load_commands($app_commands_dir, $application);

function load_commands($commands_dir, $app)
{
  if (is_dir($commands_dir)) {
    foreach (glob($commands_dir . '/*.php') as $filename) {
      require_once $filename;

      $class_name = 'CodeIgniter3\\Commands\\' . basename($filename, '.php');

      if (class_exists($class_name)) {
        $app->add(new $class_name());
      }
    }
  }
}

$application->run();
