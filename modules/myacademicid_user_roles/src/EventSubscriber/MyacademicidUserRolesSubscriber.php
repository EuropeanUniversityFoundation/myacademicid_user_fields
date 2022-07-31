<?php

namespace Drupal\myacademicid_user_roles\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\myacademicid_user_fields\Event\SetUserVopersonExternalAffilliationEvent;
use Drupal\myacademicid_user_fields\Event\UserSchacHomeOrganizationChangeEvent;
use Drupal\myacademicid_user_fields\Event\UserVopersonExternalAffilliationChangeEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Drupal\myacademicid_user_roles\Event\SetUserRolesEvent;
use Drupal\myacademicid_user_roles\Event\UserRolesChangeEvent;
use Drupal\myacademicid_user_roles\MyacademicidUserRoles;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * MyAcademicID user roles event subscriber.
 */
class MyacademicidUserRolesSubscriber implements EventSubscriberInterface {

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
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EventDispatcherInterface $event_dispatcher,
    MyacademicidUserFields $fields_service,
    MyacademicidUserRoles $roles_service,
    MessengerInterface $messenger
  ) {
    $this->configFactory   = $config_factory;
    $this->eventDispatcher = $event_dispatcher;
    $this->fieldsService   = $fields_service;
    $this->rolesService    = $roles_service;
    $this->messenger       = $messenger;
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
      UserVopersonExternalAffilliationChangeEvent::EVENT_NAME => [
        'onUserVopersonExternalAffilliationChange'
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
    dpm(__METHOD__);
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

    // Edge case: enforce user roles based on affilliation.
    elseif ($mode === MyacademicidUserFields::CLIENT_MODE) {
      $equals = $this->fieldsService
        ->equalValue($event->user, MyacademicidUserFields::FIELD_VEA);
      // Defer to the onUserSchacHomeOrganizationChange method.
      if ($equals) {
        // Instantiate a mock UserVopersonExternalAffilliationChangeEvent.
        $mock = new UserVopersonExternalAffilliationChangeEvent($event->user);
        $this->onUserVopersonExternalAffilliationChange($mock);
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
    dpm(__METHOD__);
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
      $event = new SetUserVopersonExternalAffilliationEvent(
        $event->user,
        $this->rolesService->affilliationfromRoles($event->user, $roles, $sho),
        FALSE
      );
      // Dispatch the event.
      $this->eventDispatcher->dispatch(
        $event,
        SetUserVopersonExternalAffilliationEvent::EVENT_NAME
      );
    }

    // Edge case: Client sets same VEA, different SHO; implies VEA changed.
    elseif ($mode === MyacademicidUserFields::CLIENT_MODE) {
      $equals = $this->fieldsService
        ->equalValue($event->user, MyacademicidUserFields::FIELD_VEA);
      // Defer to the onUserSchacHomeOrganizationChange method.
      if ($equals) {
        // Instantiate a mock UserVopersonExternalAffilliationChangeEvent.
        $mock = new UserVopersonExternalAffilliationChangeEvent($event->user);
        $this->onUserVopersonExternalAffilliationChange($mock);
      }
    }
  }

  /**
   * Subscribe to the user voperson_external_affilliation change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\UserVopersonExternalAffilliationChangeEvent $event
   *   The event object.
   */
  public function onUserVopersonExternalAffilliationChange(UserVopersonExternalAffilliationChangeEvent $event) {
    dpm(__METHOD__);
    $mode = $this->configFactory
      ->get('myacademicid_user_fields.settings')
      ->get('mode');

    if ($mode === MyacademicidUserFields::CLIENT_MODE) {
      // Collect user voperson_external_affilliation values.
      $vea = $this->rolesService
        ->flattenValue($event->user, MyacademicidUserFields::FIELD_VEA);

      // Instantiate our event.
      $event = new SetUserRolesEvent(
        $event->user,
        $this->rolesService->rolesFromAffilliation($event->user, $vea),
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
