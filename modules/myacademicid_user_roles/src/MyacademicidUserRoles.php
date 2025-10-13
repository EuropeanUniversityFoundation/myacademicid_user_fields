<?php

namespace Drupal\myacademicid_user_roles;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
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
    EntityTypeManagerInterface $entity_type_manager,
    EventDispatcherInterface $event_dispatcher,
    MyacademicidUserFields $fields_service,
    MessengerInterface $messenger,
    TranslationInterface $string_translation,
  ) {
    $this->configFactory     = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->eventDispatcher   = $event_dispatcher;
    $this->fieldsService     = $fields_service;
    $this->messenger         = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Check for changes in the user entity to dispatch events.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   */
  public function checkRoleChange(UserInterface $user) {
    if (!$this->equalRoles($user)) {
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
   * @return bool
   *   Whether the roles are equal.
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
   * Determine affiliation to assign based on roles and home organization.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $roles
   *   The user roles.
   * @param array $sho
   *   Array of schac_home_organization values.
   */
  public function affiliationfromRoles(UserInterface $user, array $roles, array $sho): array {
    $role_mapping = $this->configFactory
      ->get('myacademicid_user_roles.role_to_affiliation')
      ->get('role_mapping');

    $vea = [];

    foreach ($roles as $rid) {
      if (
        \array_key_exists($rid, $role_mapping) &&
        !empty($role_mapping[$rid])
      ) {
        $key = $role_mapping[$rid];

        foreach ($sho as $value) {
          $vea[] = \implode('@', [$key, $value]);
        }
      }
    }

    return $vea;
  }

  /**
   * Determine roles to assign based on affiliation.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $vea
   *   The voperson_external_affiliation values.
   *
   * @return array
   *   Array of user roles.
   */
  public function rolesFromAffiliation(UserInterface $user, array $vea): array {
    $affiliation_mapping = $this->configFactory
      ->get('myacademicid_user_roles.affiliation_to_role')
      ->get('affiliation_mapping');

    $sho = $this->flattenValue($user, MyacademicidUserFields::FIELD_SHO);

    $keys = [];

    // Gather all affiliation keys and schac_home_organization values.
    foreach ($vea as $item) {
      $parts = \explode('@', $item);

      if (\in_array($parts[1], $sho)) {
        $keys[] = $parts[0];
      }
    }

    $roles = [];

    // Gather all mapped roles from affiliation keys.
    foreach ($keys as $key) {
      if (
        \array_key_exists($key, $affiliation_mapping) &&
        !empty($affiliation_mapping[$key])
      ) {
        $roles[] = $affiliation_mapping[$key];
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
   * @param bool $save
   *   Whether the user entity should be saved after setting the value.
   */
  public function setUserRoles(UserInterface $user, array $roles, $save = TRUE) {
    $affiliation_mapping = $this->configFactory
      ->get('myacademicid_user_roles.affiliation_to_role')
      ->get('affiliation_mapping');

    $current = $user->getRoles(TRUE);

    // Roles to add.
    foreach ($roles as $rid) {
      if (!\in_array($rid, $current)) {
        $user->addRole($rid);
      }
    }

    // Roles to remove.
    foreach ($current as $rid) {
      if (
        !\in_array($rid, $roles) &&
        \in_array($rid, $affiliation_mapping)
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
   * @return array
   *   Array of field values.
   */
  public function flattenValue(UserInterface $user, string $field): array {
    $obj = $user->get($field);
    $value = [];

    foreach ($obj as $item) {
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
   * @return array
   *   Array of role labels.
   */
  public function roleLabels(array $roles): array {
    $storage = $this->entityTypeManager->getStorage('user_role');
    $labels = [];

    foreach ($roles as $role) {
      $labels[] = $storage->load($role)->label();
    }

    return $labels;
  }

}
