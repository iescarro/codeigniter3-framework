<?php

/**
 * CodeIgniter3
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2024, CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter3
 * @author	CodeIgniter3 Team
 * @copyright	Copyright (c) 2024, CodeIgniter3 Team (https://github.com/iescarro/codeigniter3-framework)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://github.com/iescarro/codeigniter3-framework
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') or exit('No direct script access allowed');

namespace CodeIgniter3\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallIgniteCommand extends Command
{
  protected static $defaultName = 'install:ignite';

  protected function configure()
  {
    $this
      ->setName('install:ignite')
      ->setDescription('');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $filename = 'ignite';
    $content = "<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

\$application = new Application();

\$system_commands_dir = __DIR__ . '/vendor/iescarro/codeigniter3-framework/system/commands';
\$app_commands_dir = __DIR__ . '/application/libraries/commands';

load_commands(\$system_commands_dir, \$application);
load_commands(\$app_commands_dir, \$application);

function load_commands(\$commands_dir, \$app)
{
  if (is_dir(\$commands_dir)) {
    foreach (glob(\$commands_dir . '/*.php') as \$filename) {
      require_once \$filename;

      \$class_name = 'CodeIgniter3\\Commands\\' . basename(\$filename, '.php');

      if (class_exists(\$class_name)) {
        \$app->add(new \$class_name());
      }
    }
  }
}

\$application->run();";
    $content = str_replace(
      [],
      [],
      $content
    );
    file_put_contents($filename, $content);

    $output->writeln('<info>Cast installed successfully!</info>');
    return Command::SUCCESS;
  }
}
