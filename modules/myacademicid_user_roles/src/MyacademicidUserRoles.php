<?php

namespace Drupal\myacademicid_user_roles;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\UserInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

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
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    MessengerInterface $messenger,
    TranslationInterface $string_translation
  ) {
    $this->configFactory     = $config_factory;
    $this->messenger         = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Check for changes in the user entity to dispatch events.
   *
   * @param \Drupal\user\UserInterface $user
   */
  public function checkRoleChange(UserInterface $user) {
    $mode = $this->configFactory
      ->get('myacademicid_user_fields.settings')
      ->get('mode');

    switch ($mode) {
      case MyacademicidUserFields::SERVER_MODE:
        // Check for differences in user roles.
        $old_roles = (empty($user->original)) ? NULL : $user->original
          ->getRoles(TRUE);
        $new_roles = $user
          ->getRoles(TRUE);

        // Check for differences in schac_home_organization.
        $old_sho = (empty($user->original)) ? NULL : $user->original
          ->get(MyacademicidUserFields::FIELD_SHO);
        $new_sho = $user
          ->get(MyacademicidUserFields::FIELD_SHO);

        // Either difference calls for a recalculation of affilliation.
        if ($old_roles !== $new_roles || $old_sho !== $new_sho) {
          $sho = [];

          foreach ($new_sho as $idx => $item) {
            $sho[] = $item->value;
          }

          $this->affilliationFromRoles($user, $new_roles, $sho);
        }

        break;

      default:
        // Check for differences in voperson_external_affilliation.
        $old_vea = (empty($user->original)) ? NULL : $user->original
          ->get(MyacademicidUserFields::FIELD_VEA);
        $new_vea = $user
          ->get(MyacademicidUserFields::FIELD_VEA);

        // A difference calls for a recalculation of user roles.
        if ($old_vea !== $new_vea) {
          $vea = [];

          foreach ($new_vea as $idx => $item) {
            $vea[] = $item->value;
          }

          $this->rolesFromAffilliation($user, $vea);
        }

        break;
    }
  }

  /**
   * Determine affilliation to assign based on roles and home organization.
   *
   * @param \Drupal\user\UserInterface $user
   * @param array $roles
   * @param array $sho
   */
  public function affilliationfromRoles(UserInterface $user, array $roles, array $sho) {
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

    if (! empty($vea)) {
      $original = $user->get(MyacademicidUserFields::FIELD_VEA)->getValue();

      $user->set(MyacademicidUserFields::FIELD_VEA, $vea);

      $user->_skipProtectedUserFieldConstraint = TRUE;
      $violations = $user->validate();

      if ($violations->count() > 0) {
        foreach ($violations as $idx => $violation) {
          $this->messenger->addError($violation->getMessage());
        }

        $this->messenger
          ->addError($this->t('Cannot set %claim claim to %value', [
            '%claim' => $claim,
            '%value' => \implode(', ', $value)
          ]));

        $user->set(MyacademicidUserFields::FIELD_VEA, $original);
      }
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
  }

}
