<?php

namespace Drupal\myacademicid_user_fields;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\UserInterface;
use Drupal\myacademicid_user_fields\Event\UserSchacHomeOrganizationChangeEvent;
use Drupal\myacademicid_user_fields\Event\UserSchacPersonalUniqueCodeChangeEvent;
use Drupal\myacademicid_user_fields\Event\UserVopersonExternalAffilliationChangeEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * MyAcademicID User Fields service.
 */
class MyacademicidUserFields {

  use StringTranslationTrait;

  const FIELD_SHO = 'maid_schac_home_organization';
  const FIELD_SPUC = 'maid_schac_personal_unique_code';
  const FIELD_VEA = 'maid_voperson_external_affilliation';

  const EVENT_CLASS = [
    self::FIELD_SHO => UserSchacHomeOrganizationChangeEvent::class,
    self::FIELD_SPUC => UserSchacPersonalUniqueCodeChangeEvent::class,
    self::FIELD_VEA => UserVopersonExternalAffilliationChangeEvent::class,
  ];

  const CLAIM_SHO = 'schac_home_organization';
  const CLAIM_SPUC = 'schac_personal_unique_code';
  const CLAIM_VEA = 'voperson_external_affilliation';

  const DESCRIPTION = 'As provided by MyAcademicID.';

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The constructor.
   *
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    EventDispatcherInterface $event_dispatcher,
    TranslationInterface $string_translation
  ) {
    $this->eventDispatcher    = $event_dispatcher;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Attach base fields to an entity.
   *
   * @return array $fields[]
   */
  public function attachBaseFields(): array {
    $fields[self::FIELD_SHO] = BaseFieldDefinition::create('string')
      ->setLabel($this->t('MyAcademicID %claim claim.', [
        '%claim' => self::CLAIM_SHO
      ]))
      ->setDescription(self::DESCRIPTION)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'textfield',
        'weight' => 100,
      ])
      ->addConstraint('SchacHomeOrganization');

    $fields[self::FIELD_SPUC] = BaseFieldDefinition::create('string')
      ->setLabel($this->t('MyAcademicID %claim claim.', [
        '%claim' => self::CLAIM_SPUC
      ]))
      ->setDescription(self::DESCRIPTION)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'textfield',
        'weight' => 100,
      ]);
      // ->addConstraint('SchacPersonalUniqueCode');

    $fields[self::FIELD_VEA] = BaseFieldDefinition::create('string')
      ->setLabel($this->t('MyAcademicID %claim claim.', [
        '%claim' => self::CLAIM_VEA
      ]))
      ->setDescription(self::DESCRIPTION)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'textfield',
        'weight' => 100,
      ]);
      // ->addConstraint('VopersonExternalAffilliation');

    return $fields;
  }

  /**
   * Check for changes in the user entity to dispatch events.
   *
   * @param \Drupal\user\UserInterface $user
   */
  public function checkFieldChange(UserInterface $user) {
    foreach (self::EVENT_CLASS as $field => $event_class) {
      $old_value = (empty($user->original)) ? NULL : $user->original
        ->get($field)->getValue();
      $new_value = $user
        ->get($field)->getValue();

      if ($old_value !== $new_value) {
        // Instantiate our event.
        $event = new $event_class($user);
        // Dispatch the event.
        $this->eventDispatcher
          ->dispatch($event, $event_class::EVENT_NAME);
      }
    }
  }

  /**
   * Set schac_home_organization value on a user entity.
   *
   * @param \Drupal\user\UserInterface $user
   * @param array $sho
   */
  public function setUserSchacHomeOrganization(UserInterface $user, array $sho) {
    $base_field = self::FIELD_SHO;

    $user->$base_field = $sho;
    $user->save();
  }

  /**
   * Set schac_personal_unique_code value on a user entity.
   *
   * @param \Drupal\user\UserInterface $user
   * @param array $spuc
   */
  public function setUserSchacPersonalUniqueCode(UserInterface $user, array $spuc) {
    $base_field = self::FIELD_SPUC;

    $user->$base_field = $spuc;
    $user->save();
  }

  /**
   * Set voperson_external_affilliation value on a user entity.
   *
   * @param \Drupal\user\UserInterface $user
   * @param array $vea
   */
  public function setUserVopersonExternalAffilliation(UserInterface $user, array $vea) {
    $base_field = self::FIELD_VEA;

    $user->$base_field = $vea;
    $user->save();
  }

}
