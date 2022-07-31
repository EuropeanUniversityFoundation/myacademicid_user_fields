<?php

namespace Drupal\myacademicid_user_hei;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\UserInterface;
use Drupal\ewp_institutions_user\InstitutionUserBridge;
use Drupal\myacademicid_user_fields\MyacademicidUserFields;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * MyAcademicID user institution service.
 */
class MyacademicidUserHei {

  use StringTranslationTrait;

  const ENTITY_TYPE = InstitutionUserBridge::ENTITY_TYPE;
  const UNIQUE_FIELD = InstitutionUserBridge::UNIQUE_FIELD;
  const BASE_FIELD = InstitutionUserBridge::BASE_FIELD;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * EWP Institutions user bridge service.
   *
   * @var \Drupal\ewp_institutions_user\InstitutionUserBridge
   */
  protected $heiUser;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The MyAcademicID user fields service.
   *
   * @var \Drupal\myacademicid_user_fields\MyacademicidUserFields
   */
  protected $maidUserFields;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   * @param \Drupal\ewp_institutions_user\InstitutionUserBridge $hei_user
   *   EWP Institutions user bridge service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\myacademicid_user_fields\MyacademicidUserFields $maid_user_fields
   *   The MyAcademicID user fields service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    EventDispatcherInterface $event_dispatcher,
    InstitutionUserBridge $hei_user,
    MessengerInterface $messenger,
    MyacademicidUserFields $maid_user_fields,
    TranslationInterface $string_translation
  ) {
    $this->configFactory     = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->eventDispatcher   = $event_dispatcher;
    $this->heiUser           = $hei_user;
    $this->messenger         = $messenger;
    $this->maidUserFields    = $maid_user_fields;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Get Institution by schac_home_organization.
   *
   * @param string $sho
   *   The event object.
   */
  public function getHeiBySho(string $sho) {
    dpm(__METHOD__);
    $hei = $this->entityTypeManager
      ->getStorage(self::ENTITY_TYPE)
      ->loadByProperties([self::UNIQUE_FIELD => $sho]);

    return $hei;
  }

}
