<?php

namespace Drupal\myacademicid_user_roles\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\Entity\Role;
use Drupal\myacademicid_user_roles\Event\UserRoleChangeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * User role change event subscriber.
 */
class UserRoleChangeEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
   public function __construct(
     MessengerInterface $messenger,
     TranslationInterface $string_translation
   ) {
     $this->messenger         = $messenger;
     $this->stringTranslation = $string_translation;
   }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      UserRoleChangeEvent::EVENT_NAME => ['onUserRoleChange'],
    ];
  }

  /**
   * Subscribe to the user role change event.
   *
   * @param \Drupal\myacademicid_user_roles\Event\UserRoleChangeEvent $event
   *   The event object.
   */
  public function onUserRoleChange(UserRoleChangeEvent $event) {
    if (empty($event->roles)) {
      $message = $this->t('User %user has no specific roles.', [
        '%user' => $event->user->label(),
      ]);

      $this->messenger->addWarning($message);
    }
    else {
      $roles = Role::loadMultiple($event->roles);
      $labels = [];

      foreach ($roles as $idx => $role) {
        $labels[] = $role->label();
      }

      $message = $this->t('User %user has the @roles %labels.', [
        '%user' => $event->user->label(),
        '@roles' => (\count($labels)>1) ? $this->t('roles') : $this->t('role'),
        '%labels' => \implode(', ', $labels)
      ]);

      $this->messenger->addStatus($message);
    }
  }

}
