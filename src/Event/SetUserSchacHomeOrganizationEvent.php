<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user's schac_home_organization must be set.
 */
class SetUserSchacHomeOrganizationEvent extends Event {

  const EVENT_NAME = 'set_user_schac_home_organization';

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
  public $sho;

  /**
   * Constructs the object.
   *
   * @param string $uid
   *   The user ID.
   * @param array $sho
   *   Array of schac_home_organization values.
   */
  public function __construct(string $uid, array $sho) {
    $this->uid = $uid;
    $this->sho = $sho;
  }

}
