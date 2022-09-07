<?php

namespace Drupal\myacademicid_user_roles\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\Entity\User;
use Drupal\myacademicid_user_roles\Event\SetUserRolesEvent;
use Drupal\myacademicid_user_roles\MyacademicidUserRoles;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SetUserRolesEvent subscriber.
 */
class SetUserRolesEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The MyAcademicID user roles service.
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
   *   The MyAcademicID user roles service.
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
    if (empty($event->roles)) {
      $message = $this->t('Unsetting mapped roles for user %user...', [
        '%user' => $event->user->label(),
      ]);

      $this->messenger->addWarning($message);
    }
    else {
      $labels = $this->service->roleLabels($event->roles);

      $message = $this->t('Setting mapped @roles %labels for user %user...', [
        '%user' => $event->user->label(),
        '@roles' => (count($labels) > 1) ? $this->t('roles') : $this->t('role'),
        '%labels' => \implode(', ', \array_unique($labels)),
      ]);

      $this->messenger->addStatus($message);
    }

    $this->service->setUserRoles(
      $event->user,
      $event->roles,
      $event->save
    );
  }

}
