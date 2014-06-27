<?php
/**
 * @file
 *
 * Contains DrupalResqueUniquePlugin.
 */

/**
 * Using event hooks defined here:
 * https://github.com/resque/resque/blob/master/docs/HOOKS.md
 */
class DrupalResqueUniquePlugin {
  /**
   * Check the job has a unique instance.
   *
   * @param string $class
   *   string containing the name of scheduled job.
   * @param array $arguments
   *   array of arguments supplied to the job.
   * @param string $queue
   *   string containing the name of the queue the job was added to.
   *
   * @return boolean
   *   Queue up the job if another with the same key doesn't exist.
   */
  function beforeEnqueue($class, $arguments, $queue) {
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
   *   The job that failed
   */
  function afterPerform(Resque_Job $job) {
    if (Resque::redis()->exists($job->payload['args'][0]['drupal_unique_key'])) {
      Resque::redis()->del($job->payload['args'][0]['drupal_unique_key']);
    }
  }

  /**
   * Clear the unique queue after the job has failed.
   *
   * @param stdClass $exception
   *   Exception that occurred.
   * @param Resque_Job $job
   *   The job that failed
   */
  function onFailure($exception, Resque_Job $job) {
    if (Resque::redis()->exists($job->payload['args'][0]['drupal_unique_key'])) {
      Resque::redis()->del($job->payload['args'][0]['drupal_unique_key']);
    }
  }
}