<?php

namespace Drupal\myacademicid_user_claims;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\UserInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserAffilliation;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

/**
 * Affilliation assertion service.
 */
class AffilliationAssertion {

  use StringTranslationTrait;

  const FIELD_CLAIMS = [
    MyacademicidUserFields::FIELD_SHO => MyacademicidUserFields::CLAIM_SHO,
    MyacademicidUserFields::FIELD_SPUC => MyacademicidUserFields::CLAIM_SPUC,
    MyacademicidUserFields::FIELD_VEA => MyacademicidUserFields::CLAIM_VEA,
  ];

  const ASSERT_MEMBER = [
    MyacademicidUserAffilliation::FACULTY,
    MyacademicidUserAffilliation::STUDENT,
    MyacademicidUserAffilliation::STAFF,
    MyacademicidUserAffilliation::EMPLOYEE,
  ];

  /**
   * The affilliation service.
   */
  protected $affilliation;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserAffilliation $affilliation
   *   The affilliation service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    MyacademicidUserAffilliation $affilliation,
    TranslationInterface $string_translation
  ) {
    $this->configFactory     = $config_factory;
    $this->affilliation      = $affilliation;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Convert user fields to claims.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $requested_scopes
   *   An array of requestes scopes.
   *
   * @return array
   *   An array of additional claims.
   */
  public function userClaims(UserInterface $user, array $requested_scopes): array {
    $claims = [];

    foreach (self::FIELD_CLAIMS as $field => $claim) {
      // MyAcademicID scopes and claims have the same names.
      if (in_array($claim, $requested_scopes)) {
        // Check for the first value.
        if (!empty($user->get($field)->value)) {
          // Build an array with all values.
          $field_values = [];

          foreach ($user->get($field) as $item) {
            $field_values[] = $item->value;
          }

          $claims[$claim] = $field_values;
        }
      }
    }

    return $claims;
  }

}
