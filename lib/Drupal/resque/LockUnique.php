<?php
/**
 * @file
 * Contains Drupal\resque\Unique.
 */

namespace Drupal\resque;

use \Resque as Php_Resque;

/**
 * Defines the LockUnique functionality.
 */
class LockUnique extends Resque {
  /**
   * Create a unique item to be processed serially with PHP Resque.
   *
   * @param string $key
   *   The key to lock the job with.
   * @param array $data
   *   The Resque_Job data.
   *
   * @return string
   *   The status for enqueuing a Resque_Job.
   */
  public function createUniqueItem($key, $data) {
    // Check to see if class name was specified in the data array.
    $queues = module_invoke_all('cron_queue_info');
    drupal_alter('cron_queue_info', $queues);

    if (!empty($queues[$this->name]['class'])) {
      $this->className = $queues[$this->name]['class'];
    }
    else {
      // Add the worker callback.
      $data['worker_callback'] = $queues[$this->name]['worker callback'];
    }
    $data['drupal_unique_key'] = $this->name . ':' . $key;

    return Php_Resque::enqueue($this->name, $this->className, $data, TRUE);
  }
}
