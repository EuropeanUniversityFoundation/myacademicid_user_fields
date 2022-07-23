<?php

namespace Drupal\myacademicid_user_fields\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserAffilliation;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure MyAcademicID affilliation types.
 */
class AffilliationTypesForm extends ConfigFormBase {

  use StringTranslationTrait;

  /**
   * The affilliation service.
   */
  protected $affilliation;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserAffilliation $affilliation
   *   The affilliation service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    MyacademicidUserAffilliation $affilliation,
    TranslationInterface $string_translation
  ) {
    parent::__construct($config_factory);
    $this->affilliation      = $affilliation;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('myacademicid_user_fields.affilliation'),
      $container->get('string_translation'),
    );
  }

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
    $form['intro'] = [
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

    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('Nothing to display.'),
    ];

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
