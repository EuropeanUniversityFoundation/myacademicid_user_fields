<?php

namespace Drupal\myacademicid_user_roles\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Drupal\myacademicid_user_roles\Event\UserRoleChangeEvent;
use Drupal\myacademicid_user_roles\MyacademicidUserRoles;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * User role change event subscriber.
 */
class UserRoleChangeEventSubscriber implements EventSubscriberInterface {

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
    $user = User::load($event->uid);

    if (empty($event->roles)) {
      $message = $this->t('User %user has no specific roles.', [
        '%user' => $user->label(),
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
        '%user' => $user->label(),
        '@roles' => (\count($labels)>1) ? $this->t('roles') : $this->t('role'),
        '%labels' => \implode(', ', $labels)
      ]);

      $this->messenger->addStatus($message);

      $sho = [];
      $field = $user->get(MyacademicidUserFields::FIELD_SHO);

      foreach ($field as $idx => $value) {
        $sho[] = $value;
      }

      $this->service->affilliationFromRoles($event->uid, $event->roles, $sho);
    }

    unset($user);
  }

}
