<?php
/**
 * @file
 * Contains Drupal\resque\Unique.
 */

namespace Drupal\resque;

use Resque as Php_Resque;
use Resque_Job;

class LockUnique extends Resque {
  /**
   * {@inheritdoc}
   */
  public function createUniqueItem($key, $data) {
    $token = NULL;

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

  /**
   * Clear the unique queue after the job has performed.
   *
   * @param Resque_Job $job
   *   The job that failed.
   */
  public static function afterPerform(Resque_Job $job) {
    if (Php_Resque::redis()->exists($job->payload['args'][0]['drupal_unique_key'])) {
      Php_Resque::redis()->del($job->payload['args'][0]['drupal_unique_key']);
    }
  }

  /**
   * Clear the unique queue after the job has failed.
   *
   * @param object $exception
   *   Exception that occurred.
   * @param Resque_Job $job
   *   The job that failed.
   */
  public static function onFailure($exception, Resque_Job $job) {
    if (Php_Resque::redis()->exists($job->payload['args'][0]['drupal_unique_key'])) {
      Php_Resque::redis()->del($job->payload['args'][0]['drupal_unique_key']);
    }
  }
}
