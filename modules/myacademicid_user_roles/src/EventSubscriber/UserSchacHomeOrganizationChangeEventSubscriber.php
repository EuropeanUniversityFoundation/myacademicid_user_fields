<?php

namespace Drupal\myacademicid_user_roles\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\myacademicid_user_fields\Event\UserSchacHomeOrganizationChangeEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Drupal\myacademicid_user_roles\MyacademicidUserRoles;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * MyAcademicID user fields event subscriber.
 */
class UserSchacHomeOrganizationChangeEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

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
   * @param \Drupal\myacademicid_user_roles\MyacademicidUserRoles $service
   *   The MyAcademicID User Roles service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    MyacademicidUserRoles $service,
    MessengerInterface $messenger,
    TranslationInterface $string_translation
  ) {
    $this->service           = $service;
    $this->messenger         = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      UserSchacHomeOrganizationChangeEvent::EVENT_NAME => [
        'onUserSchacHomeOrganizationChange'
      ],
    ];
  }

  /**
   * Subscribe to the user schac_home_organization change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\UserSchacHomeOrganizationChangeEvent $event
   *   The event object.
   */
  public function onUserSchacHomeOrganizationChange(UserSchacHomeOrganizationChangeEvent $event) {
    $roles = $event->user->getRoles(TRUE);

    $this->service->affilliationFromRoles($event->user, $roles, $event->sho);
  }

}
