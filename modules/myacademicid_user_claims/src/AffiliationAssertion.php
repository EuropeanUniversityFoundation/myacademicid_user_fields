<?php

namespace Drupal\myacademicid_user_claims;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\UserInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserAffiliation;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

/**
 * Affiliation assertion service.
 */
class AffiliationAssertion {

  use StringTranslationTrait;

  const FIELD_CLAIMS = [
    MyacademicidUserFields::FIELD_SHO => MyacademicidUserFields::CLAIM_SHO,
    MyacademicidUserFields::FIELD_SPUC => MyacademicidUserFields::CLAIM_SPUC,
    MyacademicidUserFields::FIELD_VEA => MyacademicidUserFields::CLAIM_VEA,
  ];

  const ASSERT_MEMBER = [
    MyacademicidUserAffiliation::FACULTY,
    MyacademicidUserAffiliation::STUDENT,
    MyacademicidUserAffiliation::STAFF,
    MyacademicidUserAffiliation::EMPLOYEE,
  ];

  /**
   * The affiliation service.
   */
  protected $affiliation;

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
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserAffiliation $affiliation
   *   The affiliation service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    MyacademicidUserAffiliation $affiliation,
    TranslationInterface $string_translation
  ) {
    $this->configFactory     = $config_factory;
    $this->affiliation       = $affiliation;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Member affiliation assertions defined in the system.
   *
   * @return array $assertions
   */
  public function getAssertions(): array {
    $assertions = self::ASSERT_MEMBER;

    $config = $this->configFactory->get('myacademicid_user_claims.settings');

    $additional_assertions = (array) $config->get('assert_member');

    foreach ($additional_assertions as $idx => $value) {
      $assertions[] = $value;
    }

    return $assertions;
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

    if (\array_key_exists(MyacademicidUserFields::CLAIM_VEA, $claims)) {
      $this->consolidateAffiliation($claims);
    }

    return $claims;
  }

  /**
   * Consolidate the affiliation claims.
   */
  private function consolidateAffiliation(array &$claims) {
    $assertions = $this->getAssertions();

    $structure = [];

    // Gather all affiliation keys per schac_home_organization.
    foreach ($claims[MyacademicidUserFields::CLAIM_VEA] as $idx => $value) {
      $parts = \explode('@' ,$value);
      $key = $parts[0];
      $sho = $parts[1];

      if (\array_key_exists($sho, $structure)) {
        $structure[$sho][] = $key;
      }
      else {
        $structure[$sho] = [$key];
      }
    }

    // Check whether the member affiliation needs to be asserted and added.
    foreach ($structure as $sho => $keys) {
      if (
        ! empty(\array_intersect($assertions, $keys)) &&
        ! \in_array(MyacademicidUserAffiliation::MEMBER, $keys)
      ) {
        $member = \implode('@', [MyacademicidUserAffiliation::MEMBER, $sho]);
        $claims[MyacademicidUserFields::CLAIM_VEA][] = $member;
      }
    }

    // Reduce the claims to unique values.
    $redux = $claims[MyacademicidUserFields::CLAIM_VEA];
    $redux = \array_values(\array_unique($redux));
    $claims[MyacademicidUserFields::CLAIM_VEA] = $redux;
  }

}
