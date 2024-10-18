<?php

defined('BASEPATH') or exit('No direct script access allowed');

class CI_Layout
{
  protected $layout;
  protected $CI;

  public function __construct($params = array())
  {
    $this->CI = &get_instance();
  }

  public function view($view, $vars = array(), $return = FALSE)
  {
    $vars['content'] = $this->CI->load->view($view, $vars, TRUE);
    $layout_path = 'layouts' . '/' . $this->layout;
    return $this->CI->load->view($layout_path, $vars, $return);
  }

  public function set($layout)
  {
    $this->layout = $layout;
  }
}
