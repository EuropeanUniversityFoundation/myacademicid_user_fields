<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user's voperson_external_affilliation must be set.
 */
class SetUserVopersonExternalAffilliationEvent extends Event {

  const EVENT_NAME = 'set_user_voperson_external_affilliation';

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
  public $vea;

  /**
   * Constructs the object.
   *
   * @param string $uid
   *   The user ID.
   * @param array $vea
   *   Array of voperson_external_affilliation values.
   */
  public function __construct(string $uid, array $vea) {
    $this->uid = $uid;
    $this->vea = $vea;
  }

}
