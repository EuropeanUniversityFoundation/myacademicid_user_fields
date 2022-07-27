<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\Entity\User;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

/**
 * Event that is fired when a user's schac_personal_unique_code changes.
 */
class UserSchacPersonalUniqueCodeChangeEvent extends Event {

  const EVENT_NAME = 'user_spuc_change';

  /**
   * The user ID.
   *
   * @var string
   */
  public $uid;

  /**
   * Array of schac_personal_unique_code values.
   *
   * @var array
   */
  public $spuc = [];

  /**
   * Constructs the object.
   *
   * @param string $uid
   *   The user ID.
   */
  public function __construct(string $uid) {
    $this->uid = $uid;
    $user = User::load($uid);
    $field = $user->get(MyacademicidUserFields::FIELD_SPUC);
    unset($user);

    foreach ($field as $idx => $value) {
      $this->spuc[] = $value;
    }
  }

}
