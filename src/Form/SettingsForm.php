<?php

namespace Drupal\myacademicid_user_fields\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure MyAcademicID user fields settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  const CLIENT_MODE = MyacademicidUserFields::CLIENT_MODE;
  const SERVER_MODE = MyacademicidUserFields::SERVER_MODE;

  const SERVER_SUBMODULE = 'myacademicid_user_claims';

  use StringTranslationTrait;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The affilliation service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    ModuleHandlerInterface $module_handler,
    TranslationInterface $string_translation
  ) {
    parent::__construct($config_factory);
    $this->moduleHandler     = $module_handler;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('string_translation'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myacademicid_user_fields_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['myacademicid_user_fields.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('myacademicid_user_fields.settings');

    $modes = [
      self::CLIENT_MODE => $this->t('Client mode'),
      self::SERVER_MODE => $this->t('Server mode'),
    ];

    $current_mode = $config->get('mode') ?? self::CLIENT_MODE;

    $server_allowed = $this->moduleHandler
      ->moduleExists(self::SERVER_SUBMODULE);

    $form['mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Mode of operation'),
      '#options' => $modes,
      '#default_value' => ($server_allowed) ? $current_mode : self::CLIENT_MODE,
    ];

    $form['mode'][self::CLIENT_MODE]['#description'] = $this
      ->t('Default mode; covers the most common use cases.');

    $form['mode'][self::SERVER_MODE]['#description'] = $this
      ->t('To be used in combination with an OAuth2 server module.');

    if (! $server_allowed) {
      $form['mode'][self::SERVER_MODE]['#description'] = $this
        ->t('Requires the %module module to be enabled.', [
          '%module' => $moduleHandler->getName(self::SERVER_SUBMODULE),
        ]);

      $form['mode'][self::SERVER_MODE]['#disabled'] = TRUE;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('myacademicid_user_fields.settings');

    $config->set('mode', $form_state->getValue('mode'));

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
