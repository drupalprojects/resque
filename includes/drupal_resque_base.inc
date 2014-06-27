<?php
/**
 * @file
 * Contains DrupalResqueBase.
 */

class DrupalResqueBase extends DrupalResqueAbstract {
  /**
   * {@inheritdoc}
   */
  public function perform() {
    $function = $this->args['worker_callback'];
    unset($this->args['worker_callback']);
    $function($this->args);
  }
}
