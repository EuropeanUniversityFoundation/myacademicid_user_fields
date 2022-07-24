<?php

namespace Drupal\myacademicid_user_fields;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Provides affilliation types.
 *   @see https://wiki.refeds.org/display/STAN/eduPerson+2021-11#eduPerson202111-eduPersonAffiliation
 */
class MyacademicidUserAffilliation {

  use StringTranslationTrait;

  const MEMBER = 'member';
  const FACULTY = 'faculty';
  const STUDENT = 'student';
  const STAFF = 'staff';
  const ALUM = 'alum';
  const AFFILLIATE = 'affiliate';
  const EMPLOYEE = 'employee';
  const LIBRARY_WALK_IN = 'library-walk-in';

  /**
   * Default affilliation types.
   */
  protected $defaultTypes;

  /**
   * Additional affilliation types.
   */
  protected $additionalTypes;

  /**
   * All defined affilliation types.
   */
  protected $definedTypes;

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
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    TranslationInterface $string_translation
  ) {
    $this->configFactory = $config_factory;
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
      self::MEMBER => t('Member'),
      self::FACULTY => t('Faculty'),
      self::STUDENT => t('Student'),
      self::STAFF => t('Staff'),
      self::ALUM => t('Alum'),
      self::AFFILLIATE => t('Affiliate'),
      self::EMPLOYEE => t('Employee'),
      self::LIBRARY_WALK_IN => t('Library walk-in'),
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
   * Get list of additional affilliation types from config.
   *
   * @return array
   *   An array of affilliation key => affilliation label pairs.
   */
  public function getAdditionalTypes(): array {
    if (!isset($this->additionalTypes)) {
      $this->additionalTypes = [];

      $config = $this->configFactory->get('myacademicid_user_fields.settings');

      $additional = (array) $config->get('additional');

      foreach ($additional as $idx => $value) {
        $pair = \explode('|', $value, 2);
        $key = $pair[0];
        $label = (count($pair) === 2) ? $pair[1] : $key;

        $this->additionalTypes[$key] = $label;
      }
    }

    return $this->additionalTypes;
  }

  /**
   * Get list of all defined affilliation types.
   *
   * @return array
   *   An array of affilliation key => affilliation label pairs.
   */
  public function getDefinedTypes(): array {
    if (!isset($this->definedTypes)) {
      $default = $this->getDefaultTypes();
      $additional = $this->getAdditionalTypes();

      $this->definedTypes = \array_merge($default, $additional);
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
