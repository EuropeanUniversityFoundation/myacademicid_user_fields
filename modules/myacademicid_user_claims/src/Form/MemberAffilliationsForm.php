<?php

namespace Drupal\myacademicid_user_claims\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\myacademicid_user_claims\AffilliationAssertion;
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
    return 'myacademicid_user_claims_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['myacademicid_user_claims.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('myacademicid_user_claims.settings');

    $assertions = (array) $config->get('assert_member');

    $types = $this->affilliation->getDefinedTypes();

    unset($types[MyacademicidUserAffilliation::MEMBER]);

    $defaults = AffilliationAssertion::ASSERT_MEMBER;
    $list = [];

    foreach ($defaults as $i => $value) {
      $list[] = $value;
      unset($types[$value]);
    }

    $intro = '<p>' . $this
      ->t('By default, the %member affilliation must be asserted for:', [
        '%member' => MyacademicidUserAffilliation::MEMBER
      ]) . '</p>';

    $intro .= '<ul>';
    foreach ($list as $i => $value) {
      $intro .= '<li>' . $this->t('%key', [
        '%key' => $value
      ]) . '</li>';
    }
    $intro .= '</ul>';

    $intro .= '<p>' . $this
      ->t('The %member affilliation will be asserted if %condition.', [
        '%member' => MyacademicidUserAffilliation::MEMBER,
        '%condition' => $this->t(
          'at least one of the above or below affilliations is present'
        ),
      ]) . '</p>';

    $intro .= '<p>' . $this
      ->t('If %condition, the %member affilliation will not be asserted.', [
        '%condition' => $this->t(
          'none of the above or below affilliations are present'),
        '%member' => MyacademicidUserAffilliation::MEMBER,
      ]) . '</p>';

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
    $config = $this->config('myacademicid_user_claims.settings');

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
