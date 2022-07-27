<?php

namespace Drupal\myacademicid_user_roles\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserAffilliation;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AffilliationMappingForm extends ConfigFormBase {

  use StringTranslationTrait;

  /**
   * The user roles defined in the system.
   *
   * @var Drupal\user\Entity\Role[]
   */
  protected $roles;

  /**
   * The affilliation service.
   *
   * @var Drupal\myacademicid_user_fields\MyacademicidUserAffilliation
   */
  protected $affilliation;

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
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserAffilliation $affilliation
   *   The affilliation service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    MyacademicidUserAffilliation $affilliation,
    MessengerInterface $messenger,
    TranslationInterface $string_translation
  ) {
    parent::__construct($config_factory);
    $this->affilliation      = $affilliation;
    $this->messenger         = $messenger;
    $this->stringTranslation = $string_translation;

    $this->roles = Role::loadMultiple();

    foreach ($this->roles as $rid => $role) {
      if (
        $role->isAdmin() ||
        $rid === RoleInterface::ANONYMOUS_ID ||
        $rid === RoleInterface::AUTHENTICATED_ID
      ) {
        unset($this->roles[$rid]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('myacademicid_user_fields.affilliation'),
      $container->get('messenger'),
      $container->get('string_translation'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myacademicid_user_roles_affilliation_mapping_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'myacademicid_user_roles.affilliation_to_role',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $mode = $this->config('myacademicid_user_fields.settings')->get('mode');
    if ($mode === MyacademicidUserFields::SERVER_MODE) {
      $settings_link = Link::fromTextAndUrl($this->t('Server mode'),
        Url::fromRoute('myacademicid_user_fields.settings_form'))->toString();

      $warning = $this
        ->t('These settings have no effect when operating in @mode.', [
          '@mode' => $settings_link,
        ]);

      $this->messenger->addWarning($warning);
    }

    $config = $this->config('myacademicid_user_roles.affilliation_to_role');
    $affilliationmap = $config->get('affilliation_mapping');

    $form['#tree'] = TRUE;
    $form['affilliation_mapping'] = [
      '#title' => $this->t('Affilliation type to user role'),
      '#type' => 'fieldset',
      '#description' => $this->t(
        'Map affilliation claims to be converted into user roles. %caveat', [
          '%caveat' => $this->t(
            'Admin roles cannot be automatically assigned for security reasons.'
          )
        ]
      ),
    ];

    $role_options = [];
    foreach ($this->roles as $rid => $role) {
      $role_options[$rid] = $role->label();
    }

    foreach ($this->affilliation->getOptions() as $key => $label) {
      $default = isset($affilliationmap[$key]) ? $affilliationmap[$key] : '';

      $form['affilliation_mapping'][$key] = [
        '#type' => 'select',
        '#title' => $label,
        '#options' => $role_options,
        '#empty_value' => '',
        '#empty_option' => $this->t('- No mapping -'),
        '#default_value' => $default,
      ];
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
    $config = $this->config('myacademicid_user_roles.affilliation_to_role');

    $config->set('affilliation_mapping', $form_state
      ->getValue('affilliation_mapping')
    );

    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
