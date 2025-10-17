<?php

namespace Drupal\Tests\myacademicid_user_fields\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the global settings form when Server mode is available.
 *
 * @group myacademicid_user_fields
 */
class SettingsFormServerModeTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'oauth2_server',
    'myacademicid_user_fields',
    'myacademicid_user_claims',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
  }

  /**
   * Tests access to the settings form by a non-privileged user.
   */
  public function testSettingsFormWithoutPermission() {
    $account = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/services/myacademicid');
    $this->assertSession()
      ->statusCodeEquals(403);
  }

  /**
   * Tests the settings form as a privileged user.
   */
  public function testSettingsForm() {
    $account = $this->drupalCreateUser(['administer myacademicid user fields']);
    $this->drupalLogin($account);

    // Test access to the settings form.
    $this->drupalGet('admin/config/services/myacademicid');
    $this->assertSession()
      ->statusCodeEquals(200);

    // Test the default configuration.
    $default_config = $this->config('myacademicid_user_fields.settings')
      ->get('mode');
    $this->assertEquals($default_config, 'client');

    // Test the Client option is checked by default.
    $this->assertSession()
      ->checkboxChecked('edit-mode-client');

    // Test the Server option is available.
    $this->assertSession()
      ->fieldEnabled('edit-mode-server');

    // Test form submission.
    $this->submitForm(['mode' => 'server'], 'Save configuration');
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    // Test the configuration has been updated.
    $new_config = $this->config('myacademicid_user_fields.settings')
      ->get('mode');
    $this->assertEquals($new_config, 'server');

    // Test the new value is present in the form.
    $this->drupalGet('admin/config/services/myacademicid');
    $this->assertSession()
      ->statusCodeEquals(200);
    $this->assertSession()
      ->checkboxChecked('edit-mode-server');
  }

}
