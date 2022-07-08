<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;

/**
 * Event that is fired when a user's schac_home_organization must be set.
 */
class SetUserSchacHomeOrganizationEvent extends Event {

  const EVENT_NAME = 'set_user_schac_home_organization';

  /**
   * The user entity.
   *
   * @var \Drupal\user\UserInterface
   */
  public $user;

  /**
   * Array of schac_home_organization values.
   *
   * @var array
   */
  public $sho;

  /**
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $sho
   *   Array of schac_home_organization values.
   */
  public function __construct(UserInterface $user, array $sho) {
    $this->user = $user;
    $this->sho = $sho;
  }

}
