<?php

namespace Drupal\myacademicid_user_fields\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\myacademicid_user_fields\Event\SetUserSchacPersonalUniqueCodeEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SetUserSchacPersonalUniqueCodeEvent subscriber.
 */
class SetUserSchacPersonalUniqueCodeEventSubscriber implements EventSubscriberInterface {

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
      SetUserSchacPersonalUniqueCodeEvent::EVENT_NAME => [
        'onSetUserSchacPersonalUniqueCode',
      ],
    ];
  }

  /**
   * Subscribe to the user schac_personal_unique_code change event.
   *
   * @param \Drupal\myacademicid_user_fields\Event\SetUserSchacPersonalUniqueCodeEvent $event
   *   The event object.
   */
  public function onSetUserSchacPersonalUniqueCode(SetUserSchacPersonalUniqueCodeEvent $event) {
    if (empty($event->spuc)) {
      $message = $this->t('Unsetting %claim claim for user %user...', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_SPUC,
      ]);

      $this->logger->notice($message);
    }
    else {
      $message = $this->t('Setting %claim claim as %spuc for user %user...', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_SPUC,
        '%spuc' => \implode(', ', $event->spuc),
      ]);

      $this->logger->notice($message);
    }

    $this->service->setUserSchacPersonalUniqueCode(
      $event->user,
      $event->spuc,
      $event->save
    );
  }

}
