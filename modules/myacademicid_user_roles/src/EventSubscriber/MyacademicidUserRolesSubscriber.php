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
   * The MyAcademicID User Roles service.
   *
   * @var \Drupal\myacademicid_user_roles\MyacademicidUserRoles
   */
  protected $service;

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
   * @param \Drupal\myacademicid_user_roles\MyacademicidUserRoles $service
   *   The MyAcademicID User Roles service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EventDispatcherInterface $event_dispatcher,
    MyacademicidUserRoles $service,
    MessengerInterface $messenger
  ) {
    $this->configFactory   = $config_factory;
    $this->eventDispatcher = $event_dispatcher;
    $this->service         = $service;
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
    // Get the original schac_home_organization values.
    $old_sho = (isset($event->user->original)) ? $event->user->original
      ->get(MyacademicidUserFields::FIELD_SHO) : [];
    // Get the current schac_home_organization values.
    $new_sho = $event->user
      ->get(MyacademicidUserFields::FIELD_SHO);

    // Always defer to the onUserSchacHomeOrganizationChange method.
    // If no UserSchacHomeOrganizationChangeEvent was dispatched, mock one.
    if ($old_sho === $new_sho) {
      $mock_event = new UserSchacHomeOrganizationChangeEvent($event->user);
      $this->onUserSchacHomeOrganizationChange($mock_event);
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

    if ($mode === MyacademicidUserFields::SERVER_MODE) {
      // Collect user roles and schac_home_organization values.
      $roles = $event->user->getRoles(TRUE);

      $field = $event->user->get(MyacademicidUserFields::FIELD_SHO);
      $sho = [];

      foreach ($field as $key => $item) {
        $sho[] = $item->value;
      }

      // Instantiate our event.
      $event = new SetUserVopersonExternalAffilliationEvent(
        $event->user,
        $this->service->affilliationfromRoles($event->user, $roles, $sho),
        FALSE
      );
      // Dispatch the event.
      $this->eventDispatcher->dispatch(
        $event,
        SetUserVopersonExternalAffilliationEvent::EVENT_NAME
      );
    }
  }

  /**
   * Subscribe to the user voperson_external_affilliation change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\UserVopersonExternalAffilliationChangeEvent $event
   *   The event object.
   */
  public function onUserVopersonExternalAffilliationChange(UserVopersonExternalAffilliationChangeEvent $event) {
    $mode = $this->configFactory
      ->get('myacademicid_user_fields.settings')
      ->get('mode');

    if ($mode === MyacademicidUserFields::CLIENT_MODE) {
      // Collect user voperson_external_affilliation values.
      $field = $event->user->get(MyacademicidUserFields::FIELD_VEA);
      $vea = [];

      foreach ($field as $key => $item) {
        $vea[] = $item->value;
      }

      // Instantiate our event.
      $event = new SetUserRolesEvent(
        $event->user,
        $this->service->rolesFromAffilliation($event->user, $vea),
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
