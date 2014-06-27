<?php
/**
 * @file
 * Contains DrupalResque
 */

class DrupalResque extends SystemQueue implements DrupalQueueInterface {
  /**
   * Class name that will process jobs, defaults to DrupalResqueBase.
   *
   * @var string $class_name
   */
  protected $class_name = 'DrupalResqueBase';

  /**
   * Setup redis server.
   *
   * @param string $name
   *   The name of the queue.
   */
  public function __construct($name) {
    parent::__construct($name);

    $redis_host = variable_get('redis_host', 'localhost');
    $redis_port = variable_get('redis_port', '6379');
    $redis_password = variable_get('redis_password', NULL);

    // Required if redis has a password, and/or on another server.
    if (!empty($redis_password)) {
      Resque::setBackend('redis://redis:' . $redis_password . '@' . $redis_host . ':' . $redis_port);
    }
    else {
      Resque::setBackend($redis_host . ':' . $redis_port);
    }
  }

  /**
   * Attempts to create a resque queue item.
   *
   * @param array $data
   *   Arbitrary data to be associated with the new task in the queue.
   * @return string
   *   The token to be able to track this item.
   */
  public function createItem($data) {
    $token = NULL;

    // Check to see if class name was specified in the data array.
    if (!empty($data['class_name'])) {
      $this->class_name = $data['class_name'];
    }
    $queues = module_invoke_all('cron_queue_info');
    drupal_alter('cron_queue_info', $queues);
    // Add the worker callback
    $data['worker_callback'] = $queues[$this->name]['worker callback'];
    $token = Resque::enqueue($this->name, $this->class_name, $data, TRUE);

    return $token;
  }

  /**
   * Set the class that is going to run the job.
   *
   * @param string $class_name
   *   The name of the class to process job.
   */
  function setClassName($class_name) {
    $this->class_name = $class_name;
  }

  /**
   * Get the name of the class that is processing this job.
   */
  function getClassName() {
    return $this->class_name;
  }

  /**
   * Get number of items in this particular queue.
   *
   * @return array|bool|void
   */
  public function numberOfItems() {
    return Resque::size($this->name);
  }

  /**
   * Get the status of a particular job in the queue.
   *
   * @param string $token
   *   The token string returned when creating the job.
   *
   * @return integer
   *   https://github.com/chrisboulton/php-resque/blob/master/lib/Resque/Job/Status.php
   *   1 = STATUS_WAITING
   *   2 = STATUS_RUNNING
   *   3 = STATUS_FAILED
   *   4 = STATUS_COMPLETE
   */
  public function getJobStatus($token) {
    $status = new Resque_Job_Status($token);
    return $status->get();
  }

  public function claimItem($lease_time = 300) {
    // Not implemented
    return FALSE;
  }

  public function releaseItem($item) {
    // Not implemented
    return TRUE;
  }

  public function deleteItem($item) {
    // Not implemented
    return TRUE;
  }

  public function deleteQueue() {
    // Not implemented
    return TRUE;
  }
} 