<?php

namespace Drupal\myacademicid_user_fields\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\Entity\User;
use Drupal\myacademicid_user_fields\Event\SetUserSchacHomeOrganizationEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SetUserSchacHomeOrganizationEvent subscriber.
 */
class SetUserSchacHomeOrganizationEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The MyAcademicID User Fields service.
   *
   * @var \Drupal\myacademicid_user_fields\MyacademicidUserFields
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
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserFields $service
   *   The MyAcademicID User Fields service.
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
      SetUserSchacHomeOrganizationEvent::EVENT_NAME => [
        'onSetUserSchacHomeOrganization'
      ],
    ];
  }

  /**
   * Subscribe to the user schac_home_organization change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\SetUserSchacHomeOrganizationEvent $event
   *   The event object.
   */
  public function onSetUserSchacHomeOrganization(SetUserSchacHomeOrganizationEvent $event) {
    dpm(__METHOD__);
    if (empty($event->sho)) {
      $message = $this->t('Unsetting %claim claim for user %user...', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_SHO,
      ]);

      $this->messenger->addWarning($message);
    }
    else {
      $message = $this->t('Setting %claim claim as %sho for user %user...', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_SHO,
        '%sho' => \implode(', ', $event->sho)
      ]);

      $this->messenger->addStatus($message);
    }

    $this->service->setUserSchacHomeOrganization(
      $event->user,
      $event->sho,
      $event->save
    );
  }

}
