<?php

namespace Drupal\myacademicid_user_fields\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\user\Entity\User;
use Drupal\myacademicid_user_fields\Event\SetUserSchacHomeOrganizationEvent;
use Drupal\myacademicid_user_fields\Event\SetUserSchacPersonalUniqueCodeEvent;
use Drupal\myacademicid_user_fields\Event\SetUserVopersonExternalAffilliationEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to trigger Events.
 */
class TriggerSetEventsForm extends FormBase {

  const CLAIM_SHO = MyacademicidUserFields::CLAIM_SHO;
  const CLAIM_SPUC = MyacademicidUserFields::CLAIM_SPUC;
  const CLAIM_VEA = MyacademicidUserFields::CLAIM_VEA;

  const EVENT_CLASS = [
    self::CLAIM_SHO => SetUserSchacHomeOrganizationEvent::class ,
    self::CLAIM_SPUC => SetUserSchacPersonalUniqueCodeEvent::class ,
    self::CLAIM_VEA => SetUserVopersonExternalAffilliationEvent::class
  ];

  /**
   * The current user entity.
   */
  protected $currentUser;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
  * The constructor.
  *
  * @param \Drupal\Core\Session\AccountProxy $current_user
  *   A proxied implementation of AccountInterface.
  * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
  *   The event dispatcher service.
   */
  public function __construct(
    AccountProxy $current_user,
    EventDispatcherInterface $event_dispatcher
  ) {
    $this->currentUser     = User::load($current_user->id());
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('event_dispatcher'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myacademicid_user_fields_trigger_set_events';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $markup = '<p>' . $this->t('Use this form to trigger events:') . '</p>';
    $markup .= '<ul>';
    foreach (self::EVENT_CLASS as $claim => $event_class) {
      $markup .= '<li><em>' . $event_class . '</em></li>';
    }
    $markup .= '</ul>';

    $form['intro'] = [
      '#type' => 'markup',
      '#markup' => $markup,
    ];

    $form['user'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('User entity'),
      '#description' => $this->t('Only your own account can be changed.'),
      '#default_value' => $this->currentUser,
      '#target_type' => 'user',
      '#selection_handler' => 'default',
      '#attributes' => [
        'readonly' => TRUE
      ],
    ];

    foreach (self::EVENT_CLASS as $claim => $event_class) {
      $form[$claim] = [
        '#type' => 'textarea',
        '#title' => $this->t('MyAcademicID %claim claim.', [
          '%claim' => $claim
        ]),
        '#description' => $this->t('Enter one value per line.'),
        '#rows' => 3,
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Trigger Set Events'),
      '#attributes' => [
        'class' => [
          'button--primary',
        ]
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = $this->currentUser;

    $values = [];

    foreach (self::EVENT_CLASS as $claim => $event_class) {
      $values[$claim] = array_filter(
        array_map(
          'trim', explode(
            "\n", $form_state->getValue($claim)
          )
        ), 'strlen'
      );

      // Instantiate our event.
      $event = new $event_class($user->id(), $values[$claim]);
      // Dispatch the event.
      $this->eventDispatcher
        ->dispatch($event, $event_class::EVENT_NAME);
    }
  }

}
