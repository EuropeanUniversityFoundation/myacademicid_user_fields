<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

/**
 * Event that is fired when a user's schac_personal_unique_code changes.
 */
class UserSchacPersonalUniqueCodeChangeEvent extends Event {

  const EVENT_NAME = 'user_spuc_change';

  /**
   * The user entity.
   *
   * @var \Drupal\user\UserInterface
   */
  public $user;

  /**
   * Array of schac_personal_unique_code values.
   *
   * @var array
   */
  public $spuc = [];

  /**
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   */
  public function __construct(UserInterface $user) {
    dpm(__METHOD__);
    $this->user = $user;

    $field = $this->user->get(MyacademicidUserFields::FIELD_SPUC);

    foreach ($field as $idx => $item) {
      $this->spuc[] = $item->value;
    }
  }

}
