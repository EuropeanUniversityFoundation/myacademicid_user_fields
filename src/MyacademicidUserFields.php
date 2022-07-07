<?php

namespace Drupal\myacademicid_user_fields;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * MyAcademicID User Fields service.
 */
class MyacademicidUserFields {

  use StringTranslationTrait;

  const FIELD_SHO = 'maid_schac_home_organization';
  const FIELD_SPUC = 'maid_schac_personal_unique_code';
  const FIELD_VEA = 'maid_voperson_external_affilliation';

  const CLAIM_SHO = 'schac_home_organization';
  const CLAIM_SPUC = 'schac_personal_unique_code';
  const CLAIM_VEA = 'voperson_external_affilliation';

  const DESCRIPTION = 'As provided by MyAcademicID.';

  /**
   * The constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    TranslationInterface $string_translation
  ) {
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

}
