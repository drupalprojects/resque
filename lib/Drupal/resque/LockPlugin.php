<?php
/**
 * @file
 * Contains Drupal\resque\PerformLockPlugin.
 */

namespace Drupal\resque;

use Resque as Php_Resque;
use Resque_Job;

class LockPlugin {

  /**
   * Check if the job can acquire the processing lock.
   *
   * @param object $job
   *   The resque job
   *
   * @return bool
   *   True, if the job was acquired, else false and requeue the job.
   */
  public static function beforePerform(Resque_Job $job) {
    if (Php_Resque::redis()->exists($job->payload['args'][0]['drupal_unique_key'])) {
      if ($job->payload['args'][0]['requeue']) {
        Php_Resque::enqueue($job->queue, $job->payload['class'], $job->payload['args'][0], TRUE);
        return FALSE;
      }
      else {
        return FALSE;
      }
    }
    else {
      Php_Resque::redis()->set($job->payload['args'][0]['drupal_unique_key'], '1');
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
