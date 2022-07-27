<?php

namespace Drupal\myacademicid_user_roles;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\Entity\Role;
use Drupal\user\UserInterface;
use Drupal\myacademicid_user_roles\Event\UserRoleChangeEvent;
use Drupal\myacademicid_user_roles\Event\SetUserRolesEvent;
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
    $old_roles = (empty($user->original)) ? NULL : $user->original
      ->getRoles(TRUE);
    $new_roles = $user
      ->getRoles(TRUE);

    if ($old_roles !== $new_roles) {
      // Instantiate our event.
      $event = new UserRoleChangeEvent($user);
      // Dispatch the event.
      $this->eventDispatcher
        ->dispatch($event, UserRoleChangeEvent::EVENT_NAME);
    }
  }

  /**
   * Determine roles to assign based on affilliation.
   *
   * @param \Drupal\user\UserInterface $user
   * @param array $vea
   */
  public function rolesFromAffilliation(UserInterface $user, array $vea) {
    $affilliation_mapping = $this->configFactory
      ->get('myacademicid_user_roles.affilliation_to_role')
      ->get('affilliation_mapping');

    $structure = [];

    // Gather all affilliation keys per schac_home_organization.
    foreach ($vea as $idx => $value) {
      $parts = \explode('@' ,$value->value);
      $key = $parts[0];
      $sho = $parts[1];

      if (\array_key_exists($sho, $structure)) {
        $structure[$sho][] = $key;
      }
      else {
        $structure[$sho] = [$key];
      }
    }

    $roles = [];

    // Gather all mapped roles from affilliation keys.
    foreach ($structure as $sho => $array) {
      foreach ($array as $idx => $key) {
        if (\array_key_exists($key, $affilliation_mapping)) {
          $roles[] = $affilliation_mapping[$key];
        }
      }
    }

    if (! empty($roles)) {
      // Instantiate our event.
      $event = new SetUserRolesEvent($user, $roles);
      // Dispatch the event.
      $this->eventDispatcher
        ->dispatch($event, SetUserRolesEvent::EVENT_NAME);
    }
  }

  /**
   * Set roles for a user entity.
   *
   * @param \Drupal\user\UserInterface $user
   * @param array $roles
   */
  public function setUserRoles(UserInterface $user, array $roles) {
    $affilliation_mapping = $this->configFactory
      ->get('myacademicid_user_roles.affilliation_to_role')
      ->get('affilliation_mapping');

    // Current status.
    $current = $user->getRoles(TRUE);

    // Roles to add.
    foreach ($roles as $idx => $rid) {
      if (! \in_array($rid, $current)) {
        $user->addRole($rid);
      }
    }

    // Roles to remove.
    foreach ($current as $idx => $rid) {
      if (! \in_array($rid, $roles) && \in_array($rid, $affilliation_mapping)) {
        $user->removeRole($rid);
      }
    }
  }

}
