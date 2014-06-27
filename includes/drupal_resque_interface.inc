<?php
/**
 * @file
 * Contains DrupalResqueInterface, DrupalResqueAbstract.
 */

/**
 * Implement this interface to use your own classes to run your jobs.
 *
 * Modelled after https://github.com/chrisboulton/php-resque and
 * https://github.com/resque/resque.
 */
interface DrupalResqueInterface {
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
 * Abstract class that implements resque interface.
 */
abstract class DrupalResqueAbstract implements DrupalResqueInterface {
  /**
   * {@inheritdoc}
   */
  public function setup() {
    // Do nothing.
  }
}
