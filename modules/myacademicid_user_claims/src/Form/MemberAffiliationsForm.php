<?php

namespace Drupal\myacademicid_user_claims\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\myacademicid_user_claims\AffiliationAssertion;
use Drupal\myacademicid_user_fields\MyacademicidUserAffiliation;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure MyAcademicID member affiliations.
 */
class MemberAffiliationsForm extends ConfigFormBase {

  use StringTranslationTrait;

  /**
   * The affiliation service.
   */
  protected $affiliation;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserAffiliation $affiliation
   *   The affiliation service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    MyacademicidUserAffiliation $affiliation,
    MessengerInterface $messenger,
    TranslationInterface $string_translation
  ) {
    parent::__construct($config_factory);
    $this->affiliation       = $affiliation;
    $this->messenger         = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('myacademicid_user_fields.affiliation'),
      $container->get('messenger'),
      $container->get('string_translation'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myacademicid_user_claims_member_affiliation_form';
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
    $mode = $this->config('myacademicid_user_fields.settings')->get('mode');
    if ($mode === MyacademicidUserFields::CLIENT_MODE) {
      $settings_link = Link::fromTextAndUrl($this->t('Client mode'),
        Url::fromRoute('myacademicid_user_fields.settings_form'))->toString();

      $warning = $this
        ->t('These settings have no effect when operating in @mode.', [
          '@mode' => $settings_link,
        ]);

      $this->messenger->addWarning($warning);
    }

    $config = $this->config('myacademicid_user_claims.settings');

    $assertions = (array) $config->get('assert_member');

    $types = $this->affiliation->getDefinedTypes();

    unset($types[MyacademicidUserAffiliation::MEMBER]);

    $defaults = AffiliationAssertion::ASSERT_MEMBER;
    $list = [];

    foreach ($defaults as $i => $value) {
      $list[] = $value;
      unset($types[$value]);
    }

    $intro = '<p>' . $this
      ->t('By default, the %member affiliation must be asserted for:', [
        '%member' => MyacademicidUserAffiliation::MEMBER
      ]) . '</p>';

    $intro .= '<ul>';
    foreach ($list as $i => $value) {
      $intro .= '<li>' . $this->t('%key', [
        '%key' => $value
      ]) . '</li>';
    }
    $intro .= '</ul>';

    $intro .= '<p>' . $this
      ->t('The %member affiliation will be asserted if %condition.', [
        '%member' => MyacademicidUserAffiliation::MEMBER,
        '%condition' => $this->t(
          'at least one of the above or below affiliations is present'
        ),
      ]) . '</p>';

    $intro .= '<p>' . $this
      ->t('If %condition, the %member affiliation will not be asserted.', [
        '%condition' => $this->t(
          'none of the above or below affiliations are present'),
        '%member' => MyacademicidUserAffiliation::MEMBER,
      ]) . '</p>';

    $form['intro'] = [
      '#type' => 'item',
      '#markup' => $intro,
    ];

    $form['assert'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Assert %member affiliation for:', [
        '%member' => MyacademicidUserAffiliation::MEMBER,
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
