<?php

namespace Drupal\myacademicid_user_roles\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\myacademicid_user_fields\Event\UserVopersonExternalAffilliationChangeEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Drupal\myacademicid_user_roles\MyacademicidUserRoles;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * MyAcademicID user fields event subscriber.
 */
class UserVopersonExternalAffilliationChangeEventSubscriber implements EventSubscriberInterface {

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
      UserVopersonExternalAffilliationChangeEvent::EVENT_NAME => [
        'onUserVopersonExternalAffilliationChange'
      ],
    ];
  }

  /**
   * Subscribe to the user voperson_external_affilliation change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\UserVopersonExternalAffilliationChangeEvent $event
   *   The event object.
   */
  public function onUserVopersonExternalAffilliationChange(UserVopersonExternalAffilliationChangeEvent $event) {
    $this->service->rolesFromAffilliation($event->user, $event->vea);
  }

}
