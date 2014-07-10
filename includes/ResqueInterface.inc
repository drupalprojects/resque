<?php
/**
 * @file
 * Contains ResqueInterface, ResqueAbstract.
 */

/**
 * Implement this interface to use your own classes to run your jobs.
 *
 * Modeled after https://github.com/chrisboulton/php-resque and
 * https://github.com/resque/resque.
 */
interface ResqueInterface {
  /**
   * This will always run before the perform method.
   */
  public function setUp();

  /**
   * This is the method where you can use your jobs arguments to perform tasks.
   */
  public function perform();
}

/**
 * Abstract class that implements Resque interface.
 */
abstract class ResqueAbstract implements ResqueInterface {
  /**
   * {@inheritdoc}
   */
  public function setup() {
    // Do nothing.
  }
}
