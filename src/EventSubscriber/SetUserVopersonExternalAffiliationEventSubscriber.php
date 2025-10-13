<?php

namespace Drupal\myacademicid_user_fields\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\myacademicid_user_fields\Event\SetUserVopersonExternalAffiliationEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SetUserVopersonExternalAffiliationEvent subscriber.
 */
class SetUserVopersonExternalAffiliationEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The MyAcademicID user fields service.
   *
   * @var \Drupal\myacademicid_user_fields\MyacademicidUserFields
   */
  protected $service;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserFields $service
   *   The MyAcademicID user fields service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger_factory,
    MyacademicidUserFields $service,
    TranslationInterface $string_translation,
  ) {
    $this->logger            = $logger_factory->get('myacademicid_user_fields');
    $this->service           = $service;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SetUserVopersonExternalAffiliationEvent::EVENT_NAME => [
        'onSetUserVopersonExternalAffiliation',
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

      $this->logger->notice($message);
    }
    else {
      $message = $this->t('Setting %claim claim as %vea for user %user...', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_VEA,
        '%vea' => \implode(', ', $event->vea),
      ]);

      $this->logger->notice($message);
    }

    $this->service->setUserVopersonExternalAffiliation(
      $event->user,
      $event->vea,
      $event->save
    );
  }

}
