<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;

/**
 * Event that is fired when a user's voperson_external_affilliation must be set.
 */
class SetUserVopersonExternalAffilliationEvent extends Event {

  const EVENT_NAME = 'set_user_voperson_external_affilliation';

  /**
   * The user entity.
   *
   * @var \Drupal\user\UserInterface
   */
  public $user;

  /**
   * Array of voperson_external_affilliation values.
   *
   * @var array
   */
  public $vea;

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
   * @param array $vea
   *   Array of voperson_external_affilliation values.
   * @param boolean $save
   *   Whether the user entity should be saved after setting the value.
   */
  public function __construct(UserInterface $user, array $vea, $save = TRUE) {
    dpm(__METHOD__);
    $this->user = $user;
    $this->vea = $vea;
    $this->save = $save;
  }

}
