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
 * Configure MyAcademicID member affilliations.
 */
class MemberAffilliationsForm extends ConfigFormBase {

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
    $config = $this->config('myacademicid_user_fields.settings');

    $assertions = $config->get('assert_member');

    $types = $this->affilliation->getDefinedTypes();

    unset($types[MyacademicidUserAffilliation::MEMBER]);

    $defaults = MyacademicidUserAffilliation::ASSERT_MEMBER;
    $list = [];

    foreach ($defaults as $i => $value) {
      $list[] = $value;
      unset($types[$value]);
    }

    $intro = '<p>' . $this
      ->t('By default, the %member affilliation is asserted for:', [
        '%member' => MyacademicidUserAffilliation::MEMBER
      ]) . '</p>';

    $intro .= '<ul>';
    foreach ($list as $i => $value) {
      $intro .= '<li>' . $this->t('%key', [
        '%key' => $value
      ]) . '</li>';
    }
    $intro .= '</ul>';

    $form['intro'] = [
      '#type' => 'item',
      '#markup' => $intro,
    ];

    $form['assert'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Assert %member affilliation for:', [
        '%member' => MyacademicidUserAffilliation::MEMBER,
      ]),
      '#options' => $types,
    ];

    foreach ($assertions as $key) {
      $form['assert'][$key]['#default_value'] = TRUE;
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

    $assertions = [];

    $checkboxes = $form_state->getValue('assert');

    foreach ($checkboxes as $key => $value) {
      if ($checkboxes[$key]) {
        $assertions[] = $key;
      }
    }

    $config->set('assert_member', $assertions);

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
