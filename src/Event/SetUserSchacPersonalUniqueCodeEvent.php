<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user's schac_personal_unique_code must be set.
 */
class SetUserSchacPersonalUniqueCodeEvent extends Event {

  const EVENT_NAME = 'set_user_schac_personal_unique_code';

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
  public $spuc;

  /**
   * Constructs the object.
   *
   * @param string $uid
   *   The user ID.
   * @param array $spuc
   *   Array of schac_personal_unique_code values.
   */
  public function __construct(string $uid, array $sho) {
    $this->uid = $uid;
    $this->spuc = $spuc;
  }

}
