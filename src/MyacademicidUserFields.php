<?php

namespace Drupal\myacademicid_user_fields;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\Entity\User;
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

  const CLIENT_MODE = 'client';
  const SERVER_MODE = 'server';

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
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The constructor.
   *
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    EventDispatcherInterface $event_dispatcher,
    MessengerInterface $messenger,
    TranslationInterface $string_translation
  ) {
    $this->eventDispatcher   = $event_dispatcher;
    $this->messenger         = $messenger;
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
    dpm(__METHOD__);
    foreach (self::EVENT_CLASS as $field => $event_class) {
      if (! $this->equalValue($user, $field)) {
        // Instantiate our event.
        $event = new $event_class($user);
        // Dispatch the event.
        $this->eventDispatcher
          ->dispatch($event, $event_class::EVENT_NAME);
      }
    }
  }

  /**
   * Check for equal field value on a user entity.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param string $field
   *   The field name.
   *
   * @return boolean
   */
  public function equalValue(UserInterface $user, string $field): bool {
    $old_value = (empty($user->original)) ? NULL : $user->original
      ->get($field)->getValue();
    $new_value = $user
      ->get($field)->getValue();

    return ($old_value === $new_value);
  }

  /**
   * Set schac_home_organization value on a user entity.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $sho
   *   Array of schac_home_organization values.
   * @param boolean $save
   *   Whether the user entity should be saved after setting the value.
   */
  public function setUserSchacHomeOrganization(UserInterface $user, array $sho, $save = TRUE) {
    dpm(__METHOD__);
    $this->setValidFieldValue($user, self::FIELD_SHO, $sho, self::CLAIM_SHO, $save);
  }

  /**
   * Set schac_personal_unique_code value on a user entity.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $spuc
   *   Array of schac_personal_unique_code values.
   * @param boolean $save
   *   Whether the user entity should be saved after setting the value.
   */
  public function setUserSchacPersonalUniqueCode(UserInterface $user, array $spuc, $save = TRUE) {
    dpm(__METHOD__);
    $this->setValidFieldValue($user, self::FIELD_SPUC, $spuc, self::CLAIM_SPUC, $save);
  }

  /**
   * Set voperson_external_affilliation value on a user entity.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param array $vea
   *   Array of voperson_external_affilliation values.
   * @param boolean $save
   *   Whether the user entity should be saved after setting the value.
   */
  public function setUserVopersonExternalAffilliation(UserInterface $user, array $vea, $save = TRUE) {
    dpm(__METHOD__);
    $this->setValidFieldValue($user, self::FIELD_VEA, $vea, self::CLAIM_VEA, $save);
  }

  /**
   * Set a field value on a user entity if entity validation allows.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user entity.
   * @param string $field
   *   The field machine name.
   * @param array $value
   *   The new value for the field.
   * @param string $claim
   *   The corresponding claim to the field.
   * @param boolean $save
   *   Whether the user entity should be saved after setting the value.
   */
  private function setValidFieldValue(UserInterface $user, string $field, array $value, string $claim, $save = TRUE) {
    dpm(__METHOD__);
    $original = $user->get($field)->getValue();

    $user->set($field, $value);

    $user->_skipProtectedUserFieldConstraint = TRUE;
    $violations = $user->validate();

    if ($violations->count() > 0) {
      foreach ($violations as $idx => $violation) {
        $this->messenger->addError($violation->getMessage());
      }

      $this->messenger->addError($this->t('Cannot set %claim claim to %value', [
        '%claim' => $claim,
        '%value' => \implode(', ', $value)
      ]));

      $user->set($field, $original);
    }

    if ($save) {
      $user->save();
    }
  }

}
