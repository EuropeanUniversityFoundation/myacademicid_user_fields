<?php

namespace Drupal\myacademicid_user_roles\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\RoleInterface;

/**
 * Event that is fired when a user's roles must be set.
 */
class SetUserRolesEvent extends Event {

  const EVENT_NAME = 'set_user_roles';

  /**
   * The user ID.
   *
   * @var string
   */
  public $uid;

  /**
   * Array of user roles.
   *
   * @var \Drupal\user\RoleInterface[]
   */
  public $roles;

  /**
   * Constructs the object.
   *
   * @param string $uid
   *   The user ID.
   * @param array $roles
   *   Array of user roles.
   */
  public function __construct(string $uid, array $roles) {
    $this->uid = $uid;
    $this->roles = $roles;
  }

}
