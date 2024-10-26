<?php

/**
 * CodeIgniter3
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2024, CodeIgniter3 Team
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

require '../vendor/autoload.php';

use Pusher\Pusher;

class CI_Broadcast {} // HACK: We don't need this

interface IBroadcast
{
  function on();
}

function broadcast($event)
{
  return new Broadcast($event);
}

class Broadcast
{
  var $driver;
  var $channel;
  var $event;

  function __construct($event)
  {
    $CI = &get_instance();

    $CI->load->config('broadcast');
    $broadcast_drivers = $CI->config->item('broadcast_drivers');
    $default_broadcast_driver = $CI->config->item('default_broadcast_driver');
    $config = $broadcast_drivers[$default_broadcast_driver];

    $driver = ucfirst($default_broadcast_driver) . 'Driver';
    $this->driver = new $driver($config);

    $this->channel = $event->on();

    $this->event = $event;
  }

  function data()
  {
    $reflectionClass = new ReflectionClass($this->event);
    $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

    $data = [];
    foreach ($properties as $property) {
      $property->setAccessible(true); // Make private properties accessible
      $data[$property->getName()] = $property->getValue($this->event); // Get the property name and value
    }
    return $data;
  }

  function go()
  {
    $this->driver->trigger($this->channel, 'message', $this->data());
  }
}

class PusherDriver
{
  var $pusher;

  function __construct($config)
  {
    $this->pusher = new Pusher(
      $config['key'],
      $config['secret'],
      $config['app_id'],
      [
        'cluster' => $config['options']['cluster'],
        'useTLS' => true,
      ]
    );
  }

  function trigger($channel, $event, $data)
  {
    $this->pusher->trigger($channel, $event, $data);
  }
}
