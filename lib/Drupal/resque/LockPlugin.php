<?php
/**
 * @file
 * Contains Drupal\resque\LockPlugin.
 *
 * Provides a resource lock for the Resque Job Perform function.
 */

namespace Drupal\resque;

use \Resque as Php_Resque;
use \Resque_Job;
use \Resque_Job_DontPerform;

/**
 * Provides semaphore-like behavior for Resque Jobs, by using the event hooks.
 */
class LockPlugin {
  /**
   * Attempts to acquire the jobs unique lock, or requeues the job.
   *
   * @param Resque_Job $job
   *   The resque job.
   *
   * @return bool
   *   If the job was acquired.
   *
   * @throws \Resque_Job_DontPerform
   */
  public static function beforePerform(Resque_Job $job) {
    // Attempt to acquire the job lock.
    if (!self::lockJob($job)) {
      // Attempt to requeue this job.
      QueuePlugin::requeueJob($job);
      throw new \Resque_Job_DontPerform();
    }
    else {
      // The job was acquired.
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
    // Clear the job lock.
    self::unlockJob($job);
  }

  /**
   * Clear the unique queue after the job has failed.
   *
   * @param object $exception
   *   Exception that occurred.
   * @param Resque_Job $job
   *   The job that failed.
   *
   * @throws \Exception
   */
  public static function onFailure($exception, Resque_Job $job) {
    // Clear the job lock.
    self::unlockJob($job);
  }

  /**
   * Lock the given job, using the drupal_unique_key defined in the payload.
   *
   * @param Resque_Job $job
   *   The base job to lock.
   *
   * @return bool
   *   If the job was locked.
   */
  public static function lockJob(Resque_Job $job) {
    if (Php_Resque::redis()->setnx(
      $job->payload['args'][0]['drupal_unique_key'],
      '1'
    )) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Unlock the given job, using the drupal_unique_key defined in the payload.
   *
   * @param Resque_Job $job
   *   The base job to unlock.
   *
   * @return bool
   *   If the job was unlocked.
   */
  public static function unlockJob(Resque_Job $job) {
    if (Php_Resque::redis()->exists(
      $job->payload['args'][0]['drupal_unique_key']
    )) {
      Php_Resque::redis()->del(
        $job->payload['args'][0]['drupal_unique_key']
      );

      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}
