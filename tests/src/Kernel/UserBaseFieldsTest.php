<?php

namespace Drupal\Tests\myacademicid_user_fields\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that the User entity has certain base fields.
 *
 * @group myacademicid_user_fields
 */
class UserBaseFieldsTest extends KernelTestBase {

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

    $this->assertArrayHasKey('maid_schac_home_organization', $fields);
    $this->assertArrayHasKey('maid_schac_personal_unique_code', $fields);
    $this->assertArrayHasKey('maid_voperson_external_affilliation', $fields);
  }
}
