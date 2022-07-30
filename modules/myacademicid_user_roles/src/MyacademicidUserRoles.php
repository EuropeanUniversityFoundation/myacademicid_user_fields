<?php

namespace Drupal\myacademicid_user_roles;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\UserInterface;
use Drupal\myacademicid_user_roles\Event\UserRolesChangeEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * MyAcademicID User Roles service.
 */
class MyacademicidUserRoles {

  use StringTranslationTrait;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EventDispatcherInterface $event_dispatcher,
    MessengerInterface $messenger,
    TranslationInterface $string_translation
  ) {
    $this->configFactory     = $config_factory;
    $this->eventDispatcher   = $event_dispatcher;
    $this->messenger         = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Check for changes in the user entity to dispatch events.
   *
   * @param \Drupal\user\UserInterface $user
   */
  public function checkRoleChange(UserInterface $user) {
    // Get the original user roles.
    $roles = (isset($user->original)) ? $user->original->getRoles(TRUE) : [];

    if (sort($roles) !== sort($user->getRoles(TRUE))) {
      // Instantiate our event.
      $event = new UserRolesChangeEvent($user);
      // Dispatch the event.
      $this->eventDispatcher
        ->dispatch($event, UserRolesChangeEvent::EVENT_NAME);
    }
  }

  /**
   * Determine affilliation to assign based on roles and home organization.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $roles
   *   The user roles.
   * @param array $sho
   *   Array of schac_home_organization values.
   */
  public function affilliationfromRoles(UserInterface $user, array $roles, array $sho): array {
    $role_mapping = $this->configFactory
      ->get('myacademicid_user_roles.role_to_affilliation')
      ->get('role_mapping');

    $vea = [];

    foreach ($roles as $idx => $rid) {
      if (
        \array_key_exists($rid, $role_mapping) &&
        ! empty($role_mapping[$rid])
      ) {
        $key = $role_mapping[$rid];

        foreach ($sho as $idx => $value) {
          $vea[] = \implode('@', [$key, $value]);
        }
      }
    }

    return $vea;
  }

  /**
   * Determine roles to assign based on affilliation.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $vea
   *   The voperson_external_affilliation values.
   *
   * @return array $roles
   *   Array of user roles.
   */
  public function rolesFromAffilliation(UserInterface $user, array $vea): array {
    $affilliation_mapping = $this->configFactory
      ->get('myacademicid_user_roles.affilliation_to_role')
      ->get('affilliation_mapping');

    $keys = [];
    $sho = [];

    // Gather all affilliation keys and schac_home_organization values.
    foreach ($vea as $idx => $item) {
      $parts = \explode('@' ,$item);
      $keys[] = $parts[0];
      $sho[] = $parts[1];
    }

    $roles = [];

    // Gather all mapped roles from affilliation keys.
    foreach ($keys as $idx => $key) {
      if (
        \array_key_exists($key, $affilliation_mapping) &&
        ! empty($affilliation_mapping[$key])
      ) {
        $roles[] = $affilliation_mapping[$key];
      }
    }

    return $roles;
  }

  /**
   * Set roles on a user entity.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $roles
   *   The roles to be set on the user entity.
   * @param boolean $save
   *   Whether the user entity should be saved after setting the value.
   */
  public function setUserRoles(UserInterface $user, array $roles, $save = TRUE) {
    $current = $user->getRoles(TRUE);

    // Roles to add.
    foreach ($roles as $idx => $rid) {
      if (! \in_array($rid, $current)) {
        $user->addRole($rid);
      }
    }

    // Roles to remove.
    foreach ($current as $idx => $rid) {
      if (
        ! \in_array($rid, $roles) &&
        \in_array($rid, $affilliation_mapping)
      ) {
        $user->removeRole($rid);
      }
    }

    if ($save) {
      $user->save();
    }
  }

}
