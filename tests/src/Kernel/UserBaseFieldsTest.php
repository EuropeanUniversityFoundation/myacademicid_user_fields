<?php

namespace Drupal\Tests\myacademicid_user_fields\Kernel;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that the User entity has certain base fields.
 *
 * @group myacademicid_user_fields
 */
class UserBaseFieldsTest extends KernelTestBase {

  /**
   * Fields to test.
   */
  const NEW_BASE_FIELDS = [
    'maid_schac_home_organization',
    'maid_schac_personal_unique_code',
    'maid_voperson_external_affilliation'
  ];

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['user', 'myacademicid_user_fields'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
  }

  /**
   * Tests that the base field definitions exist.
   */
  public function testBaseFieldDefinitions() {
    $fields = \Drupal::service('entity_field.manager')
      ->getFieldDefinitions('user', 'user');

    foreach (self::NEW_BASE_FIELDS as $field) {
      // Test whether the field exists.
      $this->assertArrayHasKey($field, $fields);
      // Test whether the field is a base field.
      $this->assertInstanceOf(BaseFieldDefinition::class, $fields[$field]);
    }
  }
}
