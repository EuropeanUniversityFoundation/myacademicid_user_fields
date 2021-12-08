<?php

namespace Drupal\myacademicid_user_fields\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for MyAcademicID user fields routes.
 */
class MyacademicidUserFieldsController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
