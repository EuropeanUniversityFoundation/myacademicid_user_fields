<?php

namespace Drupal\myacademicid_user_fields\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\Entity\User;
use Drupal\myacademicid_user_fields\Event\SetUserVopersonExternalAffiliationEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SetUserVopersonExternalAffiliationEvent subscriber.
 */
class SetUserVopersonExternalAffiliationEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The MyAcademicID user fields service.
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
   *   The MyAcademicID user fields service.
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
      SetUserVopersonExternalAffiliationEvent::EVENT_NAME => [
        'onSetUserVopersonExternalAffiliation'
      ],
    ];
  }

  /**
   * Subscribe to the user voperson_external_affiliation change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\SetUserVopersonExternalAffiliationEvent $event
   *   The event object.
   */
  public function onSetUserVopersonExternalAffiliation(SetUserVopersonExternalAffiliationEvent $event) {
    if (empty($event->vea)) {
      $message = $this->t('Unsetting %claim claim for user %user...', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_VEA,
      ]);

      // $this->messenger->addWarning($message);
    }
    else {
      $message = $this->t('Setting %claim claim as %vea for user %user...', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_VEA,
        '%vea' => \implode(', ', $event->vea)
      ]);

      // $this->messenger->addStatus($message);
    }

    $this->service->setUserVopersonExternalAffiliation(
      $event->user,
      $event->vea,
      $event->save
    );
  }

}
