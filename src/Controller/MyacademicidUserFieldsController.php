<?php

namespace Drupal\myacademicid_user_fields\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserAffilliation;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for MyAcademicID user fields routes.
 */
class MyacademicidUserFieldsController extends ControllerBase {

  use StringTranslationTrait;

  /**
   * The affilliation service.
   */
  protected $affilliation;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    MyacademicidUserAffilliation $affilliation,
    TranslationInterface $string_translation
  ) {
    $this->affilliation = $affilliation;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('myacademicid_user_fields.affilliation'),
      $container->get('string_translation'),
    );
  }

  /**
   * Display all defined affilliation types.
   */
  public function affilliation() {
    $build['intro'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('These are all the defined affilliation types.'),
    ];

    $header = [
      $this->t('Source'),
      $this->t('Key'),
      $this->t('Label'),
      $this->t('Example claim'),
    ];

    $rows = [];

    $default_types = $this->affilliation->getDefaultTypes();
    $defined_types = $this->affilliation->getDefinedTypes();

    foreach ($defined_types as $key => $value) {
      if (\array_key_exists($key, $default_types)) {
        $overridden = ($value !== $default_types[$key]);
        $source = ($overridden) ? $this->t('Override') : $this->t('Default');
      }
      else {
        $source = $this->t('Config');
      }

      $rows[] = [
        $source,
        $key,
        $value,
        \implode('@', [$key, 'domain.tld']),
      ];
    }

    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('Nothing to display.'),
    ];

    return $build;
  }

}
