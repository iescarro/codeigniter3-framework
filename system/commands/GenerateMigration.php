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

namespace CodeIgniter3\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMigration extends Command
{
  protected static $defaultName = 'generate:migration';

  protected function configure()
  {
    $this
      ->setName('generate:migration')
      ->setDescription('')
      ->addArgument('component', InputArgument::REQUIRED, 'The component to run (e.g., migration)')
      ->addArgument('fields', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Fields to scaffold (e.g., title:string content:text)');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $component = $input->getArgument('component');
    $fields = $input->getArgument('fields');

    $generator = new Generator($component, $fields);

    $components = explode('_', $component);
    // print_r($components);
    if (count($components) == 1) {
      $generator->generate_migration($output);
    } else {
      $prefix = ''; // implode('_', $components);
      $generator->generate_migration($output, $prefix);
    }

    return Command::SUCCESS;
  }
}
