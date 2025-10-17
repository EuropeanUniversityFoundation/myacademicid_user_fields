<?php

namespace Drupal\Tests\myacademicid_user_claims\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the global settings form when Server mode is available.
 *
 * @group myacademicid_user_fields
 */
class MemberAffiliationsFormTest extends BrowserTestBase {

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

    \Drupal::configFactory()
      ->getEditable('myacademicid_user_fields.settings')
      ->set('mode', 'server')
      ->save();
  }

  /**
   * Tests that the settings form is NOT acessible by a non-privileged user.
   */
  public function testSettingsFormWithoutPermission() {
    $account = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/services/myacademicid/member-affiliations');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests the settings form as a privileged user.
   */
  public function testSettingsForm() {
    $account = $this->drupalCreateUser(['administer myacademicid user fields']);
    $this->drupalLogin($account);

    // Test access to the settings form.
    $this->drupalGet('admin/config/services/myacademicid/member-affiliations');
    $this->assertSession()
      ->statusCodeEquals(200);

    // Test the default configuration, but with upstream Server mode.
    $mode_config = $this->config('myacademicid_user_fields.settings')
      ->get('mode');
    $this->assertEquals($mode_config, 'server');

    $types_config = $this->config('myacademicid_user_fields.types')
      ->get('additional');
    $this->assertEmpty($types_config);

    $default_config = $this->config('myacademicid_user_claims.settings')
      ->get('assert_member');
    $this->assertEmpty($default_config);

    // Test the configurable options.
    $this->assertSession()
      ->checkboxNotChecked('edit-assert-alum');
    $this->assertSession()
      ->checkboxNotChecked('edit-assert-affiliate');
    $this->assertSession()
      ->checkboxNotChecked('edit-assert-library-walk-in');

    // Test form submission.
    $submission_data = [
      'edit-assert-alum' => TRUE,
      'edit-assert-affiliate' => TRUE,
      'edit-assert-library-walk-in' => TRUE,
    ];
    $this->submitForm($submission_data, 'Save configuration');
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    // Test the configuration has been updated.
    $new_config = $this->config('myacademicid_user_claims.settings')
      ->get('assert_member');
    $this->assertEquals($new_config, ['alum', 'affiliate', 'library-walk-in']);

    // Test the new values are present in the form.
    $this->assertSession()
      ->checkboxChecked('edit-assert-alum');
    $this->assertSession()
      ->checkboxChecked('edit-assert-affiliate');
    $this->assertSession()
      ->checkboxChecked('edit-assert-library-walk-in');
  }

  /**
   * Tests the settings form with additional affiliation types.
   */
  public function testSettingsFormAdditionalTypes() {
    $account = $this->drupalCreateUser(['administer myacademicid user fields']);
    $this->drupalLogin($account);

    // Set additional affiliation types in upstream config.
    $additional = [
      'ewp-admin',
      'honor|Honoris Causa',
      'faculty|Academics staff',
    ];
    \Drupal::configFactory()
      ->getEditable('myacademicid_user_fields.types')
      ->set('additional', $additional)
      ->save();

    // Test access to the settings form.
    $this->drupalGet('admin/config/services/myacademicid/member-affiliations');
    $this->assertSession()
      ->statusCodeEquals(200);

    // Test the configurable options.
    $this->assertSession()
      ->fieldExists('edit-assert-alum');
    $this->assertSession()
      ->fieldExists('edit-assert-affiliate');
    $this->assertSession()
      ->fieldExists('edit-assert-library-walk-in');
    // Test the additional configurable options.
    $this->assertSession()
      ->fieldExists('edit-assert-ewp-admin');
    $this->assertSession()
      ->fieldExists('edit-assert-honor');
    // Test the label override has no effect here.
    $this->assertSession()
      ->fieldNotExists('edit-assert-faculty');

    // Test form submission.
    $submission_data = [
      'edit-assert-alum' => TRUE,
      'edit-assert-ewp-admin' => TRUE,
    ];
    $this->submitForm($submission_data, 'Save configuration');
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    // Test the configuration has been updated.
    $new_config = $this->config('myacademicid_user_claims.settings')
      ->get('assert_member');
    $this->assertEquals($new_config, ['alum', 'ewp-admin']);

    // Test the new values are present in the form.
    $this->assertSession()
      ->checkboxChecked('edit-assert-alum');
    $this->assertSession()
      ->checkboxNotChecked('edit-assert-library-walk-in');
    $this->assertSession()
      ->checkboxChecked('edit-assert-ewp-admin');
    $this->assertSession()
      ->checkboxNotChecked('edit-assert-honor');
  }

  /**
   * Tests the settings form in Client mode.
   */
  public function testSettingsFormClientMode() {
    $account = $this->drupalCreateUser(['administer myacademicid user fields']);
    $this->drupalLogin($account);

    // Undo setup: back to Client mode in upstream config.
    \Drupal::configFactory()
      ->getEditable('myacademicid_user_fields.settings')
      ->set('mode', 'client')
      ->save();

    // Test access to the settings form.
    $this->drupalGet('admin/config/services/myacademicid/member-affiliations');
    $this->assertSession()
      ->statusCodeEquals(200);

    // Test the Client mode enabled triggers a warning on the page.
    $this->assertSession()
      ->statusMessageExists('warning');
    // Check for the plain text first.
    $this->assertSession()
      ->pageTextContains('These settings have no effect when operating in');
    // Check for the content of the anchor tag separately.
    $this->assertSession()
      ->pageTextContains('Client mode');
  }

}
