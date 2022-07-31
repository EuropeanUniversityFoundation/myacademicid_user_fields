<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

/**
 * Event that is fired when a user's schac_home_organization changes.
 */
class UserSchacHomeOrganizationChangeEvent extends Event {

  const EVENT_NAME = 'user_sho_change';

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
  public $sho = [];

  /**
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   */
  public function __construct(UserInterface $user) {
    $this->user = $user;

    $field = $this->user->get(MyacademicidUserFields::FIELD_SHO);

    foreach ($field as $idx => $item) {
      $this->sho[] = $item->value;
    }
  }

}
