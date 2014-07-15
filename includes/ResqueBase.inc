<?php
/**
 * @file
 * Contains ResqueBase.
 */

class ResqueBase extends ResqueAbstract {
  /**
   * {@inheritdoc}
   */
  public function perform() {
    $function = $this->args['worker_callback'];
    unset($this->args['worker_callback']);
    $function($this->args);
  }
}
