<?php

/**
 * @file
 * Drupal resque administration and server settings.
 */

/**
 * Settings form builder callback.
 */
function drupal_resque_settings() {

  $form['redis_host'] = array(
    '#type' => 'textfield',
    '#title' => t('Redis Host'),
    '#description' => t('Set the redis server to be used with resque, defaults to localhost.'),
    '#default_value' => variable_get('redis_host', 'localhost'),
  );
  $form['redis_password'] = array(
    '#type' => 'textfield',
    '#title' => t('Redis password'),
    '#description' => t('Set the redis password to be used with resque, defaults to none.'),
    '#default_value' => variable_get('redis_password', NULL),
  );
  $form['redis_port'] = array(
    '#type' => 'textfield',
    '#title' => t('Redis port'),
    '#description' => t('Set the redis port to be used with resque, defaults to 6379.'),
    '#default_value' => variable_get('redis_password', '6379'),
  );

  return system_settings_form($form);
}