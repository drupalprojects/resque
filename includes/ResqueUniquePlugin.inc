<?php
/**
 * @file
 * Contains ResqueUniquePlugin.
 */

/**
 * Using event hooks defined in resque library.
 */
class ResqueUniquePlugin {
  /**
   * Check the job has a unique instance.
   *
   * @param string $class
   *   String containing the name of scheduled job.
   * @param array $arguments
   *   Array of arguments supplied to the job.
   * @param string $queue
   *   String containing the name of the queue the job was added to.
   *
   * @return bool
   *   Queue up the job if another with the same key doesn't exist.
   */
  public function beforeEnqueue($class, array $arguments, $queue) {
    if (Resque::redis()->exists($arguments['drupal_unique_key'])) {
      return FALSE;
    }

    Resque::redis()->set($arguments['drupal_unique_key'], '1');

    return TRUE;
  }

  /**
   * Clear the unique queue after the job has performed.
   *
   * @param Resque_Job $job
   *   The job that failed.
   */
  public function afterPerform(Resque_Job $job) {
    if (Resque::redis()->exists($job->payload['args'][0]['drupal_unique_key'])) {
      Resque::redis()->del($job->payload['args'][0]['drupal_unique_key']);
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
  public function onFailure($exception, Resque_Job $job) {
    if (Resque::redis()->exists($job->payload['args'][0]['drupal_unique_key'])) {
      Resque::redis()->del($job->payload['args'][0]['drupal_unique_key']);
    }
  }
}
