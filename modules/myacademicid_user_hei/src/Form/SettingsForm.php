<?php

namespace Drupal\myacademicid_user_hei\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Drupal\myacademicid_user_hei\MyacademicidUserHei;

/**
 * Configure MyAcademicID user institution settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  const KEEP_IN_SYNC = MyacademicidUserHei::KEEP_IN_SYNC;
  const SYNC_IF_EMPTY = MyacademicidUserHei::SYNC_IF_EMPTY;
  const DO_NOT_SYNC = MyacademicidUserHei::DO_NOT_SYNC;

  const CLAIM_SHO = MyacademicidUserFields::CLAIM_SHO;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myacademicid_user_hei_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['myacademicid_user_hei.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('myacademicid_user_hei.settings');

    $sync_modes = [
      self::KEEP_IN_SYNC => $this
        ->t('Keep user\'s Institution and %sho claim in sync.', [
          '%sho' => self::CLAIM_SHO,
        ]),
      self::SYNC_IF_EMPTY => $this
        ->t('Sync user\'s Institution with %sho claim @condition.', [
          '%sho' => self::CLAIM_SHO,
          '@condition' => $this->t('only when Institution is empty')
        ]),
      self::DO_NOT_SYNC => $this
        ->t('Do not sync user\'s Institution with %sho claim.', [
          '%sho' => self::CLAIM_SHO,
        ]),
    ];

    $form['sync_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Synchronization mode'),
      '#options' => $sync_modes,
      '#default_value' => $config->get('sync_mode') ?? self::DO_NOT_SYNC,
    ];

    $form['sync_mode'][self::KEEP_IN_SYNC]['#description'] = $this
      ->t('This will automatically revert any manual changes.');

    $form['sync_mode'][self::SYNC_IF_EMPTY]['#description'] = $this
      ->t('This will only have an effect when operating in %mode.', [
        '%mode' => $this->t('Client mode')
      ]);

    $form['import'] = [
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Lookup and import Institution when it does not exist.'),
      '#default_value' => $config->get('import') ?? FALSE,
      '#return_value' => TRUE,
    ];

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
    $config = $this->config('myacademicid_user_hei.settings');

    $config->set('sync_mode', $form_state->getValue('sync_mode'));
    $config->set('import', (bool) $form_state->getValue('import'));

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
