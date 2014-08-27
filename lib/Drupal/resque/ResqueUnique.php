<?php
/**
 * @file
 * Contains Drupal\resque\Unique.
 */

namespace Drupal\resque;

use Resque_Event;
use Resque as Php_Resque;

class ResqueUnique extends Resque {
  /**
   * {@inheritdoc}
   */
  public function createUniqueItem($key, $data) {
    $token = NULL;

    $queues = module_invoke_all('cron_queue_info');
    drupal_alter('cron_queue_info', $queues);

    // Check to see if class name was specified in the data array.
    if (!empty($queues[$this->name]['class'])) {
      $this->className = $queues[$this->name]['class'];
    }
    elseif (!empty($data['worker_callback'])) {
      // Add the worker callback.
      $data['worker_callback'] = $queues[$this->name]['worker callback'];
    }

    $data['drupal_unique_key'] = $this->name . ':' . $key;
    Resque_Event::listen(
      'beforeEnqueue',
      array('Drupal\resque\UniquePlugin', 'beforeEnqueue')
    );
    $token = Php_Resque::enqueue($this->name, $this->className, $data, TRUE);

    return $token;
  }
}
