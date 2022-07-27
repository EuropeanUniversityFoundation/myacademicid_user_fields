<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;

/**
 * Event that is fired when a user's schac_personal_unique_code must be set.
 */
class SetUserSchacPersonalUniqueCodeEvent extends Event {

  const EVENT_NAME = 'set_user_schac_personal_unique_code';

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
  public $spuc;

  /**
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $spuc
   *   Array of schac_personal_unique_code values.
   */
  public function __construct(UserInterface $user, array $sho) {
    $this->user = $user;
    $this->spuc = $spuc;
  }

}
