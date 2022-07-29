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
   * Whether the user entity should be saved after setting the value.
   *
   * @var boolean
   */
  public $save;

  /**
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $sho
   *   Array of schac_home_organization values.
   * @param boolean $save
   *   Whether the user entity should be saved after setting the value.
   */
  public function __construct(UserInterface $user, array $sho, $save = TRUE) {
    $this->user = $user;
    $this->sho = $sho;
    $this->save = $save;
  }

}
