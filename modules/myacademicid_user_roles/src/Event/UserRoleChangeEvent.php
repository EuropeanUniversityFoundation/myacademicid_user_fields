<?php

namespace Drupal\myacademicid_user_roles\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\Entity\User;

/**
 * Event that is fired when a user's roles change.
 */
class UserRoleChangeEvent extends Event {

  const EVENT_NAME = 'user_role_change';

  /**
   * The user ID.
   *
   * @var string
   */
  public $uid;

  /**
   * The user's roles.
   *
   * @var array
   */
  public $roles;

  /**
   * Constructs the object.
   *
   * @param string $uid
   *   The user ID.
   */
  public function __construct(string $uid) {
    $this->uid = $uid;
    $user = User::load($uid);
    $this->roles = $user->getRoles(TRUE);
    unset($user);
  }

}
