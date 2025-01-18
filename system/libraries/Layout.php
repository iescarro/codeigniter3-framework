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

class CI_Layout
{
  protected $layout;
  protected $theme;
  protected $CI;

  public function __construct($params = array())
  {
    $this->CI = &get_instance();
  }

  public function view($view, $vars = array(), $return = FALSE)
  {
    $vars['content'] = $this->CI->load->view($this->theme . '/' . $view, $vars, TRUE);
    $layout_path = 'layouts' . '/' . $this->layout;
    return $this->CI->load->view($this->theme . '/' . $layout_path, $vars, $return);
  }

  public function set($layout)
  {
    $this->layout = $layout;
  }

  function theme($theme = '')
  {
    if ($theme) {
      $this->theme = $theme;
    }
    return $this->theme;
  }
}

function get_theme()
{
  $obj = &get_instance();
  $obj->load->library('layout');
  return $obj->layout->theme();
}

function load_view($view, $data = null)
{
  $obj = &get_instance();
  $obj->load->library('layout');
  $obj->load->view(get_theme() . '/' . $view, $data);
}
