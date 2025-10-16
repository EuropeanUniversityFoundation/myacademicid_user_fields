<?php

namespace Drupal\Tests\myacademicid_user_fields\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test description.
 *
 * @group myacademicid_user_fields
 */
class SettingsFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user', 'myacademicid_user_fields'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
  }

  /**
   * Tests that the settings page is acessible by a privileged user.
   */
  public function testSettingsPage() {
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

    // Test the Server option is disabled by default.
    $this->assertSession()
      ->fieldDisabled('edit-mode-server');

    // Test form submission.
    $this->submitForm(['mode' => 'client'], 'Save configuration');
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    // Test the new value is there.
    $this->drupalGet('admin/config/services/myacademicid');
    $this->assertSession()
      ->statusCodeEquals(200);
    $this->assertSession()
      ->checkboxChecked('edit-mode-client');

    // Test the configuration has been updated.
    $new_config = $this->config('myacademicid_user_fields.settings')
      ->get('mode');
    $this->assertEquals($default_config, $new_config);
  }

  /**
   * Tests that the settings page is NOT acessible by a non-privileged user.
   */
  public function testSettingsPageWithoutPermission() {
    $account = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/services/myacademicid');
    $this->assertSession()->statusCodeEquals(403);
  }

}
