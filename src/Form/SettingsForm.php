<?php

namespace Drupal\myacademicid_user_fields\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;

/**
 * Configure MyAcademicID user fields settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  const CLIENT_MODE = MyacademicidUserFields::CLIENT_MODE;
  const SERVER_MODE = MyacademicidUserFields::SERVER_MODE;

  const SERVER_SUBMODULE = 'myacademicid_user_claims';

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

    $moduleHandler = \Drupal::service('module_handler');

    $server_allowed = $moduleHandler
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
      ->t('To be used in combination with an OpenID Connect server module.');

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
