<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\Entity\User;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

/**
 * Event that is fired when a user's schac_home_organization changes.
 */
class UserSchacHomeOrganizationChangeEvent extends Event {

  const EVENT_NAME = 'user_sho_change';

  /**
   * The user ID.
   *
   * @var string
   */
  public $uid;

  /**
   * Array of schac_home_organization values.
   *
   * @var array
   */
  public $sho = [];

  /**
   * Constructs the object.
   *
   * @param string $uid
   *   The user ID.
   */
  public function __construct(string $uid) {
    $this->uid = $uid;
    $user = User::load($uid);
    $field = $user->get(MyacademicidUserFields::FIELD_SHO);
    unset($user);

    foreach ($field as $idx => $value) {
      $this->sho[] = $value;
    }
  }

}
