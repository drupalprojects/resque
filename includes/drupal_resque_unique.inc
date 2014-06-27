<?php
/**
 * @file
 * Contains DrupalResqueUnique.
 */

class DrupalResqueUnique extends DrupalResque implements UniqueQueueInterface {
  /**
   * {@inheritdoc}
   */
  public function createUniqueItem($key, $data) {
    $token = NULL;

    // Check to see if class name was specified in the data array.
    if (!empty($data['class_name'])) {
      $this->class_name = $data['class_name'];
    }
    $queues = module_invoke_all('cron_queue_info');
    drupal_alter('cron_queue_info', $queues);
    // Add the worker callback.
    $data['worker_callback'] = $queues[$this->name]['worker callback'];
    $data['drupal_unique_key'] = $this->name . ':' . $key;
    Resque_Event::listen(
      'beforeEnqueue',
      array('DrupalResqueUniquePlugin', 'beforeEnqueue')
    );
    $token = Resque::enqueue($this->name, $this->class_name, $data, TRUE);

    return $token;
  }
}
