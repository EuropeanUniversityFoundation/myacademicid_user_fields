<?php

namespace Drupal\myacademicid_user_roles\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;

/**
 * Event that is fired when a user's roles must be set.
 */
class SetUserRolesEvent extends Event {

  const EVENT_NAME = 'set_user_roles';

  /**
   * The user entity.
   *
   * @var \Drupal\user\UserInterface
   */
  public $user;

  /**
   * Array of user roles.
   *
   * @var array
   */
  public $roles;

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
   * @param array $roles
   *   Array of user roles.
   * @param boolean $save
   *   Whether the user entity should be saved after setting the value.
   */
  public function __construct(UserInterface $user, array $roles, $save = TRUE) {
    dpm(__METHOD__);
    $this->user = $user;
    $this->roles = $roles;
    $this->save = $save;
  }

}
