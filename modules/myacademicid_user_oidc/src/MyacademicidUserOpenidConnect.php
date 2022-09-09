<?php

namespace Drupal\myacademicid_user_oidc;

use Drupal\user\UserInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

/**
 * MyAcademicID user OpenID Connect service.
 */
class MyacademicidUserOpenidConnect {

  const MAPPING = [
    MyacademicidUserFields::CLAIM_SHO => MyacademicidUserFields::FIELD_SHO,
    MyacademicidUserFields::CLAIM_SPUC => MyacademicidUserFields::FIELD_SPUC,
    MyacademicidUserFields::CLAIM_VEA => MyacademicidUserFields::FIELD_VEA,
  ];

  /**
   * Modify the list of claims.
   *
   * @param array $claims
   *   A array of claims.
   *
   * @ingroup openid_connect_api
   */
  public function claimsAlter(array &$claims) {
    foreach (self::MAPPING as $claim => $field) {
      $claims[$claim] = [
        'scope' => $claim,
        'title' => $claim,
        'type' => 'array',
        'description' => $claim,
      ];
    }
  }

  /**
   * Save userinfo hook.
   *
   * This hook runs after the claim mappings have been applied by the OpenID
   * Connect module, but before the account will be saved.
   *
   * A popular use case for this hook is mapping additional information like
   * user roles or other complex claims provided by the identity provider, that
   * the OpenID Connect module has no mapping mechanisms for.
   *
   * @param \Drupal\user\UserInterface $account
   *   A user account object.
   * @param array $context
   *   An associative array with context information:
   *   - tokens:         Array of original tokens.
   *   - user_data:      Array of user and session data from the ID token.
   *   - userinfo:       Array of user information from the userinfo endpoint.
   *   - plugin_id:      The plugin identifier.
   *   - sub:            The remote user identifier.
   *   - is_new:         Whether the account was created during authorization.
   *
   * @ingroup openid_connect_api
   */
  public function userinfoSave(UserInterface $account, array $context) {
    // Update only when the required information is available.
    foreach (self::MAPPING as $claim => $field) {
      if (!empty($context['userinfo'][$claim])) {
        $list = $context['userinfo'][$claim];
        $values = [];
        foreach ($list as $value) {
          $values[] = $value;
        }
        $account->set($field, $values);
      }
    }
  }

}
