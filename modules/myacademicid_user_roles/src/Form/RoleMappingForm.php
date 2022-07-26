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

class RoleMappingForm extends ConfigFormBase {

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

    unset($this->roles[RoleInterface::ANONYMOUS_ID]);
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
      'myacademicid_user_roles.rolemap',
    ];
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('myacademicid_user_roles.rolemap');
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

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('myacademicid_user_roles.rolemap');

    $config->set('role_mapping', $form_state->getValue('role_mapping'));

    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
