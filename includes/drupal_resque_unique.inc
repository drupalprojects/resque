<?php
/**
* @file
* Contains DrupalResqueUnique
*/

class DrupalResqueUnique extends DrupalResque implements UniqueQueueInterface {
  /**
   * Attempts to create a resque queue item.
   *
   * @param string $key
   *   The key to identify this queue as unique.
   * @param array $data
   *   Arbitrary data to be associated with the new task in the queue.
   * @return boolean
   *   If item was enqueued, TRUE otherwise FALSE.
   */
  public function createUniqueItem($key, $data) {
    $token = NULL;

    // Check to see if class name was specified in the data array.
    if (!empty($data['class_name'])) {
      $this->class_name = $data['class_name'];
    }
    $queues = module_invoke_all('cron_queue_info');
    drupal_alter('cron_queue_info', $queues);
    // Add the worker callback
    $data['worker_callback'] = $queues[$this->name]['worker callback'];
    $data['drupal_unique_key'] = $this->name . ':' . $key;
    Resque_Event::listen('beforeEnqueue', array('DrupalResqueUniquePlugin', 'beforeEnqueue'));
    $token = Resque::enqueue($this->name, $this->class_name, $data, TRUE);

    return $token;
  }
}