<?php
/**
 * @file
 * Contains Drupal\resque\QueuePlugin.
 *
 * Provides functionality for queueing Resque Jobs within a Resque Job, with
 * limits.
 */

namespace Drupal\resque;

use \Resque as Php_Resque;
use \Resque_Job;

/**
 * Provides an interface to general queueing functionality.
 */
class QueuePlugin {
  /**
   * Queue the given job.
   *
   * @param Resque_Job $job
   *   The base job to queue.
   */
  public static function queueJob(Resque_Job $job) {
    // Queue the given job.
    Php_Resque::enqueue(
      $job->queue,
      $job->payload['class'],
      $job->payload['args'][0],
      TRUE
    );
  }

  /**
   * Attempts to requeue a job by accessing its requeue and limit state.
   *
   * @param Resque_Job $job
   *   The base job to requeue.
   *
   * @throws \Exception
   */
  public static function requeueJob(Resque_Job $job) {
    // Check if the job should be requeued.
    if ($job->payload['args'][0]['requeue']) {
      // Check if the limit has been exceeded yet.
      if (isset($job->payload['args'][0]['limit'])
        && $job->payload['args'][0]['limit'] > 0
      ) {
        // Update requeue limit.
        $job->payload['args'][0]['limit']--;

        // Requeue job.
        self::queueJob($job);
      }
      else {
        throw new \Exception('The Job has exceeded the requeue limit.');
      }
    }
    else {
      throw new \Exception('The Job does not have requeue enabled');
    }
  }
}
