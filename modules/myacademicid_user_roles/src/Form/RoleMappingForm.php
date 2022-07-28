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

class RoleMappingForm extends ConfigFormBase {

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

    unset($this->roles[RoleInterface::ANONYMOUS_ID]);
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
    return 'myacademicid_user_roles_role_mapping_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'myacademicid_user_roles.role_to_affilliation',
    ];
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

    $config = $this->config('myacademicid_user_roles.role_to_affilliation');
    $rolemap = $config->get('role_mapping');

    $form['#tree'] = TRUE;
    $form['role_mapping'] = [
      '#title' => $this->t('User role to affilliation type'),
      '#type' => 'fieldset',
      '#description' => $this->t(
        'Map user roles to be converted into affilliation claims. %caveat', [
          '%caveat' => $this->t(
            'The anonymous role cannot be mapped for obvious reasons.'
          )
        ]
      ),
    ];

    $affilliation_options = $this->affilliation->getOptions();

    foreach ($this->roles as $rid => $role) {
      $default = isset($rolemap[$rid]) ? $rolemap[$rid] : '';

      $form['role_mapping'][$rid] = [
        '#type' => 'select',
        '#title' => $role->label(),
        '#options' => $affilliation_options,
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
    $config = $this->config('myacademicid_user_roles.role_to_affilliation');

    $config->set('role_mapping', \array_filter(
      $form_state->getValue('role_mapping')
    ));

    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
