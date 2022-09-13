<?php

namespace Drupal\myacademicid_user_fields;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserAffilliation;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

/**
 * MyAcademicID user fields form alter service.
 */
class MyacademicidUserFieldsFormAlter {

  use StringTranslationTrait;

  const ADMIN_PERMISSION = 'administer myacademicid user fields';
  const BASE_FIELDS = [
    MyacademicidUserFields::FIELD_SHO,
    MyacademicidUserFields::FIELD_SPUC,
    MyacademicidUserFields::FIELD_VEA
  ];
  const WRAPPER = 'myacademicid_user_fields_wrapper';

  /**
   * The current user.
   */
  protected $currentUser;

  /**
   * The affilliation service.
   */
  protected $affilliation;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   A proxied implementation of AccountInterface.
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserAffilliation $affilliation
   *   The affilliation service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    AccountProxy $current_user,
    MyacademicidUserAffilliation $affilliation,
    TranslationInterface $string_translation
  ) {
    $this->currentUser       = $current_user;
    $this->affilliation      = $affilliation;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Alter the user form element according to permissions.
   *
   * @param array $form
   * @param Drupal\Core\Form\FormStateInterface $form_state
   */
  public function userFormAlter(&$form, FormStateInterface $form_state) {
    if (!$this->currentUser->isAnonymous()) {
      // Determine whether the current user is allowed to set the value.
      $allowed = ($this->currentUser
          ->hasPermission(self::ADMIN_PERMISSION, $this->currentUser));

      $form[self::WRAPPER] = [
        '#type' => 'details',
        '#title' => $this->t('MyAcademicID user fields'),
        '#weight' => 100,
        '#open' => $allowed
      ];

      foreach (self::BASE_FIELDS as $idx => $field) {
        // If the base field is in the user form, changes may be needed,
        if (\array_key_exists($field, $form)) {
          // If not allowed, the form element must be replaced with text.
          if (! $allowed) {
            $empty = '<em>' . $this->t('Field is not set.') . '</em>';
            $list = '';

            foreach ($form[$field]['widget'] as $key => $value) {
              if (\is_numeric($key)) {
                $default_value = $value['value']['#default_value'];

                if (!empty($default_value)) {
                  $list .= '<li><code>' . $default_value . '</code></li>';
                }
              }
            }

            $markup = (empty($list)) ? $empty : '<ul>' . $list . '</ul>';

            // Build the new form element.
            $new_element = [
              '#type' => 'item',
              '#title' => $form[$field]['widget']['#title'],
              '#markup' => $markup,
            ];

            $form[$field] = $new_element;
          }

          $form[self::WRAPPER][$field] = $form[$field];
          unset($form[$field]);
        }
      }
    }
    else {
      // Hide the fields in the registration form to avoid errors.
      foreach (self::BASE_FIELDS as $idx => $field) {
        $form[$field]['#type'] = 'hidden';
      }
    }
  }

}
