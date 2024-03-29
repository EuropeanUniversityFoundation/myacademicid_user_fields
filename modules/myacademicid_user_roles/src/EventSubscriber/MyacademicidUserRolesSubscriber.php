<?php

namespace Drupal\myacademicid_user_roles\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\myacademicid_user_fields\Event\SetUserVopersonExternalAffiliationEvent;
use Drupal\myacademicid_user_fields\Event\UserSchacHomeOrganizationChangeEvent;
use Drupal\myacademicid_user_fields\Event\UserVopersonExternalAffiliationChangeEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Drupal\myacademicid_user_roles\Event\SetUserRolesEvent;
use Drupal\myacademicid_user_roles\Event\UserRolesChangeEvent;
use Drupal\myacademicid_user_roles\MyacademicidUserRoles;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * MyAcademicID user roles event subscriber.
 */
class MyacademicidUserRolesSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The MyAcademicID user fields service.
   *
   * @var \Drupal\myacademicid_user_fields\MyacademicidUserFields
   */
  protected $fieldsService;

  /**
   * The MyAcademicID user roles service.
   *
   * @var \Drupal\myacademicid_user_roles\MyacademicidUserRoles
   */
  protected $rolesService;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserFields $fields_service
   *   The MyAcademicID user fields service.
   * @param \Drupal\myacademicid_user_roles\MyacademicidUserRoles $roles_service
   *   The MyAcademicID user roles service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EventDispatcherInterface $event_dispatcher,
    MyacademicidUserFields $fields_service,
    MyacademicidUserRoles $roles_service,
    MessengerInterface $messenger,
    TranslationInterface $string_translation
  ) {
    $this->configFactory     = $config_factory;
    $this->eventDispatcher   = $event_dispatcher;
    $this->fieldsService     = $fields_service;
    $this->rolesService      = $roles_service;
    $this->messenger         = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      UserRolesChangeEvent::EVENT_NAME => [
        'onUserRolesChange'
      ],
      UserSchacHomeOrganizationChangeEvent::EVENT_NAME => [
        'onUserSchacHomeOrganizationChange'
      ],
      UserVopersonExternalAffiliationChangeEvent::EVENT_NAME => [
        'onUserVopersonExternalAffiliationChange'
      ],
    ];
  }

  /**
   * Subscribe to the user roles change event.
   *
   * @param \Drupal\myacademicid_user_roles\Event\UserRolesChangeEvent $event
   *   The event object.
   */
  public function onUserRolesChange(UserRolesChangeEvent $event) {
    if (empty($event->roles)) {
      $message = $this->t('No roles set for user %user.', [
        '%user' => $event->user->label(),
      ]);

      // $this->messenger->addWarning($message);
    }
    else {
      $labels = $this->rolesService->roleLabels($event->roles);

      $message = $this->t('%labels @roles set for user %user.', [
        '%labels' => \implode(', ', \array_unique($labels)),
        '@roles' => (count($labels) > 1) ? $this->t('roles') : $this->t('role'),
        '%user' => $event->user->label(),
      ]);

      // $this->messenger->addStatus($message);
    }

    $mode = $this->configFactory
      ->get('myacademicid_user_fields.settings')
      ->get('mode');

    // Default case: Server sets different SHO and/or Roles; recalculate VEA.
    if ($mode === MyacademicidUserFields::SERVER_MODE) {
      $equals = $this->fieldsService
        ->equalValue($event->user, MyacademicidUserFields::FIELD_SHO);
      // Defer to the onUserSchacHomeOrganizationChange method.
      if ($equals) {
        // Instantiate a mock UserSchacHomeOrganizationChangeEvent.
        $mock_event = new UserSchacHomeOrganizationChangeEvent($event->user);
        $this->onUserSchacHomeOrganizationChange($mock_event);
      }
    }

    // Edge case: enforce user roles based on affiliation.
    elseif ($mode === MyacademicidUserFields::CLIENT_MODE) {
      $equals = $this->fieldsService
        ->equalValue($event->user, MyacademicidUserFields::FIELD_VEA);
      // Defer to the onUserSchacHomeOrganizationChange method.
      if ($equals) {
        // Instantiate a mock UserVopersonExternalAffiliationChangeEvent.
        $mock = new UserVopersonExternalAffiliationChangeEvent($event->user);
        $this->onUserVopersonExternalAffiliationChange($mock);
      }
    }
  }

  /**
   * Subscribe to the user schac_home_organization change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\UserSchacHomeOrganizationChangeEvent $event
   *   The event object.
   */
  public function onUserSchacHomeOrganizationChange(UserSchacHomeOrganizationChangeEvent $event) {
    $mode = $this->configFactory
      ->get('myacademicid_user_fields.settings')
      ->get('mode');

    // Default case: Server sets different SHO and/or Roles; recalculate VEA.
    if ($mode === MyacademicidUserFields::SERVER_MODE) {
      // Collect user roles and schac_home_organization values.
      $roles = $event->user->getRoles(TRUE);

      $sho = $this->rolesService
        ->flattenValue($event->user, MyacademicidUserFields::FIELD_SHO);

      // Instantiate our event.
      $event = new SetUserVopersonExternalAffiliationEvent(
        $event->user,
        $this->rolesService->affiliationfromRoles($event->user, $roles, $sho),
        FALSE
      );
      // Dispatch the event.
      $this->eventDispatcher->dispatch(
        $event,
        SetUserVopersonExternalAffiliationEvent::EVENT_NAME
      );
    }

    // Edge case: Client sets same VEA, different SHO; implies VEA changed.
    elseif ($mode === MyacademicidUserFields::CLIENT_MODE) {
      $equals = $this->fieldsService
        ->equalValue($event->user, MyacademicidUserFields::FIELD_VEA);
      // Defer to the onUserSchacHomeOrganizationChange method.
      if ($equals) {
        // Instantiate a mock UserVopersonExternalAffiliationChangeEvent.
        $mock = new UserVopersonExternalAffiliationChangeEvent($event->user);
        $this->onUserVopersonExternalAffiliationChange($mock);
      }
    }
  }

  /**
   * Subscribe to the user voperson_external_affiliation change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\UserVopersonExternalAffiliationChangeEvent $event
   *   The event object.
   */
  public function onUserVopersonExternalAffiliationChange(UserVopersonExternalAffiliationChangeEvent $event) {
    $mode = $this->configFactory
      ->get('myacademicid_user_fields.settings')
      ->get('mode');

    if ($mode === MyacademicidUserFields::CLIENT_MODE) {
      // Collect user voperson_external_affiliation values.
      $vea = $this->rolesService
        ->flattenValue($event->user, MyacademicidUserFields::FIELD_VEA);

      // Instantiate our event.
      $event = new SetUserRolesEvent(
        $event->user,
        $this->rolesService->rolesFromAffiliation($event->user, $vea),
        FALSE
      );
      // Dispatch the event.
      $this->eventDispatcher->dispatch(
        $event,
        SetUserRolesEvent::EVENT_NAME
      );
    }
  }

}
