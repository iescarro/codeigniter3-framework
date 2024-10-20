<?php

class CI_Job
{
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
