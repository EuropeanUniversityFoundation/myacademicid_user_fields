<?php

namespace Drupal\myacademicid_user_roles\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;

/**
 * Event that is fired when a user's roles change.
 */
class UserRolesChangeEvent extends Event {

  const EVENT_NAME = 'user_roles_change';

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
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   */
  public function __construct(UserInterface $user) {
    dpm(__METHOD__);
    $this->user = $user;
    $this->roles = $user->getRoles(TRUE);
  }

}
