<?php

namespace Drupal\myacademicid_user_fields\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;

/**
 * Event that is fired when a user's voperson_external_affiliation must be set.
 */
class SetUserVopersonExternalAffiliationEvent extends Event {

  const EVENT_NAME = 'set_user_voperson_external_affiliation';

  /**
   * The user entity.
   *
   * @var \Drupal\user\UserInterface
   */
  public $user;

  /**
   * Array of voperson_external_affiliation values.
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
   *   Array of voperson_external_affiliation values.
   * @param boolean $save
   *   Whether the user entity should be saved after setting the value.
   */
  public function __construct(UserInterface $user, array $vea, $save = TRUE) {
    $this->user = $user;
    $this->vea = $vea;
    $this->save = $save;
  }

}
