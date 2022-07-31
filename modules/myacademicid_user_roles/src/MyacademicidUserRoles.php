<?php

namespace Drupal\myacademicid_user_roles;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\Entity\Role;
use Drupal\user\UserInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Drupal\myacademicid_user_roles\Event\UserRolesChangeEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * MyAcademicID user roles service.
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
   * The MyAcademicID user fields service.
   *
   * @var \Drupal\myacademicid_user_fields\MyacademicidUserFields
   */
  protected $fieldsService;

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
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserFields $fields_service
   *   The MyAcademicID user fields service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EventDispatcherInterface $event_dispatcher,
    MyacademicidUserFields $fields_service,
    MessengerInterface $messenger,
    TranslationInterface $string_translation
  ) {
    $this->configFactory     = $config_factory;
    $this->eventDispatcher   = $event_dispatcher;
    $this->fieldsService     = $fields_service;
    $this->messenger         = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Check for changes in the user entity to dispatch events.
   *
   * @param \Drupal\user\UserInterface $user
   */
  public function checkRoleChange(UserInterface $user) {
    dpm(__METHOD__);
    if (! $this->equalRoles($user)) {
      // Instantiate our event.
      $event = new UserRolesChangeEvent($user);
      // Dispatch the event.
      $this->eventDispatcher
        ->dispatch($event, UserRolesChangeEvent::EVENT_NAME);
    }
  }

  /**
   * Check for equal roles on a user entity.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   *
   * @return boolean
   */
  public function equalRoles(UserInterface $user): bool {
    // Get the original user roles.
    $old_roles = (isset($user->original)) ?
      $user->original->getRoles(TRUE) : [];
    // Get the current user roles.
    $new_roles = $user->getRoles(TRUE);

    return ($old_roles === $new_roles);
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
    dpm(__METHOD__);
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
    dpm(__METHOD__);
    $affilliation_mapping = $this->configFactory
      ->get('myacademicid_user_roles.affilliation_to_role')
      ->get('affilliation_mapping');

    $sho = $this->flattenValue($user, MyacademicidUserFields::FIELD_SHO);

    $keys = [];

    // Gather all affilliation keys and schac_home_organization values.
    foreach ($vea as $idx => $item) {
      $parts = \explode('@' ,$item);

      if (\in_array($parts[1], $sho)) {
        $keys[] = $parts[0];
      }
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
    dpm(__METHOD__);
    $affilliation_mapping = $this->configFactory
      ->get('myacademicid_user_roles.affilliation_to_role')
      ->get('affilliation_mapping');

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

  /**
   * Get a flat array of field values from a user entity.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param string $field
   *   The field name.
   *
   * @return array $value
   *   Array of field values.
   */
  public function flattenValue(UserInterface $user, string $field): array {
    $obj = $user->get($field);
    $value = [];

    foreach ($obj as $key => $item) {
      $value[] = $item->value;
    }

    return $value;
  }

  /**
   * Get a flat array of role labels.
   *
   * @param array $roles
   *   Array of role keys.
   *
   * @return array $labels
   *   Array of role labels.
   */
  public function roleLabels(array $roles): array {
    $labels = [];

    foreach ($roles as $idx => $key) {
      $labels[] = Role::load($key)->label();
    }

    return $labels;
  }

}
