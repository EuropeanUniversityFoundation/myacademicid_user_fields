<?php

namespace Drupal\Tests\myacademicid_user_hei\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the settings form.
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
  protected static $modules = [
    'user',
    'views',
    'ewp_institutions',
    'ewp_institutions_get',
    'ewp_institutions_lookup',
    'ewp_institutions_user',
    'myacademicid_user_fields',
    'myacademicid_user_hei',
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

    $this->drupalGet('admin/config/services/myacademicid/hei');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests that the settings form as a privileged user.
   */
  public function testSettingsForm() {
    $account = $this->drupalCreateUser(['administer myacademicid user fields']);
    $this->drupalLogin($account);

    // Test access to the settings form.
    $this->drupalGet('admin/config/services/myacademicid/hei');
    $this->assertSession()
      ->statusCodeEquals(200);

    // Test the default configuration.
    $default_sync_mode = $this->config('myacademicid_user_hei.settings')
      ->get('sync_mode');
    $this->assertEquals('empty', $default_sync_mode);
    $default_import = $this->config('myacademicid_user_hei.settings')
      ->get('import');
    $this->assertEquals(FALSE, $default_import);

    // Test the 'empty' sync mode option is checked by default.
    $this->assertSession()
      ->checkboxChecked('edit-sync-mode-empty');

    // Test the Import option is unchecked by default.
    $this->assertSession()
      ->checkboxNotChecked('edit-import');

    // Test form submission.
    $submission_data = [
      'sync_mode' => 'sync',
      'import' => TRUE,
    ];
    $this->submitForm($submission_data, 'Save configuration');
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    // Test the configuration has been updated.
    $new_sync_mode = $this->config('myacademicid_user_hei.settings')
      ->get('sync_mode');
    $this->assertEquals('sync', $new_sync_mode);
    $new_import = $this->config('myacademicid_user_hei.settings')
      ->get('import');
    $this->assertEquals(TRUE, $new_import);

    // Test the new values are there.
    $this->drupalGet('admin/config/services/myacademicid/hei');
    $this->assertSession()
      ->statusCodeEquals(200);
    $this->assertSession()
      ->checkboxChecked('edit-sync-mode-sync');
    $this->assertSession()
      ->checkboxChecked('edit-import');
  }

}
