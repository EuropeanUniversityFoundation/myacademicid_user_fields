<?php

namespace Drupal\myacademicid_user_fields\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\myacademicid_user_fields\Event\SetUserSchacHomeOrganizationEvent;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * SetUserSchacHomeOrganizationEvent subscriber.
 */
class SetUserSchacHomeOrganizationEventSubscriber implements EventSubscriberInterface {

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
      SetUserSchacHomeOrganizationEvent::EVENT_NAME => [
        'onSetUserSchacHomeOrganization',
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
    if (empty($event->sho)) {
      $message = $this->t('Unsetting %claim claim for user %user...', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_SHO,
      ]);

      $this->logger->notice($message);
    }
    else {
      $message = $this->t('Setting %claim claim as %sho for user %user...', [
        '%user' => $event->user->label(),
        '%claim' => MyacademicidUserFields::CLAIM_SHO,
        '%sho' => \implode(', ', $event->sho),
      ]);

      $this->logger->notice($message);
    }

    $this->service->setUserSchacHomeOrganization(
      $event->user,
      $event->sho,
      $event->save
    );
  }

}
