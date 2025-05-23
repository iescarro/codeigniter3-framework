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
use Symfony\Component\Console\Attribute\AsCommand;

class GenerateCommand extends Command
{
  protected static $defaultName = 'generate:scaffold';

  protected function configure()
  {
    $this
      ->setName('generate:scaffold')
      ->setDescription('')
      ->addArgument('component', InputArgument::REQUIRED, 'The component to run (e.g., scaffold)')
      ->addArgument('fields', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Fields to scaffold (e.g., title:string content:text)');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $component = $input->getArgument('component');
    $fields = $input->getArgument('fields');

    $generator = new Generator($component, $fields);
    $generator->scaffold($output);

    return Command::SUCCESS;
  }
}

class Generator
{
  private $component;
  private $fields;

  function __construct($component, $fields)
  {
    $this->component = $component;
    foreach ($fields as $field) {
      [$name, $type] = explode(':', $field);
      $this->fields[$name] = $type;
    }
  }

  function scaffold($output)
  {
    $this->generate_model($output);
    $this->generate_helper($output);
    $this->generate_migration($output);
    $this->generate_controller($output);
    $this->generate_views($output);
  }

  function generate_model($output)
  {
    $dir = 'application/models';
    $class = ucwords($this->component) . '_model';
    $var = '$' . lcfirst($this->component);
    $table = lcfirst(pluralize($this->component));
    $filename = $dir . '/' . $class  . '.php';
    $content = "<?php

defined('BASEPATH') or exit('No direct script access allowed');

class {class} extends CI_Model
{
	function __construct()
	{
		\$this->load->database();
	}

	function save({var})
	{
		\$this->db->insert('{table}', {var});
		return \$this->db->insert_id();
	}

	function read(\$id)
	{
		return \$this->db->get_where('{table}', ['id' => \$id])->row();
	}

	function find_all()
	{
		return \$this->db->get('{table}')->result();
	}

	function update({var}, \$id)
	{
		\$this->db->update('{table}', {var}, ['id' => \$id]);
	}

	function delete(\$id)
	{
		\$this->db->delete('{table}', ['id' => \$id]);
	}
}";
    $content = str_replace(
      ['{var}', '{table}', '{class}'],
      [$var, $table, $class],
      $content
    );
    file_put_contents($filename, $content);
    $output->writeln('<info>Model generated successfully!</info>');
  }

  function generate_helper($output)
  {
    $dir = 'application/helpers';
    $component = lcfirst($this->component);
    $helper = $component . '_helper';
    $var = '$' . lcfirst($component);
    $table = lcfirst(pluralize($component));
    $filename = $dir . '/' . $helper  . '.php';
    $content = "<?php

defined('BASEPATH') or exit('No direct script access allowed');

function {component}_form()
{
	\$obj = &get_instance();
	return [
{fields}
	];
}";
    $fields = '';
    foreach ($this->fields as $column => $type) {
      $fields .= "		'{$column}' => \$obj->input->post('{$column}'),\n";
    }
    $content = str_replace(
      ['{var}', '{table}', '{component}', '{fields}'],
      [$var, $table, $component, $fields],
      $content
    );
    file_put_contents($filename, $content);
    $output->writeln('<info>Helper generated successfully!</info>');
  }

  function generate_migration($output, $prefix = 'Create')
  {
    $dir = 'application/migrations';
    // $table = lcfirst(pluralize($this->component));
    // $class = $prefix . $table;
    // $var = '$' . lcfirst($this->component);

    $components = explode('_', $this->component); // split_pascal_case($this->component);
    if ($prefix) {
      array_unshift($components, $prefix);
    }
    $table = pluralize(lcfirst(end($components)));
    // print_r($table);
    // print_r($prefix);
    // print_r($components);

    $last_index = count($components) - 1;
    $components[$last_index] = pluralize(lcfirst($components[$last_index]));

    $command = ucfirst(reset($components));
    $migration = MigrationGenerator::create($command);
    $migration->create_migration($components, $this->fields, $dir, $table, $output);

    /*
    $class = implode('_', $components);
    $columns = '';
    if ($this->fields) {
      foreach ($this->fields as $column => $type) {
        $constraint = $type == 'varchar' ? "\n				'constraint' => 255," : "";
        $columns .= "			'$column' => array(
				'type' => '$type',$constraint
				'null' => TRUE,
			),\n";
      }
    }
    $filename = $dir . '/' . date('YmdHis')  . '_' . $class . '.php';
    $content = "<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_{class} extends CI_Migration
{
	function up()
	{
		\$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 5,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
{columns}
		));
		\$this->dbforge->add_key('id', TRUE);
		\$this->dbforge->create_table('{table}');
	}

	function down()
	{
		\$this->dbforge->drop_table('{table}');
	}
}";
    $content = str_replace(
      ['{table}', '{class}', '{columns}'],
      [$table, $class, $columns],
      $content
    );
    file_put_contents($filename, $content);
    $output->writeln('<info>Migration generated successfully!</info>');
    */
  }

  function generate_controller($output)
  {
    $dir = 'application/controllers';
    $class = ucwords(pluralize($this->component));
    $component = lcfirst($this->component);
    $var = '$' . $component;
    $table = pluralize($component);
    $model = $component . '_model';
    $var_model = '$' . $model;
    $filename = $dir . '/' . $class  . '.php';
    $content = "<?php

defined('BASEPATH') or exit('No direct script access allowed');

class {class} extends CI_Controller
{
	var {var_model};

	var \$input;
	var \$layout;

	function __construct()
	{
		parent::__construct();
		\$this->load->helper(['html', 'url', 'form', '{component}']);
		\$this->load->library(['form_validation', 'layout']);
		\$this->load->model(['{model}']);
		\$this->layout->set('layouts/app');
	}

	function index()
	{
		\$data['{table}'] = \$this->{model}->find_all();
		\$this->layout->view('{table}/index', \$data);
	}

	function create()
	{
		if (\$this->input->post()) {
			{var} = {component}_form();
			\$this->{model}->save({var});
      redirect('{table}');
		}
		\$this->layout->view('{table}/create');
	}

	function edit(\$id)
	{
		if (\$this->input->post()) {
			{var} = {component}_form();
			\$this->{model}->update({var}, \$id);
			redirect('{table}');
		}
		\$data['{component}'] = \$this->{model}->read(\$id);
		\$this->layout->view('{table}/edit', \$data);
	}

	function delete(\$id)
	{
		\$this->{model}->delete(\$id);
		redirect('{table}');
	}
}";
    $content = str_replace(
      ['{var}', '{table}', '{model}', '{class}', '{var_model}', '{component}'],
      [$var, $table, $model, $class, $var_model, $component],
      $content
    );
    file_put_contents($filename, $content);
    $output->writeln('<info>Controller generated successfully!</info>');
  }

  function generate_views($output)
  {
    $component = lcfirst($this->component);
    $var = '$' . $component;
    $vars = '$' . pluralize($component);
    $model = ucfirst($component);
    $models = pluralize($model);
    $table = pluralize($component);
    $dir = 'application/views/' . pluralize($component);
    if (!is_dir($dir)) {
      if (mkdir($dir, 0755, true)) {
      }
    }

    // Index
    $filename = $dir . '/index.php';
    $content = "<h3>{models}</h3>
<p>
	<?php echo anchor('{table}/create', 'Create {model}', 'class=\"btn btn-outline-success\"') ?>
</p>
<table class=\"table table-hover\">
	<tr>
{headers}
		<th></th>
	</tr>
	<?php foreach ({vars} as {var}): ?>
		<tr>
{columns}
			<td>
				<?php echo anchor('{table}/edit/' . {var}->id, 'Edit'); ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>";
    $headers = '';
    $columns = '';
    foreach ($this->fields as $column => $type) {
      $header = ucfirst($column);
      $headers .= "		<th>{$header}</th>\n";
      $columns .= "			<td><?php echo {$var}->{$column} ?></td>\n";
    }
    $content = str_replace(
      ['{var}', '{vars}', '{models}', '{model}', '{headers}', '{columns}', '{table}'],
      [$var, $vars, $models, $model, $headers, $columns, $table],
      $content
    );
    file_put_contents($filename, $content);

    // Create
    $filename = $dir . '/create.php';
    $content = "<h3>Create {component}</h3>
<?php echo form_open('{table}/create'); ?>
{fields}
	<p>
		<?php echo form_submit('submit', 'Save {component}', 'class=\"btn btn-outline-success\"'); ?>
		or <?php echo anchor('{table}', 'cancel'); ?>
	</p>
<?php echo form_close(); ?>";
    $fields = '';
    foreach ($this->fields as $column => $type) {
      $header = ucfirst($column);
      $fields .= "	<p>
		{$header}<br>
		<?php echo form_input('$column', \$this->input->post('$column'), 'class=\"form-control\"'); ?>
	</p>\n";
    }
    $content = str_replace(
      ['{component}', '{vars}', '{models}', '{model}', '{fields}', '{table}'],
      [$component, $vars, $models, $model, $fields, $table],
      $content
    );
    file_put_contents($filename, $content);

    // Edit
    $filename = $dir . '/edit.php';
    $content = "<h3>Edit {component}</h3>
<?php echo form_open('{table}/edit/' . {var}->id) ?>
{fields}
	<p>
		<?php echo form_submit('submit', 'Update {component}', 'class=\"btn btn-outline-success\"') ?>
		or <?php echo anchor('{table}', 'cancel'); ?>
	</p>
<?php echo form_close() ?>

<?php echo form_open('{table}/delete/' . {var}->id, array('onsubmit', 'return confirmDelete')) ?>
	<?php echo form_hidden(\$this->security->get_csrf_token_name(), \$this->security->get_csrf_hash()); ?>
	<button type='submit' class=\"btn btn-outline-danger\">Delete</button>
<?php echo form_close() ?>";
    $fields = '';
    foreach ($this->fields as $column => $type) {
      $header = ucfirst($column);
      $fields .= "	<p>
		{$header}<br>
		<?php echo form_input('$column', {$var}->{$column}, 'class=\"form-control\"'); ?>
	</p>\n";
    }
    $content = str_replace(
      ['{var}', '{vars}', '{models}', '{model}', '{fields}', '{columns}', '{table}', '{component}'],
      [$var, $vars, $models, $model, $fields, $columns, $table, $component],
      $content
    );
    file_put_contents($filename, $content);

    $output->writeln('<info>Views generated successfully!</info>');
  }
}

class MigrationGenerator
{
  static function create($command)
  {
    // print_r($command);
    if ($command == 'Create') {
      return new CreateMigrationGenerator();
    } else if ($command == 'Add') {
      return new AddMigrationGenerator();
    }
    return new EmptyMigrationGenerator();
  }
}

class EmptyMigrationGenerator extends MigrationGenerator
{
  function create_migration($components, $fields, $dir, $table, $output)
  {
    $class = implode('_', $components);
    $filename = $dir . '/' . date('YmdHis')  . '_' . $class . '.php';
    $content = "<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_{class} extends CI_Migration
{
	function up()
	{
		\$fields = array();
		\$this->dbforge->modify_column('{table}', \$fields);
	}

	function down()
	{
		\$fields = array();
		\$this->dbforge->modify_column('{table}', \$fields);
	}
}";
    $content = str_replace(
      ['{table}', '{class}'],
      [$table, $class],
      $content
    );
    file_put_contents($filename, $content);
    $output->writeln('<info>Migration generated successfully!</info>');
  }
}

class AddMigrationGenerator extends MigrationGenerator
{
  function create_migration($components, $fields, $dir, $table, $output)
  {
    // print_r($fields);
    $class = implode('_', $components);
    array_shift($components);
    // print_r($components);
    $components_without_command = implode('_', $components);
    $column_name = trim(explode('to', $components_without_command)[0], '_');
    // print_r($column_name);
    $filename = $dir . '/' . date('YmdHis')  . '_' . $class . '.php';
    $content = "<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_{class} extends CI_Migration
{
	function up()
	{
		\$fields = array(
			'{column_name}' => array(
				'type' => 'INT',
				'constraint' => 5,
				'unsigned' => TRUE
			),
		);
		\$this->dbforge->add_column('{table}', \$fields);
	}

	function down()
	{
		\$this->dbforge->drop_column('{table}', '{column_name}');
	}
}";
    $content = str_replace(
      ['{table}', '{class}', '{column_name}'],
      [$table, $class, $column_name],
      $content
    );
    file_put_contents($filename, $content);
    $output->writeln('<info>Migration generated successfully!</info>');
  }
}

class CreateMigrationGenerator extends MigrationGenerator
{
  function create_migration($components, $fields, $dir, $table, $output)
  {
    $class = implode('_', $components);
    $columns = '';
    if ($fields) {
      foreach ($fields as $column => $type) {
        $constraint = $type == 'varchar' ? "\n				'constraint' => 255," : "";
        $columns .= "			'$column' => array(
				'type' => '$type',$constraint
				'null' => TRUE,
			),\n";
      }
    }
    $filename = $dir . '/' . date('YmdHis')  . '_' . $class . '.php';
    $content = "<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_{class} extends CI_Migration
{
	function up()
	{
		\$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 5,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
{columns}
		));
		\$this->dbforge->add_key('id', TRUE);
		\$this->dbforge->create_table('{table}');
	}

	function down()
	{
		\$this->dbforge->drop_table('{table}');
	}
}";
    $content = str_replace(
      ['{table}', '{class}', '{columns}'],
      [$table, $class, $columns],
      $content
    );
    file_put_contents($filename, $content);
    $output->writeln('<info>Migration generated successfully!</info>');
  }
}

function split_pascal_case($input)
{
  // Use preg_split to split at each uppercase letter, keeping each part
  return preg_split('/(?=[A-Z])/', $input, -1, PREG_SPLIT_NO_EMPTY);
}

function pluralize($word)
{
  $plural = [
    '/(quiz)$/i' => "$1zes",
    '/^(ox)$/i' => "$1en",
    '/([m|l])ouse$/i' => "$1ice",
    '/(matr|vert|ind)(ix|ex)$/i' => "$1ices",
    '/(x|ch|ss|sh)$/i' => "$1es",
    '/([^aeiouy]|qu)y$/i' => "$1ies",
    '/(hive)$/i' => "$1s",
    '/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
    '/(shea|lea|loa|thie)f$/i' => "$1ves",
    '/sis$/i' => "ses",
    '/([ti])um$/i' => "$1a",
    '/(tomat|potat|ech|her|vet)o$/i' => "$1oes",
    '/(bu)s$/i' => "$1ses",
    '/(alias)$/i' => "$1es",
    '/(octop)us$/i' => "$1i",
    '/(ax|test)is$/i' => "$1es",
    '/(us)$/i' => "$1es",
    '/s$/i' => "s",
    '/$/' => "s"
  ];

  $irregular = [
    'move' => 'moves',
    'foot' => 'feet',
    'goose' => 'geese',
    'sex' => 'sexes',
    'child' => 'children',
    'man' => 'men',
    'tooth' => 'teeth',
    'person' => 'people'
  ];

  $uncountable = [
    'sheep',
    'fish',
    'deer',
    'series',
    'species',
    'money',
    'rice',
    'information',
    'equipment'
  ];

  if (in_array(strtolower($word), $uncountable)) {
    return $word;
  }

  foreach ($irregular as $pattern => $result) {
    $pattern = '/' . $pattern . '$/i';

    if (preg_match($pattern, $word)) {
      return preg_replace($pattern, $result, $word);
    }
  }

  foreach ($plural as $pattern => $result) {
    if (preg_match($pattern, $word)) {
      return preg_replace($pattern, $result, $word);
    }
  }

  return $word;
}
