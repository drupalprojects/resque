<?php
/**
 * @file
 * Contains Drupal\resque\PerformLockPlugin.
 */

namespace Drupal\resque;

use Resque as Php_Resque;
use Resque_Job_DontPerform;

class LockPlugin {
 /**
   * Check if the job can acquire the processing lock.
   *
   * @param object $job
   *   The resque job
   *
   * @return bool
   *   True, if the job was acquired.
   */
  public static function beforePerform(Resque_Job $job) {
    if (Php_Resque::redis()->exists($job['args']['drupal_unique_key'])) {
      if ($job['requeue']) {
          Resque_Event::listen(
            'beforePerform',
            array('Drupal\resque\LockPlugin', 'beforePerform')
          );
          Php_Resque::enqueue($job->name, $job->className, $job['args'], TRUE);
          return FALSE;
      }
      else {
        return FALSE;
      }
    }
    else {
      Php_Resque::redis()->set($job['args']['drupal_unique_key'], '1');
      return TRUE;
    }
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
