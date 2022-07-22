<?php

namespace Drupal\myacademicid_user_fields;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Provides affilliation types.
 *   @see https://wiki.refeds.org/display/STAN/eduPerson+2021-11#eduPerson202111-eduPersonAffiliation
 */
class MyacademicidUserAffilliation {

  use StringTranslationTrait;

  /**
   * Default affilliation types.
   */
  protected $defaultTypes;

  /**
   * All defined affilliation types.
   */
  protected $definedTypes;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    TranslationInterface $string_translation
  ) {
    $this->stringTranslation = $string_translation;
  }

  /**
   * Curated list of default affilliation types.
   *
   * @return array
   *   An array of affilliation key => affilliation label pairs.
   */
  public static function defaultTypes() {
    $default_types = [
      'faculty' => t('Faculty'),
      'student' => t('Student'),
      'staff' => t('Staff'),
      'alum' => t('Alum'),
      'member' => t('Member'),
      'affiliate' => t('Affiliate'),
      'employee' => t('Employee'),
      'library-walk-in' => t('Library walk-in'),
    ];

    return $default_types;
  }

  /**
   * Get list of default affilliation types.
   *
   * @return array
   *   An array of affilliation key => affilliation label pairs.
   */
  public function getDefaultTypes(): array {
    if (!isset($this->defaultTypes)) {
      $this->defaultTypes = static::defaultTypes();
    }

    return $this->defaultTypes;
  }

  /**
   * Get list of all defined affilliation types.
   *
   * @return array
   *   An array of affilliation key => affilliation label pairs.
   */
  public function getDefinedTypes(): array {
    if (!isset($this->definedTypes)) {
      $this->definedTypes = $this->getDefaultTypes();
    }

    return $this->definedTypes;
  }

  /**
   * Get an array of affilliation types as options.
   *
   * @return array
   *   An array of affilliation key => affilliation label pairs.
   */
  public function getOptions(): array {
    // Build a list from the defined types.
    $options = $this->getDefinedTypes();

    return $options;
  }

}
