<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\Entity\User;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

/**
 * Event that is fired when a user's voperson_external_affilliation changes.
 */
class UserVopersonExternalAffilliationChangeEvent extends Event {

  const EVENT_NAME = 'user_vea_change';

  /**
   * The user ID.
   *
   * @var string
   */
  public $uid;

  /**
   * Array of voperson_external_affilliation values.
   *
   * @var array
   */
  public $vea = [];

  /**
   * Constructs the object.
   *
   * @param string $uid
   *   The user ID.
   */
  public function __construct(string $uid) {
    $this->uid = $uid;
    $user = User::load($uid);
    $field = $user->get(MyacademicidUserFields::FIELD_VEA);
    unset($user);

    foreach ($field as $idx => $value) {
      $this->vea[] = $value;
    }
  }

}
