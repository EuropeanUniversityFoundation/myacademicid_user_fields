<?php

namespace Drupal\myacademicid_user_fields\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserAffiliation;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure MyAcademicID affiliation types.
 */
class AffiliationTypesForm extends ConfigFormBase {

  use StringTranslationTrait;

  /**
   * The affiliation service.
   */
  protected $affiliation;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserAffiliation $affiliation
   *   The affiliation service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    MyacademicidUserAffiliation $affiliation,
    TranslationInterface $string_translation
  ) {
    parent::__construct($config_factory);
    $this->affiliation      = $affiliation;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('myacademicid_user_fields.affiliation'),
      $container->get('string_translation'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myacademicid_user_fields_affiliation_types';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['myacademicid_user_fields.types'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $intro = '<p>' . $this
      ->t('Here are all the defined affiliation types. %caveat', [
        '%caveat' => $this->t('This configuration is used by submodules.')
      ]) . '</p>';

    $form['intro'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $intro,
    ];

    $header = [
      $this->t('Source'),
      $this->t('Key'),
      $this->t('Label'),
      $this->t('Example claim'),
    ];

    $rows = [];

    $default_types = $this->affiliation->getDefaultTypes();
    $defined_types = $this->affiliation->getDefinedTypes();

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

    $config = $this->config('myacademicid_user_fields.types');

    $additional = (array) $config->get('additional');

    $description = '<p>' . $this
      ->t('Add affiliation types or override the labels of existing ones.');
    $description .= '<br/>' . $this
      ->t('Enter one value per line, in the format key|label.');
    $description .= '<br/>' . $this
      ->t('If no label is provided, the key will also be used as the label.');
    $description .= '</p>';

    $default_text = ($additional) ? implode("\n", $additional) : '';

    $form['additional'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Additional affiliation types'),
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
    $config = $this->config('myacademicid_user_fields.types');

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
