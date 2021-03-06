<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

/**
 * Event that is fired when a user's voperson_external_affilliation changes.
 */
class UserVopersonExternalAffilliationChangeEvent extends Event {

  const EVENT_NAME = 'user_vea_change';

  /**
   * The user entity.
   *
   * @var \Drupal\user\UserInterface
   */
  public $user;

  /**
   * Array of voperson_external_affilliation values.
   *
   * @var array
   */
  public $vea = [];

  /**
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   */
  public function __construct(UserInterface $user) {
    $this->user = $user;
    $field = $user->get(MyacademicidUserFields::FIELD_VEA);

    foreach ($field as $idx => $value) {
      $this->vea[] = $value;
    }
  }

}
