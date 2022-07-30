<?php

namespace Drupal\myacademicid_user_roles\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\Entity\User;
use Drupal\myacademicid_user_roles\MyacademicidUserRoles;
use Drupal\myacademicid_user_roles\Event\SetUserRolesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SetUserRolesEvent subscriber.
 */
class SetUserRolesEventSubscriber implements EventSubscriberInterface {

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
    MyacademicidUserFields $service,
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
      SetUserRolesEvent::EVENT_NAME => [
        'onSetUserRoles'
      ],
    ];
  }

  /**
   * Subscribe to the user roles change event.
   *
   * @param \Drupal\myacademicid_user_roles\Event\SetUserRolesEvent $event
   *   The event object.
   */
  public function onSetUserRoles(SetUserRolesEvent $event) {
    $message = $this->t('Setting roles for user %user...', [
      '%user' => $event->user->label(),
    ]);

    $this->messenger->addStatus($message);

    $this->service->setUserRoles(
      $event->user,
      $event->roles,
      $event->save
    );
  }

}
