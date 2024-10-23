<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2019 - 2022, CodeIgniter Foundation
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
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @copyright	Copyright (c) 2019 - 2022, CodeIgniter Foundation (https://codeigniter.com/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') or exit('No direct script access allowed');

class CI_Job
{
  public function __get($key)
  {
    return get_instance()->$key;
  }

  static function dispatch($data = array())
  {
    $obj = &get_instance();
    $obj->load->database();

    $job_class = get_called_class();

    $job = array(
      'job' => $job_class,
      'payload' => json_encode($data),
    );
    $obj->db->set('created_at', date('Y-m-d H:i:s'));
    $obj->db->set('available_at', date('Y-m-d H:i:s'));
    $obj->db->insert('jobs', $job);
    return $obj->db->insert_id();
  }

  static function work()
  {
    $now = date('Y-m-d H:i:s');
    $obj = &get_instance();
    $obj->db->where("'$now' > available_at");
    $pending_jobs = $obj->db->get('jobs')->result();
    foreach ($pending_jobs as $job) {
      $job_name = $job->job;
      $payload = json_decode($job->payload, true);

      if (class_exists($job_name)) {
        $pending_job = new $job_name($payload);
        $pending_job->handle();
        $obj->db->delete('jobs', array('id' => $job->id));
        echo "$job_name successfully handled";
      } else {
        echo "$job_name does not exist!";
      }
    }
  }
}
