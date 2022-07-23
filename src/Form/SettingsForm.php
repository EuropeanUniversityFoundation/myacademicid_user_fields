<?php

namespace Drupal\myacademicid_user_fields\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure MyAcademicID user fields settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myacademicid_user_fields_settings';
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

    $additional = (array) $config->get('additional');

    $description = '<p>' . $this
      ->t('Add affilliation types or override the labels of existing ones.');
    $description .= '<br/>' . $this
      ->t('Enter one value per line, in the format key|label.');
    $description .= '<br/>' . $this
      ->t('If no label is provided, the key will also be used as the label.');
    $description .= '</p>';

    $default_text = ($additional) ? implode("\n", $additional) : '';

    $form['additional'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Additional affilliation types'),
      '#description' => $description,
      '#default_value' => $default_text,
      '#rows' => 5,
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
    $config = $this->config('myacademicid_user_fields.settings');

    $multiline = $form_state->getValue('additional');

    $additional = array_filter(
      array_map(
        'trim', explode(
          "\n", $multiline
        )
      ), 'strlen'
    );

    $config->set('additional', $additional);

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
