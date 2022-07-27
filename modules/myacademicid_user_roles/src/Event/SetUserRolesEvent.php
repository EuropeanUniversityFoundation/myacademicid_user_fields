<?php

namespace Drupal\myacademicid_user_roles\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\RoleInterface;
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
   * @var \Drupal\user\RoleInterface[]
   */
  public $roles;

  /**
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $roles
   *   Array of user roles.
   */
  public function __construct(UserInterface $user, array $roles) {
    $this->user = $user;
    $this->roles = $roles;
  }

}
