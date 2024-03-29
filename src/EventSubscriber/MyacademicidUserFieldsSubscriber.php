<?php

namespace Drupal\myacademicid_user_fields\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\Entity\User;
use Drupal\myacademicid_user_fields\Event\UserSchacHomeOrganizationChangeEvent;
use Drupal\myacademicid_user_fields\Event\UserSchacPersonalUniqueCodeChangeEvent;
use Drupal\myacademicid_user_fields\Event\UserVopersonExternalAffiliationChangeEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * MyAcademicID user fields event subscriber.
 */
class MyacademicidUserFieldsSubscriber implements EventSubscriberInterface {

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
      UserSchacHomeOrganizationChangeEvent::EVENT_NAME => [
        'onUserSchacHomeOrganizationChange'
      ],
      UserSchacPersonalUniqueCodeChangeEvent::EVENT_NAME => [
        'onUserSchacPersonalUniqueCodeChange'
      ],
      UserVopersonExternalAffiliationChangeEvent::EVENT_NAME => [
        'onUserVopersonExternalAffiliationChange'
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
    if (empty($event->sho)) {
      $message = $this->t('User %user has no %claim claim.', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_SHO,
      ]);

      // $this->messenger->addWarning($message);
    }
    else {
      $message = $this->t('User %user has a %claim claim of %value.', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_SHO,
        '%value' => \implode(', ', $event->sho)
      ]);

      // $this->messenger->addStatus($message);
    }
  }

  /**
   * Subscribe to the user schac_personal_unique_code change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\UserSchacPersonalUniqueCodeChangeEvent $event
   *   The event object.
   */
  public function onUserSchacPersonalUniqueCodeChange(UserSchacPersonalUniqueCodeChangeEvent $event) {
    if (empty($event->spuc)) {
      $message = $this->t('User %user has no %claim claim.', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_SPUC,
      ]);

      // $this->messenger->addWarning($message);
    }
    else {
      $message = $this->t('User %user has a %claim claim of %value.', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_SPUC,
        '%value' => \implode(', ', $event->spuc)
      ]);

      // $this->messenger->addStatus($message);
    }
  }

  /**
   * Subscribe to the user voperson_external_affiliation change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\UserVopersonExternalAffiliationChangeEvent $event
   *   The event object.
   */
  public function onUserVopersonExternalAffiliationChange(UserVopersonExternalAffiliationChangeEvent $event) {
    if (empty($event->vea)) {
      $message = $this->t('User %user has no %claim claim.', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_VEA,
      ]);

      // $this->messenger->addWarning($message);
    }
    else {
      $message = $this->t('User %user has a %claim claim of %value.', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_VEA,
        '%value' => \implode(', ', $event->vea)
      ]);

      // $this->messenger->addStatus($message);
    }
  }

}
