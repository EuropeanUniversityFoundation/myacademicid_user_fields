<?php

namespace Drupal\myacademicid_user_roles\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\myacademicid_user_fields\MyacademicidUserAffilliation;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AffilliationMappingForm extends ConfigFormBase {

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
   * The constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserAffilliation $affilliation
   *   The affilliation service.
   */
  public function __construct(
      ConfigFactoryInterface $config_factory,
      MyacademicidUserAffilliation $affilliation
  ) {
    parent::__construct($config_factory);
    $this->affilliation = $affilliation;

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
    );
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
  public function getFormId() {
    return 'myacademicid_user_roles_affilliation_mapping_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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
