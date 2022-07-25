<?php

namespace Drupal\myacademicid_user_roles\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;

/**
 * Event that is fired when a user's roles change.
 */
class UserRoleChangeEvent extends Event {

  const EVENT_NAME = 'user_role_change';

  /**
   * The user entity.
   *
   * @var \Drupal\user\UserInterface
   */
  public $user;

  /**
   * The user's roles.
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
    $this->user = $user;

    $this->roles = $this->user->getRoles(TRUE);
  }

}
